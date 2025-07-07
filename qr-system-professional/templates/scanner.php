<?php
/**
 * QR Scanner Frontend Template - Professional
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
?>

<div class="qr-scanner-app">
    <!-- Header aplikacji -->
    <div class="qr-scanner-header">
        <div class="qr-logo">
            <span class="qr-logo-icon">📱</span>
            <h1>QR Skaner</h1>
        </div>
        <div class="qr-user-info">
            <div class="qr-user-avatar">
                <?php echo get_avatar($current_user->ID, 40); ?>
            </div>
            <div class="qr-user-details">
                <div class="qr-user-name"><?php echo esc_html($current_user->display_name); ?></div>
                <div class="qr-user-role">Franczyzobiorca</div>
            </div>
        </div>
    </div>

    <!-- Główny interfejs skanowania -->
    <div class="qr-scanner-main">
        <div class="qr-scanner-container">
            
            <!-- Krok 1: Wybór lokalizacji -->
            <div class="qr-step qr-step-location">
                <div class="qr-step-header">
                    <div class="qr-step-number">1</div>
                    <div class="qr-step-content">
                        <h2>Wybierz lokalizację</h2>
                        <p>Wskaż punkt w którym skanowany jest kod</p>
                    </div>
                </div>
                
                <div class="qr-location-section">
                    <div class="qr-location-group">
                        <label for="qr-location-select">
                            <span class="dashicons dashicons-location"></span>
                            Twoja lokalizacja:
                        </label>
                        <?php echo do_shortcode('[qr_user_locations]'); ?>
                        
                        <!-- Legacy compatibility - fallback jeśli shortcode nie działa -->
                        <?php if (empty(do_shortcode('[qr_user_locations]'))): ?>
                            <select id="lokalizacjaSelect" class="qr-location-select">
                                <option value="">Wybierz lokalizację</option>
                                <?php
                                $user_id = get_current_user_id();
                                $location_keys = array('adres-punktu', 'adres-punktu2', 'adres-punktu3', 'adres-punktu4', 'adres-punktu5');
                                foreach ($location_keys as $key) {
                                    $location = get_user_meta($user_id, $key, true);
                                    if (!empty($location)) {
                                        echo '<option value="' . esc_attr($location) . '">' . esc_html($location) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Separator -->
            <div class="qr-step-separator">
                <div class="qr-separator-line"></div>
                <div class="qr-separator-text">NASTĘPNIE</div>
                <div class="qr-separator-line"></div>
            </div>
            
            <!-- Krok 2: Wybór kamery i skanowanie -->
            <div class="qr-step qr-step-camera">
                <div class="qr-step-header">
                    <div class="qr-step-number">2</div>
                    <div class="qr-step-content">
                        <h2>Skanowanie kamerą</h2>
                        <p>Uruchom kamerę i skieruj na kod QR</p>
                    </div>
                </div>
                
                <div class="qr-camera-section">
                    <div class="qr-camera-controls">
                        <div class="qr-control-group">
                            <label for="qr-camera-select">
                                <span class="dashicons dashicons-camera"></span>
                                Wybierz kamerę:
                            </label>
                            <select id="qr-camera-select" class="qr-select cameraSelect">
                                <option value="">Ładowanie kamer...</option>
                            </select>
                            <!-- Legacy compatibility -->
                            <select id="cameraSelect" style="display: none;"></select>
                        </div>
                        
                        <div class="qr-camera-actions">
                            <button id="qr-start-scan" class="qr-btn qr-btn-primary" type="button">
                                <span class="dashicons dashicons-video-alt3"></span>
                                <span class="qr-btn-text">Rozpocznij skanowanie</span>
                            </button>
                            <button id="qr-toggle-flashlight" class="qr-btn qr-btn-secondary" type="button" style="display: none;">
                                <span class="dashicons dashicons-lightbulb"></span>
                                <span class="qr-btn-text">Latarka</span>
                            </button>
                        </div>
                    </div>
                    
                    <div id="qr-reader" class="qr-reader-container">
                        <div class="qr-reader-placeholder">
                            <div class="qr-placeholder-icon">
                                <span class="dashicons dashicons-camera"></span>
                            </div>
                            <div class="qr-placeholder-text">
                                <h3>Gotowy do skanowania</h3>
                                <p>Kliknij "Rozpocznij skanowanie" aby uruchomić kamerę</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Overlay dla wykrytego kodu -->
                    <div id="qr-scan-overlay" class="qr-scan-overlay hidden">
                        <div class="qr-scan-success">
                            <div class="qr-success-icon">
                                <span class="dashicons dashicons-yes-alt"></span>
                            </div>
                            <div class="qr-success-content">
                                <h3>Kod wykryty!</h3>
                                <div class="qr-detected-code" id="qr-detected-code"></div>
                                <div class="qr-scan-actions">
                                    <button id="qr-confirm-scan" class="qr-btn qr-btn-success">
                                        <span class="dashicons dashicons-yes"></span>
                                        Potwierdź skan
                                    </button>
                                    <button id="qr-cancel-scan" class="qr-btn qr-btn-ghost">
                                        <span class="dashicons dashicons-no"></span>
                                        Anuluj
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Separator -->
            <div class="qr-step-separator">
                <div class="qr-separator-line"></div>
                <div class="qr-separator-text">LUB</div>
                <div class="qr-separator-line"></div>
            </div>
            
            <!-- Krok 3: Ręczne wpisanie kodu -->
            <div class="qr-step qr-step-manual">
                <div class="qr-step-header">
                    <div class="qr-step-number">3</div>
                    <div class="qr-step-content">
                        <h2>Wpisz kod ręcznie</h2>
                        <p>Jeśli skanowanie nie działa, wpisz kod ręcznie</p>
                    </div>
                </div>
                
                <div class="qr-manual-section">
                    <div class="qr-manual-input-group">
                        <label for="qr-manual-code">
                            <span class="dashicons dashicons-edit"></span>
                            Kod QR:
                        </label>
                        <div class="qr-input-container">
                            <input type="text" 
                                   id="qr-manual-code" 
                                   class="qr-input" 
                                   placeholder="Wpisz kod QR (np. ABC123)"
                                   autocomplete="off"
                                   spellcheck="false">
                            <button id="qr-submit-manual" class="qr-btn qr-btn-primary">
                                <span class="dashicons dashicons-search"></span>
                                Sprawdź
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        
        <!-- Panel wyników -->
        <div id="qr-results-panel" class="qr-results-panel hidden">
            <div class="qr-results-content">
                <div class="qr-results-header">
                    <h3 id="qr-results-title">Wynik skanowania</h3>
                    <button id="qr-results-close" class="qr-btn qr-btn-ghost qr-btn-small">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>
                <div class="qr-results-body">
                    <div id="qr-results-message" class="qr-results-message"></div>
                    <div id="qr-results-details" class="qr-results-details">
                        <div class="qr-result-item">
                            <span class="qr-result-label">Kod:</span>
                            <span id="qr-result-code" class="qr-result-value"></span>
                        </div>
                        <div class="qr-result-item">
                            <span class="qr-result-label">Lokalizacja:</span>
                            <span id="qr-result-location" class="qr-result-value"></span>
                        </div>
                        <div class="qr-result-item">
                            <span class="qr-result-label">Czas:</span>
                            <span id="qr-result-time" class="qr-result-value"></span>
                        </div>
                    </div>
                </div>
                <div class="qr-results-actions">
                    <button id="qr-scan-another" class="qr-btn qr-btn-primary">
                        <span class="dashicons dashicons-camera"></span>
                        Skanuj następny kod
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status bar -->
    <div class="qr-status-bar">
        <div class="qr-status-item">
            <span class="qr-status-icon qr-status-online">●</span>
            <span class="qr-status-text">Online</span>
        </div>
        <div class="qr-status-item">
            <span class="qr-status-text">Ostatnia aktywność: <span id="qr-last-activity">--:--</span></span>
        </div>
        <div class="qr-status-item">
            <a href="<?php echo wp_logout_url(home_url()); ?>" class="qr-logout-link">
                <span class="dashicons dashicons-exit"></span>
                Wyloguj
            </a>
        </div>
    </div>
</div>

<!-- Toast notifications -->
<div id="qr-toast-container" class="qr-toast-container"></div>

<!-- Legacy Modal dla kompatybilności -->
<div id="modal" class="qr-legacy-modal" style="display: none;">
    <div class="qr-legacy-modal-content">
        <div class="qr-legacy-modal-header">
            <h3>Wynik skanowania</h3>
            <button type="button" class="qr-legacy-modal-close" onclick="closeLegacyModal()">&times;</button>
        </div>
        <div class="qr-legacy-modal-body">
            <p>Zeskanowany kod QR: <span id="qrCodeMessage"></span></p>
            <p id="statusMessage"></p>
        </div>
        <div class="qr-legacy-modal-footer">
            <button type="button" id="close-btn" class="qr-btn qr-btn-primary" onclick="closeLegacyModal()">Zamknij</button>
        </div>
    </div>
</div>

<!-- Legacy compatibility IDs (ukryte) -->
<div style="display: none;">
    <div id="reader-backup"></div>
    <input type="text" id="manualCodeInput">
    <button id="submitManualCodeBtn"></button>
    <button id="startScanBtn"></button>
    <select id="cameraSelect"></select>
    <select id="lokalizacjaSelect"></select>
</div>

<script>
// Legacy modal functions
function closeLegacyModal() {
    document.getElementById('modal').style.display = 'none';
}

// Legacy compatibility functions
function wyswietlKomunikatPowodzenia(kod) {
    document.getElementById('qrCodeMessage').textContent = kod;
    document.getElementById('statusMessage').textContent = 'Powodzenie';
    document.getElementById('statusMessage').style.color = 'green';
    document.getElementById('modal').style.display = 'flex';
}

function wyswietlKomunikatPrzedawniony(kod) {
    document.getElementById('qrCodeMessage').textContent = kod;
    document.getElementById('statusMessage').textContent = 'Kod przedawniony';
    document.getElementById('statusMessage').style.color = 'red';
    document.getElementById('modal').style.display = 'flex';
}

function wyswietlKomunikatNiepowodzenia(kod) {
    document.getElementById('qrCodeMessage').textContent = kod;
    document.getElementById('statusMessage').textContent = 'Niepowodzenie';
    document.getElementById('statusMessage').style.color = 'red';
    document.getElementById('modal').style.display = 'flex';
}

// Legacy QR scanning functions
function sprawdzKodQR(kodQR) {
    var lokalizacja = document.getElementById('lokalizacjaSelect') ? document.getElementById('lokalizacjaSelect').value : '';
    
    if (typeof jQuery !== 'undefined') {
        jQuery.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'sprawdz_kod_qr',
                kodQR: kodQR,
                lokalizacja: lokalizacja
            },
            success: function(response) {
                if (response === 'true') {
                    wyswietlKomunikatPowodzenia(kodQR);
                } else if (response === 'expired') {
                    wyswietlKomunikatPrzedawniony(kodQR);
                } else {
                    wyswietlKomunikatNiepowodzenia(kodQR);
                }
            },
            error: function(xhr, status, error) {
                wyswietlKomunikatNiepowodzenia(kodQR);
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Sync legacy and new elements
    const legacyElements = {
        cameraSelect: document.getElementById('cameraSelect'),
        startBtn: document.getElementById('startScanBtn'),
        manualInput: document.getElementById('manualCodeInput'),
        submitBtn: document.getElementById('submitManualCodeBtn'),
        locationSelect: document.getElementById('lokalizacjaSelect')
    };
    
    const newElements = {
        cameraSelect: document.getElementById('qr-camera-select'),
        startBtn: document.getElementById('qr-start-scan'),
        manualInput: document.getElementById('qr-manual-code'),
        submitBtn: document.getElementById('qr-submit-manual'),
        locationSelect: document.getElementById('qr-location-select')
    };
    
    // Sync camera select
    if (legacyElements.cameraSelect && newElements.cameraSelect) {
        newElements.cameraSelect.addEventListener('change', function() {
            legacyElements.cameraSelect.innerHTML = newElements.cameraSelect.innerHTML;
            legacyElements.cameraSelect.value = newElements.cameraSelect.value;
        });
    }
    
    // Sync start button
    if (legacyElements.startBtn && newElements.startBtn) {
        legacyElements.startBtn.addEventListener('click', function() {
            newElements.startBtn.click();
        });
    }
    
    // Sync manual input
    if (legacyElements.manualInput && newElements.manualInput) {
        legacyElements.manualInput.addEventListener('input', function() {
            newElements.manualInput.value = this.value;
        });
        
        newElements.manualInput.addEventListener('input', function() {
            legacyElements.manualInput.value = this.value;
        });
    }
    
    // Sync submit button
    if (legacyElements.submitBtn && newElements.submitBtn) {
        legacyElements.submitBtn.addEventListener('click', function() {
            newElements.submitBtn.click();
        });
    }
    
    // Sync location select
    if (legacyElements.locationSelect && newElements.locationSelect) {
        legacyElements.locationSelect.addEventListener('change', function() {
            newElements.locationSelect.value = this.value;
        });
        
        newElements.locationSelect.addEventListener('change', function() {
            legacyElements.locationSelect.value = this.value;
        });
    }

document.addEventListener('DOMContentLoaded', function() {
    const QRScanner = {
        // Stan aplikacji
        isScanning: false,
        html5QrCode: null,
        lastScannedCode: null,
        
        // Elementy DOM
        elements: {
            cameraSelect: document.getElementById('qr-camera-select'),
            startScanBtn: document.getElementById('qr-start-scan'),
            flashlightBtn: document.getElementById('qr-toggle-flashlight'),
            readerDiv: document.getElementById('qr-reader'),
            scanOverlay: document.getElementById('qr-scan-overlay'),
            confirmBtn: document.getElementById('qr-confirm-scan'),
            cancelBtn: document.getElementById('qr-cancel-scan'),
            manualInput: document.getElementById('qr-manual-code'),
            submitManualBtn: document.getElementById('qr-submit-manual'),
            locationSelect: document.getElementById('qr-location-select'),
            resultsPanel: document.getElementById('qr-results-panel'),
            resultsClose: document.getElementById('qr-results-close'),
            scanAnotherBtn: document.getElementById('qr-scan-another')
        },
        
        // Inicjalizacja
        init: function() {
            this.loadCameras();
            this.bindEvents();
            this.updateLastActivity();
            setInterval(() => this.updateLastActivity(), 60000); // Co minutę
        },
        
        // Załaduj dostępne kamery
        loadCameras: function() {
            Html5Qrcode.getCameras().then(cameras => {
                const select = this.elements.cameraSelect;
                select.innerHTML = '';
                
                if (cameras && cameras.length) {
                    cameras.forEach(camera => {
                        const option = document.createElement('option');
                        option.value = camera.id;
                        option.textContent = camera.label || `Kamera ${camera.id}`;
                        select.appendChild(option);
                    });
                    
                    // Automatycznie wybierz tylną kamerę
                    const backCamera = cameras.find(camera => 
                        camera.label.toLowerCase().includes('back') || 
                        camera.label.toLowerCase().includes('rear') ||
                        camera.label.toLowerCase().includes('tył')
                    );
                    
                    if (backCamera) {
                        select.value = backCamera.id;
                    }
                    
                    this.showToast('Kamery załadowane pomyślnie', 'success');
                } else {
                    select.innerHTML = '<option value="">Brak dostępnych kamer</option>';
                    this.showToast('Nie znaleziono kamer', 'error');
                }
            }).catch(err => {
                console.error('Błąd podczas ładowania kamer:', err);
                this.elements.cameraSelect.innerHTML = '<option value="">Błąd ładowania kamer</option>';
                this.showToast('Błąd dostępu do kamer', 'error');
            });
        },
        
        // Przypisz zdarzenia
        bindEvents: function() {
            this.elements.startScanBtn.addEventListener('click', () => this.toggleScanning());
            this.elements.confirmBtn.addEventListener('click', () => this.confirmScan());
            this.elements.cancelBtn.addEventListener('click', () => this.cancelScan());
            this.elements.submitManualBtn.addEventListener('click', () => this.submitManualCode());
            this.elements.manualInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.submitManualCode();
            });
            this.elements.resultsClose.addEventListener('click', () => this.hideResults());
            this.elements.scanAnotherBtn.addEventListener('click', () => this.scanAnother());
            
            // Auto-uppercase dla ręcznego kodu
            this.elements.manualInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.toUpperCase();
            });
        },
        
        // Przełącz skanowanie
        toggleScanning: function() {
            if (this.isScanning) {
                this.stopScanning();
            } else {
                this.startScanning();
            }
        },
        
        // Rozpocznij skanowanie
        startScanning: function() {
            const cameraId = this.elements.cameraSelect.value;
            
            if (!cameraId) {
                this.showToast('Wybierz kamerę', 'error');
                return;
            }
            
            this.html5QrCode = new Html5Qrcode("qr-reader");
            
            const config = {
                fps: 10,
                qrbox: { 
                    width: Math.min(300, window.innerWidth - 40), 
                    height: Math.min(300, window.innerWidth - 40) 
                },
                aspectRatio: 1.0
            };
            
            this.html5QrCode.start(
                cameraId,
                config,
                (qrCodeMessage) => {
                    this.onScanSuccess(qrCodeMessage);
                },
                (errorMessage) => {
                    // Ignoruj błędy skanowania (normalne podczas próby odczytu)
                }
            ).then(() => {
                this.isScanning = true;
                this.updateScanButton();
                this.showToast('Skanowanie rozpoczęte', 'info');
            }).catch(err => {
                console.error('Błąd podczas uruchamiania skanowania:', err);
                this.showToast('Błąd uruchamiania kamery: ' + err, 'error');
            });
        },
        
        // Zatrzymaj skanowanie
        stopScanning: function() {
            if (this.html5QrCode && this.isScanning) {
                this.html5QrCode.stop().then(() => {
                    this.isScanning = false;
                    this.updateScanButton();
                    this.hideScanOverlay();
                    this.showToast('Skanowanie zatrzymane', 'info');
                }).catch(err => {
                    console.error('Błąd podczas zatrzymywania skanowania:', err);
                });
            }
        },
        
        // Sukces skanowania
        onScanSuccess: function(qrCodeMessage) {
            this.lastScannedCode = qrCodeMessage;
            this.showScanOverlay(qrCodeMessage);
            this.stopScanning();
        },
        
        // Pokaż overlay z wykrytym kodem
        showScanOverlay: function(code) {
            document.getElementById('qr-detected-code').textContent = code;
            this.elements.scanOverlay.classList.remove('hidden');
        },
        
        // Ukryj overlay
        hideScanOverlay: function() {
            this.elements.scanOverlay.classList.add('hidden');
        },
        
        // Potwierdź skan
        confirmScan: function() {
            if (this.lastScannedCode) {
                this.processCode(this.lastScannedCode);
            }
            this.hideScanOverlay();
        },
        
        // Anuluj skan
        cancelScan: function() {
            this.hideScanOverlay();
            this.lastScannedCode = null;
            // Możliwość kontynuowania skanowania
        },
        
        // Wyślij kod ręczny
        submitManualCode: function() {
            const code = this.elements.manualInput.value.trim();
            
            if (!code) {
                this.showToast('Wpisz kod QR', 'error');
                this.elements.manualInput.focus();
                return;
            }
            
            this.processCode(code, true);
        },
        
        // Przetwórz kod
        processCode: function(code, isManual = false) {
            const location = this.elements.locationSelect ? this.elements.locationSelect.value : '';
            
            if (!location) {
                this.showToast('Wybierz lokalizację', 'error');
                this.elements.locationSelect.focus();
                return;
            }
            
            this.showToast('Sprawdzam kod...', 'info');
            
            // Wyślij AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', qr_scanner.ajax_url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = () => {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            this.handleScanResponse(response, code, isManual);
                        } catch (e) {
                            console.error('Błąd parsowania odpowiedzi:', e);
                            this.showToast('Błąd komunikacji z serwerem', 'error');
                        }
                    } else {
                        this.showToast('Błąd połączenia z serwerem', 'error');
                    }
                }
            };
            
            const data = `action=qr_scan_code&nonce=${encodeURIComponent(qr_scanner.nonce)}&code=${encodeURIComponent(code)}&location=${encodeURIComponent(location)}`;
            xhr.send(data);
        },
        
        // Obsłuż odpowiedź serwera
        handleScanResponse: function(response, code, isManual) {
            if (response.success) {
                this.showResults('success', 'Sukces!', response.data.message, code);
                
                // Wyczyść formularz ręczny
                if (isManual) {
                    this.elements.manualInput.value = '';
                }
                
                // Wibracja i dźwięk sukcesu
                this.vibrate();
                this.playSound('success');
                
            } else {
                const errorMsg = response.data || 'Nieznany błąd';
                this.showResults('error', 'Błąd', errorMsg, code);
                this.playSound('error');
            }
            
            this.updateLastActivity();
        },
        
        // Pokaż wyniki
        showResults: function(type, title, message, code) {
            document.getElementById('qr-results-title').textContent = title;
            document.getElementById('qr-results-message').textContent = message;
            document.getElementById('qr-result-code').textContent = code;
            document.getElementById('qr-result-location').textContent = this.elements.locationSelect.value;
            document.getElementById('qr-result-time').textContent = new Date().toLocaleString('pl-PL');
            
            const panel = this.elements.resultsPanel;
            panel.className = `qr-results-panel qr-results-${type}`;
            panel.classList.remove('hidden');
        },
        
        // Ukryj wyniki
        hideResults: function() {
            this.elements.resultsPanel.classList.add('hidden');
        },
        
        // Skanuj kolejny kod
        scanAnother: function() {
            this.hideResults();
            this.hideScanOverlay();
            this.lastScannedCode = null;
            this.elements.manualInput.value = '';
        },
        
        // Aktualizuj przycisk skanowania
        updateScanButton: function() {
            const btn = this.elements.startScanBtn;
            const text = btn.querySelector('.qr-btn-text');
            const icon = btn.querySelector('.dashicons');
            
            if (this.isScanning) {
                text.textContent = 'Zatrzymaj skanowanie';
                icon.className = 'dashicons dashicons-no';
                btn.classList.remove('qr-btn-primary');
                btn.classList.add('qr-btn-danger');
            } else {
                text.textContent = 'Rozpocznij skanowanie';
                icon.className = 'dashicons dashicons-video-alt3';
                btn.classList.remove('qr-btn-danger');
                btn.classList.add('qr-btn-primary');
            }
        },
        
        // Aktualizuj ostatnią aktywność
        updateLastActivity: function() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('pl-PL', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            document.getElementById('qr-last-activity').textContent = timeString;
        },
        
        // Wibracja (mobile)
        vibrate: function() {
            if (navigator.vibrate) {
                navigator.vibrate([200, 100, 200]);
            }
        },
        
        // Dźwięk
        playSound: function(type) {
            if (window.AudioContext || window.webkitAudioContext) {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = type === 'success' ? 800 : 400;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            }
        },
        
        // Toast notifications
        showToast: function(message, type = 'info') {
            const container = document.getElementById('qr-toast-container');
            const toast = document.createElement('div');
            toast.className = `qr-toast qr-toast-${type}`;
            
            const icon = {
                success: 'yes-alt',
                error: 'warning',
                info: 'info',
                warning: 'flag'
            }[type] || 'info';
            
            toast.innerHTML = `
                <div class="qr-toast-content">
                    <span class="dashicons dashicons-${icon}"></span>
                    <span class="qr-toast-message">${message}</span>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Auto remove
            setTimeout(() => {
                toast.classList.add('qr-toast-removing');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
    };
    
    // Inicjalizuj aplikację
    QRScanner.init();
    
    // Eksportuj do globalnego zasięgu dla debugowania
    window.QRScanner = QRScanner;
});
</script>