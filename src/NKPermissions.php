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
 * @package Config
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKPermissions
{
  /**
   * Udostępnia podstawowe dane użytkowników (obiekt NKUser)
   *
   */
  const BASIC_PROFILE        = 'BASIC_PROFILE_ROLE';

  /**
   * W obiekcie NKUser udostępnia pole birthday
   */
  const BIRTHDAY_PROFILE     = 'BIRTHDAY_PROFILE_ROLE';

  /**
   * W obiekcie NKUser udostępnia pole phoneNumber
   */
  const PHONE_PROFILE        = 'PHONE_PROFILE_ROLE';

  /**
   * W obiekcie NKUser udostępnia pole email
   */
  const EMAIL_PROFILE        = 'EMAIL_PROFILE_ROLE';

  /**
   * W obiekcie NKUser udostępnia pole friendsCount
   */
  const PERSON_FRIENDS       = 'PERSON_FRIENDS_ROLE';

  /**
   * Umożliwia poprzez obiekty NKPhotoAlbum i NKPhoto dostęp do galerii i zdjęć użytkownika
   */
  const PICTURES_PROFILE     = 'PICTURES_PROFILE_ROLE';

  /**
   * Pseudo-uprawnienie (użytkownik nie jest proszony o jego akceptację). Udostępnia ilość znajomych użytkownika
   * w obiekcie NKUser
   */
  const PERSON_FRIENDS_COUNT = 'PERSON_FRIENDS_COUNT_SELECTOR';

  /**
   * Pozwala na dodawanie wpisów na tablicy zalogowanego użytkownika
   */
  const CREATE_SHOUTS        = 'CREATE_SHOUTS_ROLE';

  /**
   * Zwraca zestaw uprawnień potrzebny do wyświetlenia podstawowych informacji o profilu, takich jak imię, nazwisko
   * avatar, miejscowość, etc. Zestaw powinien być użyty podczas budowania obiektu NKCongig i przypisany do propercji
   * ->permissions
   *
   * <code>
   * $conf = new NKConfig();
   * $conf->permissions = NKPermissions::profile_minimal();
   * [...]
   * </code>
   *
   * @since 1.0
   *
   * @static
   * @return array
   */
  public static function profile_minimal()
  {
    return array(self::BASIC_PROFILE);
  }

  /**
   * Zwraca zestaw wszystkich dostępnych uprawnień, o które możesz poprosić
   *
   * <code>
   * $conf = new NKConfig();
   * $conf->permissions = NKPermissions::all();
   * [...]
   * </code>
   *
   * @since 1.0
   *
   * @static
   * @return array
   */
  public static function all()
  {
    return array(
      self::BASIC_PROFILE,
      self::BIRTHDAY_PROFILE,
      self::PHONE_PROFILE,
      self::EMAIL_PROFILE,
      self::PICTURES_PROFILE,
      self::CREATE_SHOUTS,
    );
  }
}
