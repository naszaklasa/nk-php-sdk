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

// Załaduj bibliotekę NK
require '../src/NK.php';

// Konfiguracja Twojej aplikacji, wszystkie potrzebne informacje znajdziesz w panelu konfiguracyjnym aplikacji
$conf = new NKConfig();
$conf->permissions = array(NKPermissions::BASIC_PROFILE, NKPermissions::EMAIL_PROFILE, NKPermissions::CREATE_SHOUTS);
$conf->key = 'demo';
$conf->secret = 'b27d8aa6-74ee-4bbc-9ea1-0a3e5acc9bb8';
$conf->callback_url = 'http://172.19.32.1/~akurylowicz/sdk/nk-php-sdk/examples/authentication2callback.php';

$db = new PDO("sqlite:authentication2.s3db", null, null, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
