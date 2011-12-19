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
 * Plik rejestruje własny autoloader, obsługujący wyłącznie drzewo plików związanych z SDK
 *
 * @access private
 * @package Core
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
include_once dirname(__FILE__) . '/OAuth.php';

function nk_autoload($class)
{
  if ('NK' == substr($class, 0, 2)) {
    $d = dirname(__FILE__);
    $f = sprintf("%s/%s.php", $d, $class);
    if (true === file_exists($f)) {
      require $f;
    }
  }
}

spl_autoload_register('nk_autoload');
