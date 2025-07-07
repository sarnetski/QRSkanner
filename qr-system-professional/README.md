# QR-System Professional

Zaawansowany system kodów QR dla WordPress z pełną kompatybilnością z poprzednimi wersjami.

## ✨ Funkcjonalności

### 🎯 Podstawowe funkcje
- **Skanowanie kodów QR** - Nowoczesny interfejs z obsługą kamer
- **Ręczne wprowadzanie kodów** - Alternatywa dla skanowania
- **Zarządzanie grupami** - Organizacja kodów w logiczne grupy
- **Historia skanów** - Szczegółowy monitoring aktywności
- **Statystyki i analityka** - Kompleksowe raporty wydajności

### 🔧 Zaawansowane funkcje
- **Panel administracyjny** - Intuitive zarządzanie systemem
- **Eksport danych** - CSV, PDF i inne formaty
- **Archiwizacja** - Automatyczne czyszczenie starych danych
- **Zabezpieczenia** - Uprawnienia, nonces, sanitization
- **Kompatybilność** - Pełna kompatybilność z legacy systemem

### 📱 Interfejs użytkownika
- **Responsywny design** - Działa na wszystkich urządzeniach
- **Nowoczesny UI** - Profesjonalny wygląd
- **Toast notifications** - Przyjazne powiadomienia
- **Dark mode** - Wsparcie dla ciemnego motywu

## 🚀 Instalacja

### Wymagania
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+
- Obsługa kamer w przeglądarce (HTTPS)

### Kroki instalacji

1. **Uploaduj wtyczkę**
   ```
   wp-content/plugins/qr-system-professional/
   ```

2. **Aktywuj wtyczkę**
   - Przejdź do WP Admin → Wtyczki
   - Znajdź "QR-System Professional"
   - Kliknij "Aktywuj"

3. **Konfiguracja automatyczna**
   - System automatycznie utworzy tabele bazy danych
   - Doda przykładowe grupy i kody
   - Utworzy stronę skanowania `/skanowanie/`

4. **Skonfiguruj użytkowników**
   - Dodaj lokalizacje w profilach użytkowników
   - Ustaw uprawnienia dla kodów specjalnych

## 📋 Konfiguracja

### Ustawienia użytkowników

Każdy franczyzobiorca musi mieć skonfigurowane lokalizacje w swoim profilu:

1. **Profil użytkownika** → **Adresy punktów**
   - Adres punktu 1
   - Adres punktu 2 (opcjonalny)
   - Adres punktu 3 (opcjonalny)
   - Adres punktu 4 (opcjonalny)
   - Adres punktu 5 (opcjonalny)

### Kody specjalne

Kody typu "special" mogą być skanowane tylko przez uprzywilejowanych użytkowników:
- `tomasz.szulik@sweetgallery.pl`
- `kontakt@sarnetski.pl`

### Strona skanowania

System automatycznie tworzy stronę `/skanowanie/` z shortcode `[qr_scanner]`.

## 🎮 Użytkowanie

### Dla adminów

1. **Panel QR-System** → **Dashboard**
   - Przegląd statystyk
   - Ostatnie skany
   - Szybkie akcje

2. **Kody QR**
   - Dodawanie nowych kodów
   - Edycja istniejących
   - Przypisywanie do grup
   - Ustawianie limitów i dat ważności

3. **Grupy**
   - Tworzenie grup kodów
   - Organizacja według kampanii
   - Kolorowanie dla lepszej identyfikacji

4. **Historia skanów**
   - Monitoring wszystkich skanów
   - Filtry i wyszukiwanie
   - Eksport danych

5. **Statystyki**
   - Analiza wydajności
   - Wykresy aktywności
   - Ranking użytkowników

### Dla franczyzobiorców

1. **Przejdź na stronę** `/skanowanie/`
2. **Wybierz lokalizację** z listy dostępnych punktów
3. **Skanuj kod QR** kamerą lub wpisz ręcznie
4. **Potwierdź skan** i sprawdź wynik

## 🔄 Kompatybilność z legacy

System jest w pełni kompatybilny z poprzednią wersją:

### Legacy endpoints
- `sprawdz_kod_qr` - Skanowanie QR
- `sprawdz_kod_manual` - Ręczne kody
- `aktualizuj_status_kodu_qr` - Aktualizacja statusu

### Legacy post types
- `kody-rabatowe` - Normalne kody
- `kody-rabatowem` - Kody specjalne (M)
- `zeskanowane` - Historia skanów
- `zeskanowanem` - Historia skanów (M)

### Legacy shortcodes
- `[moja_lista_adresow]` - Lista lokalizacji użytkownika

## 🛠 Rozwiązywanie problemów

### Kamery nie działają

1. **Sprawdź HTTPS** - Kamery wymagają bezpiecznego połączenia
2. **Uprawnienia przeglądarki** - Pozwól na dostęp do kamery
3. **Sprawdź konsolę** - F12 → Console → szukaj błędów

### Błędy bazy danych

1. **Odśwież tabele** - Panel → Ustawienia → "Napraw tabele"
2. **Sprawdź uprawnienia** - Użytkownik MySQL musi mieć uprawnienia CREATE/DROP
3. **Zwiększ limity** - `max_execution_time`, `memory_limit`

### Problemy z skanowaniem

1. **Sprawdź lokalizację** - Użytkownik musi wybrać lokalizację
2. **Sprawdź kod** - Kod musi istnieć i być aktywny
3. **Sprawdź uprawnienia** - Kody specjalne wymagają uprawnień

## 📞 Wsparcie

W przypadku problemów:

1. **Sprawdź logi** - Panel → Ustawienia → Informacje systemowe
2. **Wyeksportuj dane** - Przed większymi zmianami
3. **Kontakt** - kontakt@sarnetski.pl

## 📄 Licencja

GPL v2 or later

## 🏗 Architektura

### Struktura plików
```
qr-system-professional/
├── qr-system.php           # Główny plik wtyczki
├── assets/                 # Zasoby CSS/JS
│   ├── admin.css          # Style administratora
│   ├── admin.js           # Skrypty administratora
│   ├── scanner.css        # Style skanera
│   └── scanner.js         # Skrypty skanera
├── templates/             # Szablony HTML
│   ├── admin-dashboard.php
│   ├── admin-codes.php
│   ├── admin-groups.php
│   ├── admin-scans.php
│   ├── admin-stats.php
│   ├── admin-settings.php
│   └── scanner.php
└── README.md              # Ten plik
```

### Tabele bazy danych
- `wp_qr_codes` - Kody QR
- `wp_qr_scans` - Historia skanów
- `wp_qr_groups` - Grupy kodów

### AJAX endpoints
- `qr_scan_code` - Nowe skanowanie
- `qr_add_code` - Dodawanie kodu
- `qr_refresh_tables` - Odświeżanie tabel
- `sprawdz_kod_qr` - Legacy skanowanie

## 🎨 Customizacja

### Kolory i style
Edytuj pliki CSS w folderze `assets/` aby dostosować wygląd.

### Dodatkowe funkcje
Użyj hooków WordPress do rozszerzenia funkcjonalności:
- `qr_system_before_scan`
- `qr_system_after_scan`
- `qr_system_code_added`

### Tłumaczenia
System używa polskich tekstów. Aby dodać inne języki, użyj standardowych narzędzi WordPress i18n.

---

**QR-System Professional** - Nowoczesne rozwiązanie dla zarządzania kodami QR w WordPress.
