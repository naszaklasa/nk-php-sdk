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
class NKAuthenticationUnauthorisedException extends NKException
{

}

/**
 * @package Auth
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 * @abstract
 */
abstract class NKAuthentication implements NKTokenProvider
{
  /**
   * @var NKConfig
   */
  private $config;

  /**
   * @var NKServices
   */
  private $nkservice;

  /**
   * @var NKUser
   */
  private $user;

  /**
   *
   * @param array|NKConfig $config
   */
  public function __construct($config = null)
  {
    if (null !== $config) {
      $this->setConfig($config);
    }
  }

  /**
   * Ustawia obiekt konfiguracji, który będzie używany przez instancje klasy
   *
   * @throws NKConfigException
   *
   * @param array|NKConfig $config
   * @return NKAuthentication
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
   * Metoda zwraca obiekt NKService, obsługujący wszystkie dostępne przez publiczne API serwisy NK dla aktualnie zalogowanego
   * użytkownika.
   *
   * @throws NKAuthenticationUnauthorisedException
   * @return NKService
   */
  public function getService()
  {
    if (true  !== $this->tokenAvailable()) {
      throw new NKAuthenticationUnauthorisedException("Unable to get NKService object because user is not authenticated");
    }
    if (null === $this->nkservice) {
      $this->nkservice = new NKService($this->getConfig(), $this);
    }
    return $this->nkservice;
  }

  /**
   * Metoda pozwala na sprawdzenie stanu sesji. Jeśli użytkownik jest poprawnie zalogowany zwróci true, w przeciwnym
   * wypadku false
   *
   * @return bool
   */
  public function authenticated()
  {
    try {
      $this->user();
      return true;
    }
    catch(Exception $e) {
      return false;
    }
  }

  /**
   * Metoda, która zwraca obiekt aktualnie zalogowanego użytkownika (NKUser) Obiekt udostępnia dane o użytkowniku w
   * zależności od zakres uprawnień o który poprosisz
   *
   * @throws NKAuthenticationUnauthorisedException
   * @return NKUser
   */
  public function user()
  {
    if (true  !== $this->tokenAvailable()) {
      throw new NKAuthenticationUnauthorisedException("Unable to determine session user because there is no authentication session");
    }
    if (null === $this->user) {
      $this->user = $this->getService()->me();
    }
    return $this->user;
  }
}
