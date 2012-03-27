PHP SDK dla otwartego API portalu NK.pl
=======================================

Aktualna wersja to 1.2, 'master' jest gałęzią developerską, gałęzie stabilne znajdują się w branchach
z numerami wersji.

Przykłady
---------

Przykłady użycia znajdziesz w katalogu 'examples/'
Na stronach http://developers.nk.pl udostępniamy dokumentację zawierającą szczegółowy opis integracji.

Społeczność
---------

Informacje o nowościach znajdziesz na stronie http://developers.nk.pl, oferujemy tam także support w postaci
FAQ a także dokumentacji.

Changelog
---------
1.2

* Dodano automatyczne odświeżanie tokena

1.1

* Zastąpiono w autentykacji NKConnect flow JS na flow server to server (oauth2), flow JS jest depricated
* Dodano serwis dodawania wpisów użytkownika wraz z nowym uprawnieniem NKPermissions::CREATE_SHOUTS
* poprawiono i rozbudowano przykłady użycia

1.0: pierwsza wersja:

* Autentykacja NKConnect z użyciem flow JS
* Dostęp do danych użytkownika
* Dostęp do galerii użytkownika
* Dostęp do zdjęć użytkownika

Testy
---------

Uruchamiaj testy używywając autoloadera:

  cd nk-php-sdk/
  phpunit -d display_errors=1 --colors --bootstrap ./src/NK.php ./tests/
