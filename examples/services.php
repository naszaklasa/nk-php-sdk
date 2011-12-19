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

/*
 * Ten przykład pokazuje w jaki sposób uzyskać dostęp do danych portalu NK
 */

// Załaduj bibliotekę NK
require '../src/NK.php';

// Konfiguracja Twojej aplikacji, wszystkie potrzebne informacje znajdziesz w panelu konfiguracyjnym aplikacji
// Jeśli preferujesz bardziej obiektowy styl, popatrz na klasę NKConfig
$conf = array('permissions' => array(NKPermissions::BASIC_PROFILE, NKPermissions::PICTURES_PROFILE),
              'key'         => 'my_key',
              'secret'      => 'my_secret');

$auth = new NKConnect($conf);
$auth->handleCallback();

try {
  $service = $auth->getService();
}
catch (NKAuthenticationUnauthorisedException $e) {
  $service = null;
}
?>
<html>
  <head>
    <title>Demo services</title>
  </head>
  <body>
    <?php if ($auth->authenticated()): ?>
      Jesteś zalogowany jako <?php echo htmlspecialchars($auth->user()->name()) ?> <img src="<?php echo $auth->user()->thumbnailUrl() ?>" />
      <br />
      <br />
      <span style="color: red">Uwaga!</span>
      <br />
      Jeśli poniżej dostaniesz wyjątek braku dostępu, wyloguj się, i zaloguj ponownie, NK poprosi Cię o akceptacje nowego zestawu uprawnień.
      <br />
      <a href="<?php echo $auth->logoutLink() ?>">Wyloguj</a>
    <?php else: ?>
      <?php echo $auth->button() ?>
    <?php endif ?>

    <br />

    <?php if ($service): ?>
      Twoje albumy:
      <ol>
        <?php foreach ($service->photoAlbums($auth->user()) as $album): ?>
        <li><img src="<?php echo $album->thumbnailUrl() ?>" />Album: "<?php echo htmlspecialchars($album->title()) ?>"</li>
        <ol>
          <?php foreach ($service->photos($album) as $photo): ?>
          <li><img src="<?php echo $photo->thumbnailUrl() ?>" />Zdjęcie "<?php echo htmlspecialchars($photo->description()) ?>"</li>
          <?php endforeach ?>
        </ol>
        <?php endforeach ?>
      </ol>
    <?php endif ?>
    <br />
    &copy;NK.pl 2011
  </body>
</html>
