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
class NKHttpClientException extends NKException
{

}

/**
 *
 * @package Http
 * @access private
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKHttpClient
{
  const UA = 'nk-php-sdk';

  const HTTP_GET =  'GET';
  const HTTP_POST =  'POST';

  private $curl;
  private $response;
  private $response_code = 0;
  
  protected function getCurl() {
    return $this->curl;
  }

  protected function initCurl()
  {
    if (null === $this->curl) {
      $this->curl = curl_init();
      curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($this->curl, CURLINFO_HEADER_OUT, true);
      curl_setopt($this->curl, CURLOPT_FAILONERROR, false);
      curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($this->curl, CURLOPT_TIMEOUT, 5);
      curl_setopt($this->curl, CURLOPT_USERAGENT, sprintf("%s: %s", self::UA, NKService::VERSION));
    }
  }

  /**
   *
   * @throws NKHttpClientException
   * @param $url
   * @param array $headers
   *
   * @return NKHttpClientException
   */
  public function exec($url, array $headers, $method = self::HTTP_GET, $postfields = null)
  {
    $this->response = null;
    $this->response_code = 0;

    $this->initCurl();

    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
    if (self::HTTP_POST == $method && null !== $postfields) {
      curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postfields);
    }
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

    $this->response = curl_exec($this->curl);
    if ($errno = curl_errno($this->curl)) {
      throw new NKHttpClientException(curl_error($this->curl), $errno);
    }

    $this->response_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    return $this;
  }

  /**
   *
   * @return string
   */
  public function getResponse()
  {
    $string = trim($this->response);
    return $string;
  }

  /**
   *
   * @return int
   */
  public function getResponseCode()
  {
    return $this->response_code;
  }
  
  public function __destruct()
  {
    if (null !== $this->curl) {
      curl_close($this->curl);
    }
  }
}
