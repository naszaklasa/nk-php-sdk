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
 * Wyjątek wyrzucany w przypadku nieistniejącego rekordu, eg. jeśli podasz niepoprawny person.id lub inny identyfikator obiektu,
 * który nie istnieje
 *
 * @package Service
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKServiceMissingRecordException extends NKException
{

}

/**
 * Wyjątek wyrzucany w przypadku błednego wywołania (niewłaściwe lub brakujące argumenty) serwisu lub innej metody
 *
 * @package Service
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKServiceInvalidParamsException extends NKException
{

}

/**
 * Wyjątek wyrzucany w przypadku wywołania nieistniejącego serwisu. Dotyczy wyłącznie metody call, kiedy podajesz samodzielnie
 * URL serwisu
 *
 * @package Service
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKServiceMissingException extends NKException
{

}

/**
 * Wyjątek informujący o braku uprawnień potrzebnych do wywołania serwisu. Przyczyną braku uprawnień może być brak zgód
 * użytkownika, błędne dane autoryzacyjne (key/secret) lub odmowa administracyjna dostępu do danego serwisu.
 *
 * @package Service
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKServiceMissingPermissionException extends NKException
{

}

/**
 * Wyjątek informujący o problemach komunikacji pomiędzy Twoim serwerem, a systemami NK. Sprawdź czy Twój serwer ma
 * połączenie z internetem, potrafi poprawnie rozwiązywać nazwy w DNS oraz może ustanowić połączenie HTTP z adresem URL
 * podanym w stałej NKService::BASE_URL
 *
 * @package Service
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKServiceHttpException extends NKException
{

}

/**
 * Główna klasa definiująca dostępne serwisu w otwartym API NK. Aby użyć któregokolwiek z serwisów potrzebujesz obiektu
 * dostarczającego token autoryzacyjny. Możesz użyć dowolnego komponentu autoryzacyjnego, eg. NKConnect. Powinieneś także
 * podać opcje konfiguracyjne.
 *
 * @package Service
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKService
{
  /**
   * Wersja nk-php-sdk
   */
  const VERSION = '1.2';

  /**
   * Bazowy URL pod którym znajdują się serwisy wystawiane w publicznym API NK
   */
  const BASE_URL = 'http://opensocial.nk-net.pl/v09/social/rest';

  /**
   * @var NKConfig
   */
  private $config;

  /**
   * @var NKTokenProvider
   */
  private $token;

  private $consumer;
  private $http_client;

  private $debug = false;
  
  /**
   * Konstruktor: możesz w argumentach przekazać tablicę lub obiekt NKConfig zawierające konfigurację, oraz obiekt będący
   * instancją NKTokenProvider, dostarczający tokena autoryzacyjnego
   *
   * <code>
   * $conf = new NKConfig(array('key' => 'mykey', 'secret' => 'mysecret', 'permissions' => array(NKPermissions::BASIC_PROFILE)));
   * $auth = new NKConnect($conf);
   * [...]
   * $service = new NKService($conf, $auth);
   * [...]
   * </code>
   *
   * @since 1.0
   *
   * @param NKConfig $config
   * @param NKTokenProvider $token
   */
  public function __construct($config = null, NKTokenProvider $token = null)
  {
    if (null !== $config) {
      $this->setConfig($config);
    }
    if (null !== $token) {
      $this->setTokenProvider($token);
    }
  }

  /**
   * Ustawia obiekt konfiguracji, który będzie używany przez instancje klasy
   *
   * @throws NKConfigException
   *
   * @param array|NKConfig $config
   * @return NKService
   */
  public function setConfig($config)
  {
    if ($config instanceof NKConfig) {
      $this->config = $config;
      return $this;
    } elseif (true === is_array($config)) {
      $this->config = new NKConfig($config);
      return $this;
    }
    throw new NKConfigException("Config must be an array or instance of NKConfig");
  }

  /**
   *
   * @throws NKConfigException
   * @return NKConfig
   */
  public function getConfig()
  {
    if (false === $this->config instanceof NKConfig) {
      throw new NKConfigException("You must provide configuration passing it via constructor or setConfig method");
    }
    return $this->config;
  }

  /**
   * Ustawia obiekt dostarczający informacji o tokenie
   *
   * @param NKTokenProvider $token
   * @return NKService
   */
  public function setTokenProvider(NKTokenProvider $token)
  {
    $this->token = $token;
    return $this;
  }

  /**
   *
   * @throws NKServiceInvalidParamsException
   * @return NKTokenProvider
   */
  protected function getTokenProvider()
  {
    if (false === $this->token instanceof NKTokenProvider) {
      throw new NKServiceInvalidParamsException("You must provide token provider object passing it via constructor or setTokenProvider method");
    }
    return $this->token;
  }

  /**
   *
   * @throws NKConfigException
   * @return OAuthConsumer
   */
  protected function getOAuthConsumer()
  {
    if (strlen($this->getConfig()->key) < 1) {
      throw new NKConfigException('You must provide $key in your config');
    }
    if (!preg_match("/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/", $this->getConfig()->secret)) {
      throw new NKConfigException('You must provide $secret in your config, please be sure is\'t exactly the same secret as in applications management site');
    }
    if (null === $this->consumer) {
      $this->consumer = new OAuthConsumer($this->getConfig()->key, $this->getConfig()->secret);
    }
    return $this->consumer;
  }

  /**
   *
   * @since 1.0
   *
   * @throws NKServiceInvalidParamsException
   *
   * @param NKUser $user
   * @param integer $limit
   * @param integer $offset
   *
   * @return array
   *
   */
  public function photoAlbums(NKUser $user, $limit = null, $offset = null)
  {
    if (!$user) {
      throw new NKServiceInvalidParamsException("You must provide user to that service");
    }

    $params = array();
    if ($limit) {
      $params['count'] = $limit;
    }
    if ($offset) {
      $params['startIndex'] = $offset;
    }

    $data = $this->call("/albums/{$user->id()}/@self", $params);
    $result = array();

    foreach ($data['entry'] as $row) {
      $album = new NKPhotoAlbum();
      $album->assignData($row);
      $result[$album->id()] = $album;
    }
    return $result;
  }

  /**
   *
   * @since 1.0
   *
   * @throws NKServiceInvalidParamsException
   *
   * @param NKPhotoAlbum $album
   * @param null $limit
   * @param null $offset
   *
   * @return array
   */
  public function photos(NKPhotoAlbum $album, $limit = null, $offset = null)
  {
    if (!$album) {
      throw new NKServiceInvalidParamsException("You must provide album to that service");
    }

    $params = array('fields' => 'id,albumId,created,description,mimeType,thumbnailUrl,url,nk_addedBy');
    if ($limit) {
      $params['count'] = $limit;
    }
    if ($offset) {
      $params['startIndex'] = $offset;
    }

    $data = $this->call("/mediaItems/{$album->ownerId()}/@self/{$album->id()}", $params);
    $result = array();

    foreach ($data['entry'] as $row) {
      $photo =  new NKPhoto();
      $photo->assignData($row);
      $result[$photo->id()] = $photo;
    }
    return $result;
  }

  /**
   * Serwis zwraca obiekt użytkownika NKUser związany z użytkownikiem, dla którego został wystawiony token przekazany
   * w obiekcie NKTokenProvider ("zalogowanego użytkownika")
   *
   * @since 1.0
   *
   * @return NKUser
   */
  public function me()
  {
    $result = $this->people('@me');

    if (true === empty($result)) {
      throw new NKServiceMissingRecordException("User not found");
    }
    reset($result);
    return current($result);
  }

  private function person($id)
  {
    $result = $this->people($id);

    if (true === empty($result)) {
      throw new NKServiceMissingRecordException("User not found");
    }
    reset($result);
    return current($result);
  }

  private function people()
  {
    $args = func_get_args();
    if (0 == count($args)) {
      throw new BadMethodCallException("Service 'people' require one or more person id as arguments");
    }

    $ids = array();
    foreach ($args as $user) {
      if (true === $user instanceof NKUser) {
        $ids[] = $user->id();
      } else {
        $ids[] = $user;
      }
    }

    $fields = array('id','age','name','currentLocation','displayName','gender','photos','profileUrl','thumbnailUrl','urls');
    
    if (in_array(NKPermissions::EMAIL_PROFILE, $this->getConfig()->permissions)) {
      $fields[] = 'emails';
    }
    if (in_array(NKPermissions::BIRTHDAY_PROFILE, $this->getConfig()->permissions)) {
      $fields[] = 'birthday';
    }
    if (in_array(NKPermissions::PHONE_PROFILE, $this->getConfig()->permissions)) {
      $fields[] = 'phoneNumbers';
    }
    if (in_array(NKPermissions::PERSON_FRIENDS_COUNT, $this->getConfig()->permissions)) {
      $fields[] = 'nkFriendsCount';
    }

    $data = $this->call('/people/' . implode(',', $ids), array('fields' => implode(',', $fields)));
    $result = array();

    if (1 === count($data)) {
      $user = new NKUser();
      $user->assignData($data['entry']);
      $result[$user->id()] = $user;
    } else {
      foreach ($data['entry'] as $row) {
        $user = new NKUser();
        $user->assignData($row);
        $result[$user->id()] = $user;
      }
    }

    return $result;
  }

  /**
   * Dodaje na tablicy aktualnie zalogowanego użytkownika wpis o treści $content Dodatkowo możesz zawęzić widoczność
   * wpisu wypisu wyłącznie dla znajomych użytkownika, ustawiając parametr $only_friends na true
   *
   * @since 1.1
   *
   * @param $content
   * @param bool $only_friends
   *
   * @return bool
   *
   * @throws NKServiceInvalidParamsException
   */
  public function postActivity($content, $only_friends = false)
  {
    $length = strlen($content);
    if ($length < 1) {
      throw new NKServiceInvalidParamsException("Activity content is too short, it should have at least 1 char");
    } elseif ($length > 500) {
      throw new NKServiceInvalidParamsException("Activity content is too long, it should fit in 500 chars");
    }

    $url = '/activities/@me/' . ($only_friends ? '@friends' : '@all') . '/app.sledzik';
    $this->call($url, array('title' => $content), NKHttpClient::HTTP_POST);

    return true;
  }

  /**
   * @return NKHttpClient
   */
  protected function getHttpClient()
  {
    if (null === $this->http_client) {
      $this->http_client = new NKHttpClient();
    }
    return $this->http_client;
  }

  /**
   * Metoda pozwalająca na bezpośrednie wywołanie dowolnego serwisu API NK, zwraca dane w postaci tablicy, odpowiadającej
   * formatowi danych wywoływanego serwisu. Zwróć uwagę, iż nie wszystkie serwisy opisane w ogólnej dokumentacji są otwarte
   * w publicznym API
   *
   * @since 1.0
   *
   * @param $url
   * @param array $params
   * @param string $method
   *
   * @throws NKServiceHttpException|NKServiceMissingException|NKServiceMissingPermissionException|NKServiceInvalidParamsException
   * @return array
   */
  public function call($url, $params = array(), $method = NKHttpClient::HTTP_GET)
  {
    $url = self::BASE_URL . $url;

    if (NKHttpClient::HTTP_GET == $method) {
      $body = null;
      $params = array_merge(array("nk_token" => $this->getTokenProvider()->getToken()), $params);
      $url_params = $params;
    } elseif (NKHttpClient::HTTP_POST == $method) {
      $body = json_encode($params);
      $params = array(
        "nk_token"        => $this->getTokenProvider()->getToken(),
        "oauth_body_hash" => base64_encode(hash('sha1', $body, true))
      );
      $url_params = array("nk_token"=> $this->getTokenProvider()->getToken());
    } else {
      throw new NKServiceInvalidParamsException("Unknown HTTP method to call");
    }

    $request = OAuthRequest::from_consumer_and_token($this->getOAuthConsumer(), null, $method, $url, $params);
    $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->getOAuthConsumer(), null);

    $url = $url . "?" . OAuthUtil::build_http_query($url_params);
    $this->debugOutput($url);

    $auth_header = $request->to_header();
    $this->debugOutput($auth_header);
    
    $client = $this->getHttpClient();

    try {
      $client->exec($url, array($auth_header, 'Content-Type: application/json; charset=utf8'), $method, $body);
    }
    catch (NKHttpClientException $e) {
      throw new NKServiceHttpException($e->getMessage(), $e->getCode());
    }

    switch ($rc = $client->getResponseCode()) {
      case 0:
        throw new NKServiceHttpException("No HTTP request were executed");
        break;

      case 200:
        break;

      case 404:
        throw new NKServiceMissingException("No such service");
        break;

      case 401:
      case 403:
        throw new NKServiceMissingPermissionException("You need additional permissions to use this service ($rc)");
        break;

      default:
        throw new NKServiceHttpException("Invalid response HTTP code $rc");
        break;
    }
    
    $data = json_decode($client->getResponse(), true);
    $this->debugOutput($data);

    return $data;
  }

  /**
   * Włącza lub wyłącza debugowanie w zależności od poprzedniego stanu
   *
   * @since 1.0
   *
   * @return NKService
   */
  public function debug()
  {
    $this->debug = !$this->debug;
    return $this;
  }

  private function debugOutput($sth)
  {
    if ($this->debug) {
      echo "<pre>";
      var_dump($sth);
      echo "</pre>";
    }
  }
}
