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
 *
 * @package Http
 * @access private
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKHttpRequest
{
  /**
   *
   */
  private function __construct()
  {

  }

  private static $instance;

  /**
   * @static
   * @return NKHttpRequest
   */
  public static function singleton()
  {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   *
   * @return bool
   */
  public function headersSent()
  {
    return headers_sent();
  }

  public function header($value)
  {
    header($value);
    return $this;
  }

  /**
   *
   * @return array
   */
  public function getServerData()
  {
    return $_SERVER;
  }

  /**
   *
   * @return array
   */
  public function getPostData()
  {
    return $_POST;
  }

  /**
   *
   * @return array
   */
  public function getRequestData()
  {
    return $_REQUEST;
  }

  /**
   *
   * @return NKHttpRequest
   */
  public function startSessionIfRequired()
  {
    if ('' === session_id() && false === headers_sent()) {
      session_start();
    }
    return $this;
  }

  /**
   * 
   * @return array
   */
  public function getSessionData()
  {
    return $_SESSION;
  }

  /**
   *
   * @param $key
   * @param $value
   *
   * @return NKHttpRequest
   */
  public function setSessionData($key, $value)
  {
    $_SESSION[$key] = $value;
    return $this;
  }

  /**
   *
   * @param $key
   * @return NKHttpRequest
   */
  public function unsetSessionData($key)
  {
    unset($_SESSION[$key]);
    return $this;
  }

  /**
   * 
   * @return void
   */
  public function terminate()
  {
    exit;
  }

  /**
   *
   * @return int
   */
  public function getTime()
  {
    return (isset($_SERVER["REQUEST_TIME"]) ? $_SERVER["REQUEST_TIME"] : time());
  }
}
