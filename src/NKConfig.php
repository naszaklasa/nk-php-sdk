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
 * Klasa transportująca konfigurację, można go instancjonować bezpośrednio, ustawić wartości propertisów i przekazywać
 * jako argument do klas które tego wymagają.
 *
 * Możesz jako argument konstruktora podać tablicę klucz=>wartość zawierającą nazwy opcji i ich wartości
 *
 * <code>
 * $conf = new NKConfig(array('key' => 'mykey', 'secret' => 'mysecret', 'permissions' => array(NKPermissions::BASIC_PROFILE)));
 * </code>
 *
 * lub przypisywać wartości do publicznych propercji obiektu
 *
 * <code>
 * $conf = new NKConfig();
 * $conf->permissions = array(NKPermissions::BASIC_PROFILE);
 * $conf->key = 'some_key';
 * $conf->secret = 'some_secret';
 * </code>
 *
 * @package Config
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKConfig
{
  /**
   *
   * @param array|null $config
   */
  public function __construct(array $config = null)
  {
    if (null !== $config) {
      $this->fromArray($config);
    }
  }

  /**
   * Opcjonalne: Tablica zawierająca zestaw uprawnień opisanych stałymi, zdefiniowanymi w klasie NKPermissions. Powinieneś
   * tutaj określić wszystkie potrzebne Ci uprawnienia do danych użytkownika. W momencie pierwszego użycia aplikacji użytkownik
   * zostanie poproszony o akceptację wymaganych przez Ciebie uprawnień. Jeśli na działającej aplikacji zmienisz zestaw
   * wymaganych uprawnień użytkownicy, podczas procesu autentykacji zostaną poproszeni o ich ponowną akceptację. Pole
   * opcjonalne
   *
   * Zwróć uwagę, iż niektóre serwisy, w przypadku braku uprawnień zwracają uboższy zestaw informacji lub wyjątek
   * NKServiceMissingPermissionException
   *
   * @since 1.0
   * @optional
   * @var array
   */
  public $permissions = array();

  /**
   * Wymagane: unikatowy klucz Twojej strony lub aplikacji, to ten zam klucz, który podałeś w panelu administracyjnym
   *
   * @since 1.0
   * @required
   * @var string
   */
  public $key;

  /**
   * Wymagane: klucz prywatny aplikacji (sekret) automatycznie przydzielany Twojej stronie lub aplikacji, w momencie
   * jej utworzenia
   *
   * @since 1.0
   * @required
   * @var string
   */
  public $secret;

  /**
   * Opcjonalne: pełny URL do strony obsługującej callback lub pusty string, jeśli chcesz użyć domyślnego URLa (tego, który
   * podałeś w panelu administracyjnym) W przypadku mechanizmów autentykacji, jeśli $callback_url będzie pusty, callback
   * zostanie zarejestrowany na każdej stronie, na której stworzysz obiekt i wywołasz na nim metodę handleCallback()
   *
   * @since 1.0
   * @optional
   * @var string
   */
  public $callback_url = '';

  /**
   * Opcjonalne: URL do pliku jQuery, jeśli używasz własnej wersji możesz ją tu podać, przy czym zwróć uwagę, iż gwarantujemy
   * poprawne działanie SDK z wersją jQuery predefiniowaną tutaj.
   *
   * @since 1.0
   * @optional
   * @var string
   */
  public $jquery_src = 'http://code.jquery.com/jquery-1.6.4.min.js';

  /**
   * Ustawia opcje konfiguracyjne używając tablicy asocjacyjnej $config
   *
   * @param array $config
   * @return void
   */
  public function fromArray(array $config)
  {
    foreach ($config as $k => $v) {
      if (property_exists($this, $k)) {
        $this->{$k} = $v;
      }
    }
  }
}
