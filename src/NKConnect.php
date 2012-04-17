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
  const MODE_WINDOW = 1;
  const MODE_POPUP = 2;

  // @fixme: ilość sekund odejmowanych od TTLa podczas decydowania o wygaśnięciu tokena w celu wyprzedzenia odświeżenia, powinno być 2, dodatkowe 300 naprawia buga z 10m TTLem (powinien być 15m)
  const REFRESH_AHEAD_TIME = 302;

  private $errors = array();

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
    return NKHttpRequest::singleton();
  }

  /**
   *
   * @return NKHttpClient
   */
  protected function getHttpClient()
  {
    return new NKHttpClient();
  }

  /**
   * Zwraca komunikat błędu jeśli wystąpił problem lub null jeśli błędu nie ma. Metoda powinna być wywołana w callbacku
   * aby obsłużyć niepoprawne żądania
   * 
   * @return null|string
   */
  public function getError()
  {
    return (0 <> count($this->errors) ? implode(', ', $this->errors) : null);
  }

  /**
   * Zwraca tablicę asocjacyjną, zawierającą kody błędów wraz z ich opisami
   *
   * @return array|null
   */
  public function getErrors()
  {
    return (0 <> count($this->errors) ? $this->errors : null);
  }

  /**
   * Metoda służąca do obsługi mechanizmu callback'a. Metoda zwraca true jeśli zakończono z powodzeniem proces autentykacji. Zwróć
   * uwagę, iż false *nie oznacza* niepowodzenia. False jest zwracane również wtedy, kiedy kod callbacku nie jest wykonywany. W przypadku
   * wystąpienia błędu odmowy udostępnienia danych lub innego błędu przejścia pomiędzy stanami, błąd sygnalizuje metoda
   * getError()
   *
   * @return bool
   */
  public function handleCallback()
  {
    $req = $this->getHttpRequest();
    $req_data = $req->getRequestData();

    if (true === isset($req_data['nkconnect_state']) && $req_data['nkconnect_state'] == 'callback') {

      if (false === isset($req_data['state']) || $req_data['state'] <> $this->getOtp()) {
        $this->errors['invalid_otp'] = 'invalid OTP';
        return false;
      }

      if (true === isset($req_data['error'])) {
        $this->errors[$req_data['error']] = isset($req_data['error_description']) ? $req_data['error_description'] : $req_data['error'];
        return false;
      }

      if (false === isset($req_data['code']) || strlen($req_data['code']) < 1) {
        $this->errors['code_missing'] = 'Missing auth code';
        return false;
      }

      $this->exchangeCodeToToken($req_data['code']);
      $req->unsetSessionData($this->getSessionKey('otp'));

      return $this->authenticated();
    } elseif (true === isset($req_data['nkconnect_state']) && $req_data['nkconnect_state'] == 'logout') {

      $this->logout();
      return !$this->authenticated();
    }

    return false;
  }

  private function exchangeCodeToToken($code)
  {
    $params = array(
      "client_id"     => $this->getConfig()->key,
      "client_secret" => $this->getConfig()->secret,
      "grant_type"    => 'authorization_code',
      "redirect_uri"  => $this->redirectUri(),
      "scope"         => implode(',', $this->getConfig()->permissions),
      "code"          => $code,
    );

    // Usuń wszystkie dane autoryzacyjne, jesli nie udało się wymienić tokena
    if (false === ($data = $this->oauth2HttpTokenRequest($params))) {
      $this->logout();
      return false;
    }

    $this->getHttpRequest()
      ->setSessionData($this->getSessionKey('token'), $data['access_token'])
      ->setSessionData($this->getSessionKey('refresh'), $data['refresh_token'])
      ->setSessionData($this->getSessionKey('expiry'), ($this->getHttpRequest()->getTime() + $data['expires_in']));

    return true;
  }

  private function refreshToken($refresh_token)
  {
    $params = array(
      "client_id"     => $this->getConfig()->key,
      "client_secret" => $this->getConfig()->secret,
      "grant_type"    => 'refresh_token',
      "scope"         => implode(',', $this->getConfig()->permissions),
      "refresh_token" => $refresh_token,
    );

    // Usuń wszystkie dane autoryzacyjne, jesli nie udało się odświeżyć tokena
    if (false === ($data = $this->oauth2HttpTokenRequest($params))) {
      $this->logout();
      return false;
    }

    $this->getHttpRequest()
      ->setSessionData($this->getSessionKey('token'), $data['access_token'])
      ->setSessionData($this->getSessionKey('expiry'), ($this->getHttpRequest()->getTime() + $data['expires_in']));

    return true;
  }

  private function oauth2HttpTokenRequest($params)
  {
    $url = "https://nk.pl/oauth2/token";

    $http_client = $this->getHttpClient();
    $http_client->exec($url, array(), NKHttpClient::HTTP_POST, $params);

    if (false === in_array($http_client->getResponseCode(), array(200, 400))) {
      $this->errors['http_error'] = 'failed to exec HTTP request when exchanging code to token';
      return false;
    }

    $data = json_decode($http_client->getResponse(), true);

    if (null === $data) {
      $this->errors['decode_error'] = 'Unable to decode response';
      return false;
    }

    if (true == isset($data['error'])) {
      $this->errors[$data['error']] = isset($data['error_description']) ? $data['error_description'] : $data['error'];
      return false;
    }

    if (false === isset($data['access_token']) || false === isset($data['expires_in'])) {
      $this->errors['auth_error'] = 'Not authenticated';
      return false;
    }

    return $data;
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
    return $this->redirectUri('logout');
  }

  /**
   * Metoda usuwa sesję użytkownika NK z kontenera sesji, powinieneś wywołać ją w momencie kiedy wylogowujesz użytkownika
   * z serwisu
   *
   * @return void
   */
  public function logout()
  {
    $this->getHttpRequest()
      ->unsetSessionData($this->getSessionKey('token'))
      ->unsetSessionData($this->getSessionKey('expiry'))
      ->unsetSessionData($this->getSessionKey('refresh'))
      ->unsetSessionData($this->getSessionKey('otp'));
  }

  /**
   * Metoda zwraca token lub null jeśli token nie jest dostępny
   *
   * @return string
   */
  public function getToken()
  {
    if (true !== $this->tokenAvailable()) {
      return null;
    }

    $session_data = $this->getHttpRequest()->getSessionData();
    return $session_data[$this->getSessionKey('token')];
  }

  /**
   * Metoda zwraca true jeśli token jest dostępny. W przeciwnym przypadku zwracane jest false
   *
   * @abstract
   * @return bool
   */
  public function tokenAvailable()
  {
    $req = $this->getHttpRequest();
    $session_data = $req->getSessionData();

    $exists = (isset($session_data[$this->getSessionKey('token')]) && isset($session_data[$this->getSessionKey('expiry')]));
    $not_expired = isset($session_data[$this->getSessionKey('expiry')]) && ($req->getTime() <= ($session_data[$this->getSessionKey('expiry')] - self::REFRESH_AHEAD_TIME));

    if (true === $exists && false === $not_expired  && true === isset($session_data[$this->getSessionKey('refresh')]) && true === $this->refreshToken($session_data[$this->getSessionKey('refresh')])) {
      return $this->tokenAvailable();
    }

    return ($exists && $not_expired);
  }

  private function getSessionKey($key)
  {
    return sprintf("nkconnect_%s_%s", $this->getConfig()->key, $key);
  }

  private function getOtp()
  {
    $session_data = $this->getHttpRequest()->getSessionData();
    $req = $this->getHttpRequest();
    $key = $this->getSessionKey('otp');

    if (false === isset($session_data[$key])) {
      $otp = sha1($req->getTime().uniqid());
      $this->getHttpRequest()->setSessionData($key, $otp);
      return $otp;
    }

    return $session_data[$key];
  }

  /**
   * Metoda zwraca kod HTML przycisku logowania w NK ("Zaloguj się z NK") dla niezalogowanego użytkownika, dla użytkownika
   * zalogowanego wyświetli link "Wyloguj". Jeśli nie chcesz wyświetlać linku wyświetlaj przycisk wyłącznie dla niezalogowanych
   * użytkowników (popatrz na ->authenticated())
   *
   * @throws NKConfigException
   *
   * @return string
   */
  public function button()
  {
    $img  = (true === isset($server_data["HTTPS"]) && 'on' == $server_data["HTTPS"]) ? "https://" : "http://";
    $img .= "nk.pl/img/oauth2/connect";

    if (self::MODE_POPUP === $this->getConfig()->login_mode) {
      $nks = <<<EOT
<script type="text/javascript">
  function NkConnectPopup() {
    var pageURL = '{$this->nkConnectLoginUri()}';
    var title = 'Zaloguj z NK';
    var w = 490;
    var h = 247;
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    var targetWin = window.open (pageURL, title, 'modal=yes, toolbar=no, location=yes, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
  }
</script>
EOT;
      return $nks . sprintf('<a href="javascript:NkConnectPopup();"><img src="%s" alt="Zaloguj z NK" border="0"></a>', $img);
    } else {
      return sprintf('<a href="%s"><img src="%s" alt="Zaloguj z NK" border="0"></a>', $this->nkConnectLoginUri(), $img);
    }
  }

  /**
   * Ta metoda buduje URL przyjmujący żądania logowania użytkownika. Jeśli nie chcesz korzystać ze standardowo oferowanych
   * tutaj elementów (->button()) możesz samodzielnie zbudować element logowania używając tej metody jako źródła adresu
   * docelowego.
   *
   * @return string
   */
  public function nkConnectLoginUri()
  {
    return "https://nk.pl/oauth2/login" .
      "?client_id=" . $this->getConfig()->key .
      "&response_type=code" .
      "&redirect_uri=" . OAuthUtil::urlencode_rfc3986($this->redirectUri()) .
      "&scope=" . implode(',', $this->getConfig()->permissions) .
      "&state=" . $this->getOtp();
  }

  private function redirectUri($state = 'callback')
  {
    $url  = $this->getConfig()->callback_url ? $this->getConfig()->callback_url : $this->getMyUrl();
    $url .= false !== strpos($url, '?') ? '&' : '?';
    $url .= "nkconnect_state=";
    $url .= $state;

    return $url;
  }

  private function getMyUrl()
  {
    $server_data = $this->getHttpRequest()->getServerData();

    $url  = (true === isset($server_data["HTTPS"]) && 'on' == $server_data["HTTPS"]) ? "https://" : "http://";
    $url .= $server_data['HTTP_HOST'] . parse_url($server_data['REQUEST_URI'], PHP_URL_PATH);

    $params = parse_url($server_data['REQUEST_URI'], PHP_URL_QUERY);
    $strip = array('nkconnect_state', 'code', 'state', 'error', 'error_description');

    if ($params) {
      $p = array();
      foreach (OAuthUtil::parse_parameters($params) as $k => $v) {
        if (false === in_array($k, $strip)) {
          $p[$k] = $v;
        }
      }
      if (0 <> count($p)) {
        $url .= '?' . OAuthUtil::build_http_query($p);
      }
    }

    return $url;
  }
}
