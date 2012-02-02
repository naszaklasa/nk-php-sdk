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

/*
 * Konfiguracja Twojej aplikacji, wszystkie potrzebne informacje znajdziesz w panelu konfiguracyjnym aplikacji
 * Jeśli preferujesz bardziej obiektowy styl, popatrz na klasę NKConfig
 */
$conf = array('permissions' => array(NKPermissions::BASIC_PROFILE),
              'key'         => 'demo',
              'secret'      => 'b27d8aa6-74ee-4bbc-9ea1-0a3e5acc9bb8');

/*
 * Zanim rozpoczniesz renderowanie HTMLa utwórz obiekt NKConnect i pozwól mu obsłużyć na tej proces
 * logowania (handleCallback). Jeśli chcesz lub musisz obsłużyć callback na osobnej stronie, zaimplementować jakąś logikę
 * wykonywaną w momencie zalogowania w NK lub połączyć rejestrację/własną bazę użytkowników popatrz na przykład z pliku
 * authentication2.php
 */
$auth = new NKConnect($conf);
$auth->handleCallback();
?>
<html>
  <head>
    <title>Demo autentykacji z użyciem NKConnect</title>
  </head>
  <body>
    <p>Ten przykład pokazuje sposób w jaki można użyć NKConnect do logowania bez potrzeby używania dodatkowej strony
    obsługującej callback. Dla nie zalogowanego użytkownika wyświetlamy przycisk "Zaloguj z NK" który otworzy stronę
    logowania w portalu NK i poprosi użytkownika o akceptację dostępu do danych. Po zalogowaniu uruchamiana jest sesja,
    dzięki czemu na dowolnej stronie, poprzez użycie</p>
    <pre>
    $auth = new NKConnect($conf);
    $user = $auth->user()
    </pre>
    <p>masz dostęp do informacji o zalogowanym użytkowniku.</p>
    
    <?php if ($auth->authenticated()): ?>
      Jesteś zalogowany jako <?php echo htmlspecialchars($auth->user()->name()) ?>.<br />
      <img src="<?php echo $auth->user()->thumbnailUrl() ?>"  alt="Thumb"/><br />
      <a href="<?php echo $auth->logoutLink() ?>">Wyloguj</a>
    <?php else: ?>
      <?php echo $auth->button()  ?>
    <?php endif ?>

    <br />
    <br />
    &copy;NK.pl 2012
  </body>
</html>
