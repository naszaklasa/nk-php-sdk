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
 * Wrapper na token, klasa może być użyta jeśli samodzielnie obsługujesz mechanizm pozyskania tokena. W większości przypadków
 * powinieneś użyć któregoś z mechanizmów autentykacyjnych, eg. NKConnect
 *
 * @package Auth
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKToken extends NKAuthentication
{
  private $token = '';

  public function __construct($token = null)
  {
    if (null !== $token) {
      $this->setToken($token);
    }
  }

  /**
   *
   * @param $token
   * @return void
   */
  public function setToken($token)
  {
    $this->token = trim($token);
  }

  /**
   * Metoda zwraca token lub null jeśli token nie jest dostępny
   *
   * @return string
   */
  public function getToken()
  {
    return empty($this->token) ? null : $this->token;
  }

  /**
   * Metoda zwraca true jeśli token jest dostępny. W przeciwnym przypadku zwracane jest false
   *
   * @return bool
   */
  public function tokenAvailable()
  {
    return false === empty($this->token);
  }
}
