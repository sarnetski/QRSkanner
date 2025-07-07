# 🚀 Szybka instalacja QR-System

## Krok 1: Instalacja wtyczki

1. **Pobierz** plik `qr-system-simple.zip`
2. **Przejdź** do panelu WordPress → **Wtyczki** → **Dodaj nową**
3. **Kliknij** "Wyślij wtyczkę na serwer"
4. **Wybierz** plik `qr-system-simple.zip`
5. **Kliknij** "Zainstaluj teraz"
6. **Aktywuj** wtyczkę

**LUB** skopiuj ręcznie folder `qr-system-simple` do `/wp-content/plugins/`

---

## Krok 2: Utworzenie strony skanowania

1. **Przejdź** do **Strony** → **Dodaj nową**
2. **Tytuł:** `Skanowanie`
3. **Slug:** `skanowanie` (w ustawieniach strony)
4. **Treść:** Wstaw shortcode: `[qr_scanner]`
5. **Opublikuj** stronę

---

## Krok 3: Dodanie pierwszego franczyzobiorcy

1. **Przejdź** do **Użytkownicy** → **Dodaj użytkownika**
2. **Wypełnij dane:**
   - Nazwa użytkownika: `franczyza1`
   - Email: `franczyza1@example.com`  
   - Hasło: (wygeneruj lub ustaw)
   - **Rola:** `Subscriber`
3. **Dodaj użytkownika**
4. **Edytuj** profil tego użytkownika
5. **Znajdź** sekcję "Lokalizacje QR-System"
6. **Dodaj** adresy punktów (np. "Warszawa Centrum", "Kraków Główny")

---

## Krok 4: Dodanie pierwszego kodu QR

1. **Przejdź** do **QR-System** → **Kody QR**
2. **Kliknij** "Dodaj kod QR"
3. **Wypełnij:**
   - **Kod QR:** `TEST123` (lub kliknij "Generuj kod")
   - **Typ:** Normalny
   - **Wiadomość:** "Gratulacje! Kod został zeskanowany pomyślnie!"
   - **Data ważności:** (pozostaw puste dla bezterminowego)
   - **Maksymalna liczba użyć:** `1`
4. **Zapisz** kod

---

## Krok 5: Test systemu

### Test jako administrator:
1. **Przejdź** do **QR-System** → **Dashboard**
2. **Sprawdź** czy statystyki wyświetlają się poprawnie (liczby, nie błędy)

### Test jako franczyzobiorca:
1. **Zaloguj się** jako franczyzobiorca (lub otwórz incognito)
2. **Przejdź** na stronę `/skanowanie/`
3. **Sprawdź** czy:
   - Lista lokalizacji się wyświetla
   - Można wybrać kamerę
   - Można wpisać kod ręcznie
4. **Wpisz** kod `TEST123` ręcznie
5. **Wybierz** lokalizację
6. **Kliknij** "Sprawdź kod"
7. **Powinieneś** zobaczyć komunikat sukcesu

### Sprawdź historię:
1. **Wróć** do panelu administratora
2. **Przejdź** do **QR-System** → **Skany**
3. **Sprawdź** czy skan został zapisany

---

## 🆘 Szybkie rozwiązywanie problemów

### ❌ Błąd: "Fatal error" lub "Database error"
**Rozwiązanie:** 
- Dezaktywuj wtyczkę
- Aktywuj ponownie (to utworzy tabele)

### ❌ Strona skanowania nie działa
**Rozwiązanie:**
- Sprawdź czy shortcode `[qr_scanner]` jest w treści strony
- Sprawdź czy strona ma slug `skanowanie`

### ❌ Franczyzobiorca nie widzi lokalizacji
**Rozwiązanie:**
- Edytuj profil użytkownika
- Dodaj lokalizacje w sekcji "Lokalizacje QR-System"

### ❌ Kamera nie działa
**Rozwiązanie:**
- Użyj HTTPS (wymagane dla kamery)
- Sprawdź uprawnienia przeglądarki
- Spróbuj innej przeglądarki

---

## ✅ System jest gotowy gdy:

- [ ] Dashboard wyświetla statystyki bez błędów
- [ ] Można dodawać kody QR  
- [ ] Strona `/skanowanie/` działa
- [ ] Franczyzobiorcy mogą skanować kody
- [ ] Historia skanów się zapisuje
- [ ] Eksport CSV działa

---

## 📞 Potrzebujesz pomocy?

**Email:** kontakt@szymonsarnecki.pl  
**Strona:** https://szymonsarnecki.pl/

**Załącz przy zgłoszeniu:**
- Wersję WordPress
- Wersję PHP  
- Komunikaty błędów z debug.log
- Screenshots problemów