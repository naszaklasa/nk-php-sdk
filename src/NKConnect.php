<?php

/*
 * Copyright 2011 Nasza Klasa Spółka z ograniczoną odpowiedzialnością
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @package Auth
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKConnectUnusableException extends NKException
{

}

/**
 * Obsługa mechanizmu autentykacyjnego NKConnect. NKConnect pozwala na autoryzację użytkownika Twojej strony w serwisie NK
 * Klasa implementuje interfejs NKTokenProvider dzięki czemu może zostać użyta jako źródło tokena autoryzacyjnego dla
 * wywołań serwisów otwartego API NK. Przykłady implementacji znajdziesz w plikach examples/authentication*.php
 *
 * <code>
 * $conf = array('permissions' => array(NKPermissions::BASIC_PROFILE),
 *               'key'         => 'some_key',
 *               'secret'      => 'some_secret');
 * $auth = new NKConnect($conf);
 * </code>
 *
 * <code>
 * $conf = new NKConfig();
 * $conf->permissions' = array(NKPermissions::BASIC_PROFILE);
 * $conf->key = 'some_key';
 * $conf->secret = 'some_secret';
 * $auth = new NKConnect($conf);
 * $if ($auth->handleCallback()) {
 *  // Ten kod zostanie wykonany w momencie pomyślnego zakończenia procesu autentykacji
 * } elseif($auth->getError()) {
 *  // Ten kod zostanie wykonany, jeśli podczas procesu autentykacji wystąpi błąd
 * }
 * if ($auth->authenticated()) {
 *   // Ten kod wykonany będzie wtedy, jeśli użytkownik jest prawidłowo zalogowany
 * }
 * </code>
 *
 * @package Auth
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKConnect extends NKAuthentication
{
  private $http_request;

  /**
   * Konstruktor, powinieneś go wywołać *zanim wyślesz* jakiekolwiek nagłówki. Konstruktor w przypadku braku sesji PHP
   * uruchomi ją, aby móc zapisać swój stan. Możesz w argumencie konstruktora podać tablicę klucz => wartość z opcjami
   * konfiguracyjnymi, lub przekazać obiekt NKConfig. Jeśli nie chcesz ustawiać konfiguracji w konstruktorze, możesz
   * to zrobić z użyciem metody ->setConfig
   *
   * <code>
   * $conf = array('permissions' => array(NKPermissions::BASIC_PROFILE),
   *               'key'         => 'some_key',
   *               'secret'      => 'some_secret');
   * $auth = new NKConnect($conf);
   * </code>
   *
   * <code>
   * $conf = new NKConfig();
   * $conf->permissions' = array(NKPermissions::BASIC_PROFILE);
   * $conf->key = 'some_key';
   * $conf->secret = 'some_secret';
   * $auth = new NKConnect($conf);
   * </code>
   *
   * @throws NKConnectUnusableException
   * @param array|NKConfig $config
   */
  public function __construct($config = null)
  {
    parent::__construct($config);

    if (false !== $this->getHttpRequest()->headersSent()) {
      throw new NKConnectUnusableException("Headers was already sent, you should call handleCallback() before you render *any* HTML or on separate callback page");
    }

    $this->getHttpRequest()->startSessionIfRequired();
  }

  /**
   *
   * @return NKHttpRequest
   */
  protected function getHttpRequest()
  {
    if (null === $this->http_request) {
      $this->http_request = new NKHttpRequest();
    }
    return $this->http_request;
  }

  /**
   * Zwraca komunikat błędu jeśli wystąpił problem lub null jeśli błędu nie ma. Metoda powinna być wywołana w callbacku
   * aby obsłużyć niepoprawne żądania
   * 
   * @return null|string
   */
  public function getError()
  {
    $request_data = $this->getHttpRequest()->getRequestData();

    if (isset($request_data['nkconnect_error']) && '' <> trim($request_data['nkconnect_error'])) {
      return urldecode($request_data['nkconnect_error']);
    }
    return null;
  }

  /**
   * Metoda służąca do obsługi mechanizmu callback'a. Adres strony z kodem callbacka powinieneś
   * określić w panelu administracyjnym NK. Metoda zwraca true jeśli zakończono z powodzeniem proces autentykacji. Zwróć
   * uwagę, iż false zwracane jest podczas przejść pomiędzy stanami, i *nie oznacza* niepowodzenia autoryzacji. W przypadku
   * niepoprawnego logowania proces kończy się w popupie wywołanym przyciskiem, callback nie jest wtedy wywoływany, w przypadku
   * wystąpienia błędu odmowy udostępnienia danych lub innego błędu przejścia pomiędzy stanami, błąd sygnalizuje metoda
   * getError()
   *
   * @return bool
   */
  public function handleCallback()
  {
    $auth_state = $this->authState();
    $result = false;

    if (1 === $auth_state || (0 === $auth_state && null === $this->getError() && '' <> $this->getConfig()->callback_url && false !== strpos($this->getMyUrl(), $this->getConfig()->callback_url))) {
      $this->handleJsToken();
    } elseif (2 === $auth_state) {
      $this->handleJsTokenRegistration();
    } elseif (3 === $auth_state && $this->tokenAvailable()) {
      $result = true;
    } elseif ($auth_state < 0) {
      $this->handleLogout();
    }

    return $result;
  }

  /**
   * Metoda zwraca kod HTML przycisku logowania w NK ("Zaloguj się z NK") dla niezalogowanego użytkownika, dla użytkownika
   * zalogowanego wyświetli link "Wyloguj". Jeśli nie chcesz wyświetlać linku wyświetlaj przycisk wyłącznie dla niezalogowanych
   * użytkowników (popatrz na ->authenticated())
   *
   * @throws NKConfigException
   * 
   * @param bool $refreshable
   * @return string
   */
  public function button($refreshable = false)
  {
    $key = trim($this->getConfig()->key);
    if ('' == $key) {
      throw new NKConfigException('Please provide $key in your config');
    }
    if (false === is_array($this->getConfig()->permissions)) {
      throw new NKConfigException("Permissions provided by config must be an array");
    }

    if ($refreshable && $this->tokenAvailableButExpired()) {
      $nks = <<<EOT
<script type="text/javascript" src="http://%d.s-nk.pl/script/packs/oauth"></script>
<script type="text/javascript">
  nk.OAuth.try_to_get_token("%s", %s, "%s", "%s");
</script>
EOT;
      $url = ('' == $this->getConfig()->callback_url ? ("\"".$this->getAuthStateUrl()."\"") : ("\"".$this->getConfig()->callback_url."\""));
      $nks = sprintf($nks, rand(0,1), $key, $url, implode(',',$this->getConfig()->permissions), $this->getOtp());
    } else {
      $nks = <<<EOT
<script type="text/javascript" src="http://%d.s-nk.pl/script/packs/oauth"></script>
<script type="text/javascript">
  nk.OAuth.create_button("%s", %s, "%s", "%s");
</script>
EOT;
      $url = ('' == $this->getConfig()->callback_url ? ("\"".$this->getAuthStateUrl()."\"") : ("\"".$this->getConfig()->callback_url."\""));
      $nks = sprintf($nks, rand(0, 1), $key, $url, implode(',', $this->getConfig()->permissions), $this->getOtp());
    }
    return $nks;
  }

  /**
   * Metoda podaje link służący do zamknięcia sesji NKConnect. Zwróć uwagę, że zamknięcie sesji NKConnect nie oznacza
   * zamknięcia Twojej sesji autoryzacyjnej (o ile jej używasz). W takim przypadku powinieneś samodzielnie obsłużyć
   * wylogowanie użytkownika, używając w procesie metody NKConnect::logout()
   *
   * @return string
   */
  public function logoutLink()
  {
    return $this->getAuthStateUrl(-1);
  }

  /**
   * Metoda usuwa sesję użytkownika NK z kontenera sesji, powinieneś wywołać ją w momencie kiedy wylogowujesz użytkownika
   * z serwisu
   *
   * @return void
   */
  public function logout()
  {
    $req = $this->getHttpRequest();

    $req->unsetSessionData($this->getSessionKey('token'));
    $req->unsetSessionData($this->getSessionKey('token_exp'));
    $req->unsetSessionData($this->getSessionKey('otp'));
  }

  /**
   * Metoda zwraca token lub null jeśli token nie jest dostępny
   *
   * @return string
   */
  public function getToken()
  {
    $session_data = $this->getHttpRequest()->getSessionData();
    return $this->tokenAvailable() ? $session_data[$this->getSessionKey('token')] : null;
  }

  /**
   * Metoda zwraca true jeśli token jest dostępny. W przeciwnym przypadku zwracane jest false
   *
   * @abstract
   * @return bool
   */
  public function tokenAvailable()
  {
    $session_data = $this->getHttpRequest()->getSessionData();
    return (isset($session_data[$this->getSessionKey('token')]) && isset($session_data[$this->getSessionKey('token_exp')]) && (time() <= $session_data[$this->getSessionKey('token_exp')]));
  }

  private function tokenAvailableButExpired()
  {
    $session_data = $this->getHttpRequest()->getSessionData();
    return (isset($session_data[$this->getSessionKey('token')]) && isset($session_data[$this->getSessionKey('token_exp')]) && (time() > $session_data[$this->getSessionKey('token_exp')]));
  }

  private function getSessionKey($key)
  {
    return sprintf("nkconnect_%s_%s", $this->getConfig()->key, $key);
  }

  protected function authState()
  {
    $request_data = $this->getHttpRequest()->getRequestData();

    if (true === isset($request_data['nkconnect_state']) && $request_data['nkconnect_state'] < 4) {
      return (int)$request_data['nkconnect_state'];
    }
    return 0;
  }

  private function getMyUrl()
  {
    $server_data = $this->getHttpRequest()->getServerData();

    $url  = (true === isset($server_data["HTTPS"]) && 'on' == $server_data["HTTPS"]) ? "https://" : "http://";
    $url .= $server_data['HTTP_HOST'] . parse_url($server_data['REQUEST_URI'], PHP_URL_PATH);

    $params = parse_url($server_data['REQUEST_URI'], PHP_URL_QUERY);
    if ($params) {
      $params = OAuthUtil::parse_parameters($params);
      if (true === isset($params['nkconnect_state'])) {
        unset($params['nkconnect_state']);
      }
      if (true === isset($params['nkconnect_error'])) {
        unset($params['nkconnect_error']);
      }
      if (0 <> count($params)) {
        $url .= '?' . OAuthUtil::build_http_query($params);
      }
    }

    return $url;
  }

  private function getErrorUrl()
  {
    $url = $this->getMyUrl();
    $url .= false !== strpos($url, '?') ? '&' : '?';
    $url .= 'nkconnect_error=';
    return $url;
  }

  private function getAuthStateUrl($state = null)
  {
    $url = $this->getMyUrl();
    $state = (null !== $state ? $state : ($this->authState() + 1));

    $url .= false !== strpos($url, '?') ? '&' : '?';
    $url .= 'nkconnect_state=' . $state;

    return $url;
  }

  private function getOtp()
  {
    $session_data = $this->getHttpRequest()->getSessionData();
    $key = $this->getSessionKey('otp');

    if (false === isset($session_data[$key])) {
      $otp = sha1(time().uniqid());
      $this->getHttpRequest()->setSessionData($key, $otp);
      return $otp;
    }

    return $session_data[$key];
  }

  private function handleLogout()
  {
    $this->logout();
    $this->getHttpRequest()->header("Location: " . $this->getMyUrl());
    $this->getHttpRequest()->terminate();
  }

  private function handleJsTokenRegistration()
  {
    $req = $this->getHttpRequest();

    $server_data = $req->getServerData();
    $post_data = $req->getPostData();

    if ($server_data["REQUEST_METHOD"] == "POST" && isset($post_data['nkconnect_otp']) && $post_data['nkconnect_otp'] == $this->getOtp() && isset($post_data['nkconnect_token'])) {
      $req->setSessionData($this->getSessionKey('token'), $post_data['nkconnect_token']);
      $req->setSessionData($this->getSessionKey('token_exp'), (time() + NKTokenProvider::TOKEN_TTL));
      $resp = array('result'   => true,
                    'error'    => '',
                    'redirect' => $this->getAuthStateUrl());
    } else {
      $errors = array();
      if ($server_data["REQUEST_METHOD"] <> "POST") {
        $errors[] = 'this is not a POST request';
      }
      if (!isset($post_data['nkconnect_otp'])) {
        $errors[] = 'missing state token';
      }
      if ($post_data['nkconnect_otp'] <> $this->getOtp()) {
        $errors[] = 'state token mismatch';
      }
      if (!isset($post_data['nkconnect_token'])) {
        $errors[] = 'token is missing';
      }
      $resp = array('result'   => false,
                    'error'    => 'Unable to get or validate authentication state: ' . implode(', ', $errors),
                    'redirect' => '');
    }

    $req->unsetSessionData($this->getSessionKey('otp'));
    $req->header('Content-type: application/json');

    echo json_encode($resp);

    $req->terminate();
  }

  private function handleJsToken()
  {
    $rnd = rand(0, 1);
    $jsr = $this->getConfig()->jquery_src;
    $sur = $this->getAuthStateUrl(2);
    $erl = $this->getErrorUrl();
    $nks = <<<EOT
<html>
  <head>
    <script type="text/javascript" src="http://{$rnd}.s-nk.pl/script/packs/oauth"></script>
    <script src="{$jsr}"></script>
    <script>
      $.extend({
        getUrlHashVars: function(){
          var vars = [], hash;
          var hashes = window.location.href.slice(window.location.href.indexOf('#') + 1).split('&');
          for(var i = 0; i < hashes.length; i++)
          {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
          }
          return vars;
        },
        getUrlHashVar: function(name){
          return $.getUrlHashVars()[name];
        }
      });
    </script>
  </head>
  <body>
    <script type="text/javascript">
      var callback = function(token, state) {
        $.ajax({
          type: 'POST',
          url: '{$sur}',
          data: {
            nkconnect_token: token,
            nkconnect_otp: state
          },
          error: function (resp) {
            window.location = '{$erl}' + resp.error;
          },
          success: function (resp) {
            if (resp.result == true && resp.redirect != '') {
              window.location = resp.redirect;
            }
            else {
              window.location = '{$erl}' + resp.error;
            }
          }
        });
      };
      $(document).ready(function() {
        if ($.getUrlHashVar('error')) {
          window.location = '{$erl}' + $.getUrlHashVar('error_description');
        }
        nk.OAuth.is_token_available(callback);
      });
    </script>
  </body>
</html>
EOT;

    echo $nks;
    $this->getHttpRequest()->terminate();
  }
}
