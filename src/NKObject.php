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
 * @package Service
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 * @abstract
 */
abstract class NKObject
{
  /**
   * @abstract
   * @access private
   * @param array $data
   * @return void
   */
  abstract public function assignData(array $data);
  
  /**
   * Konwertuje obiekt do formatu JSON
   *
   * @return string
   */
  public function asJson()
  {
    return json_encode($this->asArray());
  }

  /**
   * Konwertuje obiekt do tablicy
   *
   * @return array
   */
  public function asArray()
  {
    $i = array('__construct', 'assignData', 'asJson', 'asArray');
    $a = array();
    foreach (get_class_methods($this) as $m) {
      if (false === in_array($m, $i)) {
        $a[$m] = $this->{$m}();
      }
    }
    return $a;
  }
}
