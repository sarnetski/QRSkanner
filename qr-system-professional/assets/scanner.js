/**
 * QR Scanner Professional - Frontend JavaScript
 * Kompatybilność z legacy systemem + nowoczesne funkcjonalności
 */

(function($) {
    'use strict';
    
    // Globalne zmienne
    let qrScanner = {
        html5QrCode: null,
        isScanning: false,
        cameras: [],
        currentCamera: null,
        detectedCode: null,
        
        // Legacy kompatybilność
        legacyMode: true,
        
        // Elementy DOM
        elements: {
            cameraSelect: null,
            startBtn: null,
            reader: null,
            manualInput: null,
            submitBtn: null,
            locationSelect: null,
            resultsPanel: null
        },
        
        // Inicjalizacja
        init: function() {
            this.setupElements();
            this.setupEventListeners();
            this.loadCameras();
            this.showToast('System QR gotowy do pracy', 'info');
        },
        
        // Konfiguracja elementów DOM
        setupElements: function() {
            this.elements = {
                cameraSelect: $('#qr-camera-select, #cameraSelect'),
                startBtn: $('#qr-start-scan, #startScanBtn'),
                reader: $('#qr-reader, #reader'),
                manualInput: $('#qr-manual-code, #manualCodeInput'),
                submitBtn: $('#qr-submit-manual, #submitManualCodeBtn'),
                locationSelect: $('#qr-location-select, #lokalizacjaSelect'),
                resultsPanel: $('#qr-results-panel'),
                overlay: $('#qr-scan-overlay'),
                confirmBtn: $('#qr-confirm-scan'),
                cancelBtn: $('#qr-cancel-scan')
            };
        },
        
        // Event listenery
        setupEventListeners: function() {
            const self = this;
            
            // Start skanowania
            this.elements.startBtn.on('click', function() {
                self.toggleScanning();
            });
            
            // Zmiana kamery
            this.elements.cameraSelect.on('change', function() {
                if (self.isScanning) {
                    self.stopScanning();
                    setTimeout(() => self.startScanning(), 100);
                }
            });
            
            // Manual kod
            this.elements.submitBtn.on('click', function() {
                self.submitManualCode();
            });
            
            this.elements.manualInput.on('keypress', function(e) {
                if (e.which === 13) {
                    self.submitManualCode();
                }
            });
            
            // Potwierdzenie skanu
            this.elements.confirmBtn.on('click', function() {
                self.confirmScan();
            });
            
            this.elements.cancelBtn.on('click', function() {
                self.cancelScan();
            });
            
            // Zamknięcie wyników
            $('#qr-results-close, #qr-scan-another').on('click', function() {
                self.hideResults();
                self.resetScanner();
            });
        },
        
        // Ładowanie dostępnych kamer
        loadCameras: function() {
            const self = this;
            
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                this.showToast('Twoja przeglądarka nie obsługuje kamer', 'error');
                return;
            }
            
            // Sprawdź uprawnienia do kamery
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    // Zatrzymaj stream
                    stream.getTracks().forEach(track => track.stop());
                    
                    // Teraz pobierz kamery
                    return navigator.mediaDevices.enumerateDevices();
                })
                .then(function(devices) {
                    self.cameras = devices.filter(device => device.kind === 'videoinput');
                    self.populateCameraSelect();
                    
                    if (self.cameras.length === 0) {
                        self.showToast('Nie znaleziono kamer', 'error');
                    } else {
                        self.showToast(`Znaleziono ${self.cameras.length} kamer`, 'success');
                    }
                })
                .catch(function(error) {
                    console.error('Błąd dostępu do kamery:', error);
                    self.showToast('Brak dostępu do kamery. Sprawdź uprawnienia.', 'error');
                });
        },
        
        // Wypełnienie selecta kamerami
        populateCameraSelect: function() {
            const select = this.elements.cameraSelect;
            select.empty();
            
            if (this.cameras.length === 0) {
                select.append('<option value="">Brak dostępnych kamer</option>');
                return;
            }
            
            select.append('<option value="">Wybierz kamerę</option>');
            
            this.cameras.forEach((camera, index) => {
                const label = camera.label || `Kamera ${index + 1}`;
                const isBack = label.toLowerCase().includes('back') || 
                               label.toLowerCase().includes('rear') ||
                               label.toLowerCase().includes('environment');
                
                select.append(`<option value="${camera.deviceId}" ${isBack ? 'selected' : ''}>${label}</option>`);
            });
            
            // Auto-wybierz tylną kamerę jeśli dostępna
            const backCamera = this.cameras.find(camera => 
                camera.label.toLowerCase().includes('back') ||
                camera.label.toLowerCase().includes('rear') ||
                camera.label.toLowerCase().includes('environment')
            );
            
            if (backCamera) {
                select.val(backCamera.deviceId);
                this.currentCamera = backCamera.deviceId;
            }
        },
        
        // Toggle skanowania
        toggleScanning: function() {
            if (this.isScanning) {
                this.stopScanning();
            } else {
                this.startScanning();
            }
        },
        
        // Start skanowania
        startScanning: function() {
            const self = this;
            const cameraId = this.elements.cameraSelect.val();
            
            if (!cameraId) {
                this.showToast('Wybierz kamerę przed rozpoczęciem skanowania', 'warning');
                return;
            }
            
            if (this.isScanning) {
                this.stopScanning();
            }
            
            // Inicjalizuj Html5Qrcode
            if (!this.html5QrCode) {
                this.html5QrCode = new Html5Qrcode("qr-reader");
            }
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };
            
            this.html5QrCode.start(
                cameraId,
                config,
                (decodedText, decodedResult) => {
                    self.onScanSuccess(decodedText, decodedResult);
                },
                (errorMessage) => {
                    // Ignoruj błędy skanowania - są normalne
                }
            ).then(() => {
                self.isScanning = true;
                self.elements.startBtn.html('<span class="dashicons dashicons-no"></span><span class="qr-btn-text">Zatrzymaj skanowanie</span>');
                self.elements.startBtn.removeClass('qr-btn-primary').addClass('qr-btn-danger');
                self.showToast('Skanowanie rozpoczęte', 'success');
            }).catch((err) => {
                console.error('Błąd rozpoczęcia skanowania:', err);
                self.showToast('Błąd uruchomienia kamery: ' + err, 'error');
            });
        },
        
        // Stop skanowania
        stopScanning: function() {
            const self = this;
            
            if (this.html5QrCode && this.isScanning) {
                this.html5QrCode.stop().then(() => {
                    self.isScanning = false;
                    self.elements.startBtn.html('<span class="dashicons dashicons-video-alt3"></span><span class="qr-btn-text">Rozpocznij skanowanie</span>');
                    self.elements.startBtn.removeClass('qr-btn-danger').addClass('qr-btn-primary');
                    self.showToast('Skanowanie zatrzymane', 'info');
                }).catch((err) => {
                    console.error('Błąd zatrzymania skanowania:', err);
                });
            }
        },
        
        // Obsługa pomyślnego skanu
        onScanSuccess: function(decodedText, decodedResult) {
            console.log('Wykryto kod QR:', decodedText);
            
            this.detectedCode = decodedText;
            this.showScanOverlay(decodedText);
            
            // Zatrzymaj skanowanie
            this.stopScanning();
        },
        
        // Pokaż overlay z wykrytym kodem
        showScanOverlay: function(code) {
            $('#qr-detected-code').text(code);
            this.elements.overlay.removeClass('hidden').addClass('show');
        },
        
        // Potwierdź skan
        confirmScan: function() {
            if (this.detectedCode) {
                this.processScan(this.detectedCode);
            }
            this.hideScanOverlay();
        },
        
        // Anuluj skan
        cancelScan: function() {
            this.detectedCode = null;
            this.hideScanOverlay();
            this.showToast('Skan anulowany', 'info');
        },
        
        // Ukryj overlay
        hideScanOverlay: function() {
            this.elements.overlay.removeClass('show').addClass('hidden');
        },
        
        // Przetwarzanie skanu (główna logika)
        processScan: function(code) {
            const self = this;
            const location = this.elements.locationSelect.val();
            
            if (!location) {
                this.showToast('Wybierz lokalizację przed skanowaniem', 'warning');
                return;
            }
            
            this.showToast('Sprawdzam kod...', 'info');
            
            // Użyj legacy AJAX endpoint dla kompatybilności
            $.ajax({
                url: qr_scanner.ajax_url || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'sprawdz_kod_qr',
                    kodQR: code,
                    lokalizacja: location,
                    nonce: qr_scanner.nonce
                },
                success: function(response) {
                    self.handleScanResponse(response, code, location);
                },
                error: function(xhr, status, error) {
                    console.error('Błąd AJAX:', error);
                    self.showToast('Błąd połączenia z serwerem', 'error');
                    self.showResults('error', 'Błąd połączenia', code, location);
                }
            });
        },
        
        // Obsługa odpowiedzi serwera
        handleScanResponse: function(response, code, location) {
            console.log('Odpowiedź serwera:', response);
            
            let resultType, message;
            
            // Sprawdź czy to customowa wiadomość
            if (response.startsWith('success:')) {
                resultType = 'success';
                message = response.substring(8); // Usuń prefix 'success:'
            } else if (response === 'true') {
                resultType = 'success';
                message = 'Powodzenie';
            } else if (response === 'expired') {
                resultType = 'warning';
                message = 'Przedawniony';
            } else if (response === 'special') {
                resultType = 'error';
                message = 'Brak uprawnień do tego kodu specjalnego';
            } else {
                resultType = 'error';
                message = 'Niepowodzenie';
            }
            
            this.showToast(message, resultType);
            this.showResults(resultType, message, code, location);
        },
        
        // Manual kod
        submitManualCode: function() {
            const code = this.elements.manualInput.val().trim();
            
            if (!code) {
                this.showToast('Wpisz kod QR', 'warning');
                this.elements.manualInput.focus();
                return;
            }
            
            this.processScanManual(code);
        },
        
        // Przetwarzanie manual kodu
        processScanManual: function(code) {
            const self = this;
            const location = this.elements.locationSelect.val();
            
            if (!location) {
                this.showToast('Wybierz lokalizację', 'warning');
                return;
            }
            
            this.showToast('Sprawdzam kod...', 'info');
            
            $.ajax({
                url: qr_scanner.ajax_url || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'sprawdz_kod_manual',
                    kodManual: code,
                    lokalizacja: location,
                    nonce: qr_scanner.nonce
                },
                success: function(response) {
                    self.handleScanResponse(response, code, location);
                    self.elements.manualInput.val(''); // Wyczyść pole
                },
                error: function(xhr, status, error) {
                    console.error('Błąd AJAX manual:', error);
                    self.showToast('Błąd połączenia z serwerem', 'error');
                    self.showResults('error', 'Błąd połączenia', code, location);
                }
            });
        },
        
        // Pokaż wyniki
        showResults: function(type, message, code, location) {
            $('#qr-results-title').text(\n                type === 'success' ? 'Sukces!' : \n                type === 'warning' ? 'Ostrzeżenie' : 'Błąd'\n            );\n            \n            $('#qr-results-message').html(message);\n            $('#qr-result-code').text(code);\n            $('#qr-result-location').text(location || 'Brak');\n            $('#qr-result-time').text(new Date().toLocaleString('pl-PL'));\n            \n            this.elements.resultsPanel.removeClass('hidden').addClass('show');\n            \n            // Auto-scroll do wyników\n            setTimeout(() => {\n                this.elements.resultsPanel[0].scrollIntoView({ \n                    behavior: 'smooth', \n                    block: 'center' \n                });\n            }, 100);\n        },\n        \n        // Ukryj wyniki\n        hideResults: function() {\n            this.elements.resultsPanel.removeClass('show').addClass('hidden');\n        },\n        \n        // Reset skanera\n        resetScanner: function() {\n            this.detectedCode = null;\n            this.elements.manualInput.val('');\n            this.hideScanOverlay();\n        },\n        \n        // Toast notifications\n        showToast: function(message, type = 'info') {\n            // Usuń istniejące toasty\n            $('.qr-toast').remove();\n            \n            const toastClass = `qr-toast qr-toast-${type}`;\n            const iconClass = {\n                success: 'dashicons-yes-alt',\n                error: 'dashicons-warning',\n                warning: 'dashicons-info',\n                info: 'dashicons-info'\n            }[type] || 'dashicons-info';\n            \n            const toast = $(`\n                <div class=\"${toastClass}\">\n                    <div class=\"qr-toast-content\">\n                        <span class=\"dashicons ${iconClass}\"></span>\n                        <span class=\"qr-toast-message\">${message}</span>\n                    </div>\n                </div>\n            `);\n            \n            $('body').append(toast);\n            \n            // Animacja wejścia\n            setTimeout(() => toast.addClass('qr-toast-show'), 100);\n            \n            // Auto-usunięcie\n            setTimeout(() => {\n                toast.removeClass('qr-toast-show');\n                setTimeout(() => toast.remove(), 300);\n            }, 4000);\n        }\n    };\n    \n    // Inicjalizacja po załadowaniu DOM\n    $(document).ready(function() {\n        // Sprawdź czy Html5Qrcode jest dostępne\n        if (typeof Html5Qrcode === 'undefined') {\n            console.error('Html5Qrcode nie jest załadowane!');\n            return;\n        }\n        \n        qrScanner.init();\n    });\n    \n    // Eksportuj do globalnego scope dla kompatybilności\n    window.qrScanner = qrScanner;\n    \n    // Legacy funkcje dla kompatybilności\n    window.sprawdzKodQR = function(kod) {\n        qrScanner.processScan(kod);\n    };\n    \n    window.obsluzZeskanowanyKodQR = function(kod) {\n        qrScanner.onScanSuccess(kod, null);\n    };\n    \n    window.rozpocznijSkanowanie = function(cameraId) {\n        if (cameraId) {\n            qrScanner.elements.cameraSelect.val(cameraId);\n        }\n        qrScanner.startScanning();\n    };\n    \n    window.zatrzymajSkanowanie = function() {\n        qrScanner.stopScanning();\n    };\n\n})(jQuery);\n\n// Legacy kod dla pełnej kompatybilności z poprzednim systemem\nif (typeof Html5Qrcode !== 'undefined') {\n    // Funkcje legacy\n    function checkCameraAccess() {\n        if (window.qrScanner) {\n            window.qrScanner.loadCameras();\n        }\n    }\n\n    function wyswietlKomunikatPowodzenia(kod) {\n        if (window.qrScanner) {\n            window.qrScanner.showToast('Kod zeskanowany pomyślnie: ' + kod, 'success');\n        }\n    }\n\n    function wyswietlKomunikatPrzedawniony(kod) {\n        if (window.qrScanner) {\n            window.qrScanner.showToast('Kod przedawniony: ' + kod, 'warning');\n        }\n    }\n\n    function wyswietlKomunikatNiepowodzenia(kod) {\n        if (window.qrScanner) {\n            window.qrScanner.showToast('Niepowodzenie: ' + kod, 'error');\n        }\n    }\n}\n