# Instrukcja instalacji QR-System Professional

## 🚀 Szybka instalacja (5 minut)

### Krok 1: Przygotowanie
1. **Utwórz backup** swojej strony WordPress
2. **Sprawdź wymagania**:
   - WordPress 5.0+
   - PHP 7.4+
   - HTTPS (dla funkcji kamery)

### Krok 2: Instalacja wtyczki

#### Opcja A: Upload przez panel WP
1. Sciągnij plik `qr-system-professional.zip`
2. WP Admin → Wtyczki → Dodaj nową → Wyślij wtyczkę na serwer
3. Wybierz plik ZIP i kliknij "Zainstaluj teraz"
4. **Aktywuj wtyczkę**

#### Opcja B: Upload FTP
1. Wypakuj folder `qr-system-professional`
2. Upload do `/wp-content/plugins/`
3. WP Admin → Wtyczki → Aktywuj "QR-System Professional"

### Krok 3: Automatyczna konfiguracja
✅ System automatycznie:
- Tworzy tabele bazy danych
- Dodaje przykładowe grupy i kody
- Tworzy stronę `/skanowanie/`
- Ustawia domyślne opcje

### Krok 4: Konfiguracja użytkowników
1. **WP Admin → Użytkownicy → Wszyscy użytkownicy**
2. **Edytuj profil** każdego franczyzobiorcy
3. **Sekcja "Adresy punktów"** - dodaj lokalizacje:
   ```
   Adres punktu 1: Kraków, ul. Floriańska 1
   Adres punktu 2: Kraków, ul. Grodzka 15
   itd.
   ```

### Krok 5: Test systemu
1. Przejdź na `/skanowanie/`
2. Zaloguj się jako franczyzobiorca
3. **Sprawdź kamery** - powinny się załadować
4. **Test kod**: `TESTQR001`
5. **Wybierz lokalizację** i zeskanuj

## 🔧 Konfiguracja zaawansowana

### Ustawienia systemowe
**QR-System → Ustawienia**

#### Skanowanie
- ✅ Wymagaj potwierdzenia skanów
- ❌ Pozwól na wielokrotne skanowanie  
- ✅ Włącz szczegółowe logowanie

#### Archiwizacja
- **Auto archiwizacja**: 30 dni
- **Maks kodów na grupę**: 1000

### Dodawanie kodów QR
**QR-System → Kody QR → Dodaj nowy**

#### Kod normalny
```
Kod: PROMO2025
Typ: Normal
Wiadomość: Rabat 20% na wszystko
Data ważności: 2025-12-31 23:59
Maksymalne użycia: 100
Grupa: Promocje specjalne
```

#### Kod specjalny (tylko uprzywilejowani)
```
Kod: VIPSPECIAL
Typ: Special
Wiadomość: Kod VIP - dostęp ograniczony
Data ważności: 2025-06-30 23:59
Maksymalne użycia: 50
Grupa: Kody VIP
```

### Zarządzanie grupami
**QR-System → Grupy → Dodaj nową**

```
Nazwa: Promocje Świąteczne
Opis: Kody promocyjne na okres świąteczny
Kolor: #e74c3c (czerwony)
```

## 🏥 Migracja z poprzedniego systemu

### Automatyczna kompatybilność
✅ **System automatycznie obsługuje**:
- Stare post types (`kody-rabatowe`, `kody-rabatowem`)
- Legacy AJAX endpoints
- Istniejące shortcodes
- Profile użytkowników

### Migracja danych (opcjonalna)
1. **Eksportuj stare dane** (jeśli potrzebne)
2. **QR-System → Ustawienia → "Napraw tabele"**
3. **Sprawdź funkcjonalność** na stronie `/skanowanie/`

## 🛠 Rozwiązywanie problemów

### ❌ Błąd: "Unknown column 'max_uses'"
**Rozwiązanie:**
1. QR-System → Ustawienia → "Napraw tabele"
2. Lub: Dezaktywuj i reaktywuj wtyczkę

### ❌ Kamery nie działają
**Przyczyny i rozwiązania:**
- **Brak HTTPS**: Przełącz na SSL
- **Brak uprawnień**: Przeglądarką → Ustawienia → Zezwól na kamerę
- **Stara przeglądarka**: Aktualizuj do nowszej wersji

### ❌ Błąd AJAX / "Brak połączenia"
**Sprawdź:**
1. **Logi błędów** (cPanel → Pliki dziennika błędów)
2. **Limity PHP**: zwiększ `memory_limit`, `max_execution_time`
3. **Wtyczki konfliktujące**: Wyłącz inne wtyczki

### ❌ "Brak uprawnień do tego kodu"
**Kod specjalny** - tylko dla:
- `tomasz.szulik@sweetgallery.pl`
- `kontakt@sarnetski.pl`

**Rozwiązanie**: Zmień typ kodu na "Normal" lub dodaj email do listy uprzywilejowanych w kodzie.

## 📱 Instrukcja dla użytkowników końcowych

### Jak skanować kod QR?

#### Na komputerze
1. Przejdź na `twoja-domena.pl/skanowanie/`
2. **Zaloguj się** swoimi danymi
3. **Wybierz lokalizację** z listy
4. **Wpisz kod ręcznie** w polu tekstowym
5. Kliknij **"Sprawdź"**

#### Na telefonie
1. Przejdź na `twoja-domena.pl/skanowanie/`
2. **Zaloguj się** swoimi danymi  
3. **Wybierz lokalizację** z listy
4. **Wybierz kamerę** (tylna zalecana)
5. **Rozpocznij skanowanie**
6. **Skieruj kamerę** na kod QR
7. **Potwierdź** wykryty kod

### Wyniki skanowania
- 🟢 **Sukces** - Kod poprawny, rabat naliczony
- 🔴 **Błąd** - Kod wykorzystany lub nie istnieje  
- 🟡 **Wygasł** - Kod przestarzały
- ⚫ **Brak uprawnień** - Kod specjalny, brak dostępu

## 🔒 Bezpieczeństwo

### Najlepsze praktyki
1. **Regularne backupy** - Szczególnie przed aktualizacjami
2. **Silne hasła** - Dla wszystkich użytkowników systemu
3. **HTTPS** - Wymagane dla funkcji kamery
4. **Aktualizacje** - WordPress, wtyczki, motywy

### Monitoring
- **QR-System → Historia skanów** - Sprawdzaj podejrzaną aktywność
- **QR-System → Statystyki** - Analizuj wzorce użytkowania
- **Logi serwera** - Błędy i ostrzeżenia systemu

## 📞 Wsparcie techniczne

### Przed zgłoszeniem
1. **Sprawdź logi błędów** - cPanel → Pliki dziennika błędów
2. **QR-System → Ustawienia → Informacje systemowe**
3. **Wyeksportuj dane** - Jako backup przed zmianami

### Kontakt
📧 **Email**: kontakt@sarnetski.pl
🌐 **Web**: https://szymonsarnecki.pl

### W zgłoszeniu podaj
- Wersja WordPress
- Wersja PHP  
- Opis problemu
- Zrzuty ekranu (jeśli możliwe)
- Logi błędów

---

**Powodzenia z nowym systemem QR! 🎉**

Szymon Sarnecki  
kontakt@sarnetski.pl
