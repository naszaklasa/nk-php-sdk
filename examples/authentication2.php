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

// Załaduj wspólny plik konfiguracji
require 'authentication2config.php';

$auth = new NKConnect($conf);
if ($auth->authenticated()) {

  // Ten kawałek kodu próbuje odszukać w lokalnej bazie danych użytkownika zalogowanego przez NK
  // Jeśli użytkownik jest zalogowany spróbuj pobrać jego dane
  $q = $db->prepare("SELECT * FROM `users` WHERE `nk_person_id` = :nk_person_id");
  $q->bindValue('nk_person_id', $auth->user()->id(), PDO::PARAM_STR);
  $q->execute();
  $user = $q->fetch(PDO::FETCH_ASSOC);
}
?>
<html>
  <head>
    <title>Demo autentykacji z użyciem NKConnect i wydzielonej strony z callbackiem</title>
  </head>
  <body>
    <p>Ten przykład pokazuje sposób w jaki można użyć NKConnect do logowania, z użyciem osobnej strony obsługującej callback
    wraz z integracją z istniejącą bazą danych użytkowników/rejestrowaniem nowych użytkowników. Adres strony z kodem callbacka
    powinieneś określić w konfiguracji. Na stronie logowania, lub dowolnych innych umieść przycisk, na stronie
    obsługującej callback umieść kod podobny do tego, który znajdziesz w przykładzie authentication2callback.php
    Po zalogowaniu uruchamiana jest sesja, dzięki czemu na dowolnej stronie, poprzez użycie</p>
    <pre>
    $auth = new NKConnect($conf);
    $user = $auth->user()
    </pre>
    <p>masz dostęp do informacji o zalogowanym użytkowniku.</p>

    <?php if ($auth->authenticated()): ?>
      Jesteś zalogowany jako <?php echo htmlspecialchars($auth->user()->name()) ?>, adres email <strong><?php echo htmlspecialchars($auth->user()->email()) ?></strong>
      ostatnie logowanie <?php echo $user['last_login_2'] ?>, logowałeś się <?php echo $user['login_count'] ?> razy.<br />
      <img alt="avatar" src="<?php echo $auth->user()->thumbnailUrl() ?>" /><br />
      <a href="authentication2logout.php">Wyloguj</a>
    <?php else: ?>
      <?php echo $auth->button() ?>
    <?php endif ?>

    <p><strong>Lista użytkowników:</strong></p>

    <pre><?php
    $q = $db->prepare("SELECT * FROM `users`");
    $q->execute();
    while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
      echo $r['nk_person_id'] . ': ' . htmlspecialchars($r['name']) . "\n";
    }
    ?></pre>

    <br />
    <br />
    &copy;NK.pl 2012
  </body>
</html>
