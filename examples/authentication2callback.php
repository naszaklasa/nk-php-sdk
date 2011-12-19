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
if ($auth->handleCallback()) {

  // Tutaj umieść kod, który wykonany zostanie po udanym zalogowaniu użytkownika na NK, wsztstkie dane użytkownika
  // dostępne są poprzez $auth->user() - możesz także skorzystać z dostępnych serwisów NK, przykłady znajdziesz w
  // pliku services.php.

  // Poniższy kawałek kodu jest prostym przykładem, służy do zademonstrowania *przykładowego* sposobu obsługi
  // użytkowników we własnej bazie danych

  // Sprawdź używając NK id, czy użytkownik znajduje się w bazie danych
  $q = $db->prepare("SELECT * FROM `users` WHERE `nk_person_id` = :nk_person_id");
  $q->bindValue('nk_person_id', $auth->user()->id(), PDO::PARAM_STR);
  $q->execute();

  if (false === ($u = $q->fetch(PDO::FETCH_ASSOC))) {

    // W bazie nie ma użytkownika, zarejestrujmy go
    $q = $db->prepare("INSERT INTO `users` (`nk_person_id`, `name`, `last_login_1`, `last_login_2`, `login_count`) VALUES (:nk_person_id, :name, :now, :now, 1)");
    $q->bindValue('nk_person_id', $auth->user()->id(), PDO::PARAM_STR);
    $q->bindValue('name', $auth->user()->name(), PDO::PARAM_STR);
    $q->bindValue('now', @date("Y-m-d H:i:s"), PDO::PARAM_STR);
    $q->execute();
  }
  else {

    // Użytkownik istnieje, zaktualizujmy datę ostatniego logowania i podbijmy licznik
    $q = $db->prepare("UPDATE `users` SET `last_login_2` = `last_login_1`, `last_login_1` = :now, `login_count` = (`login_count` + 1) WHERE `id` = :id");
    $q->bindValue('id', $u['id'], PDO::PARAM_INT);
    $q->bindValue('now', @date("Y-m-d H:i:s"), PDO::PARAM_STR);
    $q->execute();
  }

  // Ustaw w sesji informacje o zalogowaniu się użytkownika
  $_SESSION['logged_in'] = true;

  // Przekieruj na stronę początkową
  header("Location: authentication2.php");
}
elseif ($error = $auth->getError()) {

  // Wystąpił jakiś błąd podczas procesu autentykacji, wyświetl go lub zrób cokolwiek innego aby go obsłużyć
  echo "<html><head></head><body><div>" . htmlspecialchars($error) . "</div></body></html>";
}
