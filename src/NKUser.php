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
 * @package Service
 * @author Arkadiusz Kuryłowicz <arkadiusz.kurylowicz@nasza-klasa.pl>
 * @link http://developers.nk.pl
 */
class NKUser extends NKObject
{
  /**
   * Identyfikator płci męskiej użytkownika, używane do identyfikacji płci zwracanej przez NKUser::gender()
   */
  const GENDER_MALE = 'male';

  /**
   * Identyfikator płci żeńskiej użytkownika, używane do identyfikacji płci zwracanej przez NKUser::gender()
   */
  const GENDER_FEMALE = 'female';

  private $age;
  private $gender;
  private $friendsCount;
  private $email;
  private $id;
  private $thumbnailUrl;
  private $name;
  private $phoneNumber;
  private $photos = array();
  private $location;
  private $birthday;

  /**
   * Konstruktor, jeśli potrzebujesz użyć instancji klasy NKUser do identyfikacji użytkownika w innych serwisach (photos())
   * bez konieczności pobierania jego danych z API NK (trzymasz person_id aka id) we własnej bazie, przekaż go jako  argument
   * konstruktora
   *
   * @param string $id
   */
  public function __construct($id = null)
  {
    $this->id = $id;
  }

  /**
   * Identyfikator użytkownika, unikalny w zakresie danej aplikacji (key/secret)
   *
   * @return string
   */
  public function id()
  {
    return $this->id;
  }

  /**
   * Sformatowane imię i nazwisko użytkownika
   *
   * @return string
   */
  public function name()
  {
    return $this->name;
  }

  /**
   * Tablica zawierająca zdjęcia powiązane z użytkownikiem (avatar, zdjęcie profilowe)
   *
   * @return array
   */
  public function photos()
  {
    return $this->photos;
  }

  /**
   * URL wskazujący na plik graficzny zawierający avatar użytkownika
   *
   * @return string
   */
  public function thumbnailUrl()
  {
    return $this->thumbnailUrl;
  }

  /**
   * Adres email, aby uzyskać do niego dostęp wymagane jest uprawnienie NKPermissions::EMAIL_PROFILE, jeśli o nie nie
   * poprosisz zwracany jest null
   *
   * @return string
   */
  public function email()
  {
    return $this->email;
  }

  /**
   * Wiek użytkownika
   *
   * @return int
   */
  public function age()
  {
    return $this->age;
  }

  /**
   * Płeć użytkownika w formie opisowej (male, female). Do identyfikacji, powinieneś użyć stałych NKUser::GENDER_MALE
   * NKUser::GENDER_FEMALE
   *
   * @return string
   */
  public function gender()
  {
    return $this->gender;
  }

  /**
   * Lokalizacja użytkownika, tak jak podaje w swoim profilu, zazwyczaj miasto. Nie walidujemy poprawności podanej
   * przez użytkownika lokalizacji, dla tego jeśli jest ona dla Ciebie kluczowa powinieneś walidować ją samodzielnie
   *
   * @return string
   */
  public function location()
  {
    return $this->location;
  }

  /**
   * Ilość znajomych użytkownika, zwraca domyślnie null aby uzyskać dostęp do liczby znajomych musisz użyć pseudo-uprawnienia
   * NKPermissions::PERSON_FRIENDS_COUNT
   *
   * @return integer
   */
  public function friendsCount()
  {
    return $this->friendsCount;
  }

  /**
   * Numer telefonu użytkownika, tak jak podaje w swoim profilu. NIe walidujemy poprawności podanego
   * przez użytkownika numeru, dla tego jeśli jest on dla Ciebie kluczowy powinieneś walidować go samodzielnie
   *
   * @return string
   */
  public function phoneNumber()
  {
    return $this->phoneNumber;
  }

  /**
   * Data urodzin użytkownika, w formacie YYYY-MM-DD - aby uzyskać dostęp do daty urodzin, powinieneś poprosić o
   * uprawnienie NKPermissions::BIRTHDAY_PROFILE
   *
   * @return string
   */
  public function birthday()
  {
    return $this->birthday;
  }

  /**
   *
   * @access private
   * @param array $data
   * @return void
   */
  public function assignData(array $data)
  {
    $this->id = $data['id'];
    $this->thumbnailUrl = $data['thumbnailUrl'];
    $this->name = $data['displayName'];
    $this->photos = $data['photos'];
    $this->age = (int)$data['age'];
    $this->location = $data['currentLocation']['region'];
    
    if (isset($data['gender'])) {
      $this->gender = $data['gender'];
    }
    if (isset($data['nkFriendsCount'])) {
      $this->friendsCount = $data['nkFriendsCount'];
    }

    if (isset($data['emails']) && is_array($data['emails']) && count($data['emails']) > 0) {
      $this->email = $data['emails'][0]['value'];
    }
    if (isset($data['phoneNumbers']) && is_array($data['phoneNumbers']) && count($data['phoneNumbers']) > 0) {
      $this->phoneNumber = $data['phoneNumbers'][0]['value'];
    }
    if (isset($data['birthday'])) {
      $this->birthday = @date("Y-m-d", @strtotime($data['birthday']));
    }
  }
}
