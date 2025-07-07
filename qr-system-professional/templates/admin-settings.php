<?php
/**
 * Admin Settings Template - Professional
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap qr-admin-wrap">
    <div class="qr-header">
        <div class="qr-header-left">
            <h1><span class="dashicons dashicons-admin-settings"></span> Ustawienia QR-System</h1>
            <p class="qr-subtitle">Konfiguruj system według swoich potrzeb</p>
        </div>
    </div>

    <form method="post" class="qr-settings-form">
        <?php wp_nonce_field('qr_save_settings', 'qr_settings_nonce'); ?>
        
        <div class="qr-settings-container">
            <!-- Ustawienia skanowania -->
            <div class="qr-settings-section">
                <div class="qr-settings-header">
                    <h3><span class="dashicons dashicons-camera"></span> Ustawienia skanowania</h3>
                    <p>Konfiguruj zachowanie systemu podczas skanowania kodów QR</p>
                </div>
                
                <div class="qr-settings-content">
                    <div class="qr-setting-item">
                        <div class="qr-setting-control">
                            <label class="qr-toggle">
                                <input type="checkbox" 
                                       name="require_confirmation" 
                                       value="1" 
                                       <?php checked(isset($settings['require_confirmation']) ? $settings['require_confirmation'] : true); ?>>
                                <span class="qr-toggle-slider"></span>
                            </label>
                        </div>
                        <div class="qr-setting-info">
                            <h4>Wymagaj potwierdzenia skanów</h4>
                            <p>Użytkownicy muszą potwierdzić skan przed jego zapisaniem w systemie</p>
                        </div>
                    </div>

                    <div class="qr-setting-item">
                        <div class="qr-setting-control">
                            <label class="qr-toggle">
                                <input type="checkbox" 
                                       name="allow_multiple_scans" 
                                       value="1" 
                                       <?php checked(isset($settings['allow_multiple_scans']) ? $settings['allow_multiple_scans'] : false); ?>>
                                <span class="qr-toggle-slider"></span>
                            </label>
                        </div>
                        <div class="qr-setting-info">
                            <h4>Pozwól na wielokrotne skanowanie</h4>
                            <p>Ten sam kod może być skanowany przez tego samego użytkownika wielokrotnie</p>
                        </div>
                    </div>

                    <div class="qr-setting-item">
                        <div class="qr-setting-control">
                            <label class="qr-toggle">
                                <input type="checkbox" 
                                       name="enable_logging" 
                                       value="1" 
                                       <?php checked(isset($settings['enable_logging']) ? $settings['enable_logging'] : true); ?>>
                                <span class="qr-toggle-slider"></span>
                            </label>
                        </div>
                        <div class="qr-setting-info">
                            <h4>Włącz szczegółowe logowanie</h4>
                            <p>Zapisuj wszystkie próby skanowania dla celów debugowania (przez 7 dni)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ustawienia archiwizacji -->
            <div class="qr-settings-section">
                <div class="qr-settings-header">
                    <h3><span class="dashicons dashicons-archive"></span> Archiwizacja danych</h3>
                    <p>Zarządzaj przechowywaniem i archiwizacją starych danych</p>
                </div>
                
                <div class="qr-settings-content">
                    <div class="qr-setting-item">
                        <div class="qr-setting-control">
                            <input type="number" 
                                   name="auto_archive_days" 
                                   value="<?php echo isset($settings['auto_archive_days']) ? intval($settings['auto_archive_days']) : 30; ?>"
                                   min="7" 
                                   max="365" 
                                   class="qr-number-input">
                            <span class="qr-input-suffix">dni</span>
                        </div>
                        <div class="qr-setting-info">
                            <h4>Automatyczna archiwizacja</h4>
                            <p>Automatycznie archiwizuj skany starsze niż określona liczba dni</p>
                        </div>
                    </div>

                    <div class="qr-setting-item">
                        <div class="qr-setting-control">
                            <input type="number" 
                                   name="max_codes_per_group" 
                                   value="<?php echo isset($settings['max_codes_per_group']) ? intval($settings['max_codes_per_group']) : 1000; ?>"
                                   min="100" 
                                   max="10000" 
                                   class="qr-number-input">
                            <span class="qr-input-suffix">kodów</span>
                        </div>
                        <div class="qr-setting-info">
                            <h4>Maksymalna liczba kodów na grupę</h4>
                            <p>Ogranicz liczbę kodów QR w pojedynczej grupie</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ustawienia powiadomień -->
            <div class="qr-settings-section">
                <div class="qr-settings-header">
                    <h3><span class="dashicons dashicons-email"></span> Powiadomienia</h3>
                    <p>Konfiguruj powiadomienia e-mail i alerty systemowe</p>
                </div>
                
                <div class="qr-settings-content">
                    <div class="qr-setting-item">
                        <div class="qr-setting-control">
                            <label class="qr-toggle">
                                <input type="checkbox" 
                                       name="enable_notifications" 
                                       value="1" 
                                       <?php checked(isset($settings['enable_notifications']) ? $settings['enable_notifications'] : true); ?>>
                                <span class="qr-toggle-slider"></span>
                            </label>
                        </div>
                        <div class="qr-setting-info">
                            <h4>Włącz powiadomienia</h4>
                            <p>Otrzymuj powiadomienia o ważnych wydarzeniach w systemie</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Przycisk zapisz -->
        <div class="qr-settings-footer">
            <button type="submit" name="submit" class="button button-primary button-large">
                <span class="dashicons dashicons-yes"></span>
                Zapisz ustawienia
            </button>
            <button type="button" class="button button-secondary button-large" onclick="resetToDefaults()">
                <span class="dashicons dashicons-undo"></span>
                Przywróć domyślne
            </button>
        </div>
    </form>

    <!-- Informacje systemowe -->
    <div class="qr-system-info-section">
        <div class="qr-settings-header">
            <h3><span class="dashicons dashicons-info"></span> Informacje systemowe</h3>
            <p>Aktualne informacje o konfiguracji i stanie systemu</p>
        </div>
        
        <div class="qr-system-info-grid">
            <div class="qr-info-card">
                <div class="qr-info-header">
                    <span class="dashicons dashicons-admin-plugins"></span>
                    <h4>Wtyczka</h4>
                </div>
                <div class="qr-info-content">
                    <div class="qr-info-item">
                        <span class="qr-info-label">Wersja:</span>
                        <span class="qr-info-value"><?php echo QR_SYSTEM_VERSION; ?></span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Status:</span>
                        <span class="qr-info-value qr-status-active">Aktywna</span>
                    </div>
                </div>
            </div>

            <div class="qr-info-card">
                <div class="qr-info-header">
                    <span class="dashicons dashicons-database"></span>
                    <h4>Baza danych</h4>
                </div>
                <div class="qr-info-content">
                    <?php
                    $table_codes = $this->wpdb->prefix . 'qr_codes';
                    $table_scans = $this->wpdb->prefix . 'qr_scans';
                    $table_groups = $this->wpdb->prefix . 'qr_groups';
                    
                    $codes_exists = $this->wpdb->get_var("SHOW TABLES LIKE '$table_codes'") === $table_codes;
                    $scans_exists = $this->wpdb->get_var("SHOW TABLES LIKE '$table_scans'") === $table_scans;
                    $groups_exists = $this->wpdb->get_var("SHOW TABLES LIKE '$table_groups'") === $table_groups;
                    ?>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Tabela kodów:</span>
                        <span class="qr-info-value <?php echo $codes_exists ? 'qr-status-ok' : 'qr-status-error'; ?>">
                            <?php echo $codes_exists ? '✓ OK' : '✗ Błąd'; ?>
                        </span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Tabela skanów:</span>
                        <span class="qr-info-value <?php echo $scans_exists ? 'qr-status-ok' : 'qr-status-error'; ?>">
                            <?php echo $scans_exists ? '✓ OK' : '✗ Błąd'; ?>
                        </span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Tabela grup:</span>
                        <span class="qr-info-value <?php echo $groups_exists ? 'qr-status-ok' : 'qr-status-error'; ?>">
                            <?php echo $groups_exists ? '✓ OK' : '✗ Błąd'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="qr-info-card">
                <div class="qr-info-header">
                    <span class="dashicons dashicons-admin-site"></span>
                    <h4>WordPress</h4>
                </div>
                <div class="qr-info-content">
                    <div class="qr-info-item">
                        <span class="qr-info-label">Wersja WP:</span>
                        <span class="qr-info-value"><?php echo get_bloginfo('version'); ?></span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Wersja PHP:</span>
                        <span class="qr-info-value"><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Strona skanowania:</span>
                        <span class="qr-info-value">
                            <a href="<?php echo home_url('/skanowanie/'); ?>" target="_blank">
                                <?php echo home_url('/skanowanie/'); ?>
                            </a>
                        </span>
                    </div>
                </div>
            </div>

            <div class="qr-info-card">
                <div class="qr-info-header">
                    <span class="dashicons dashicons-chart-area"></span>
                    <h4>Statystyki</h4>
                </div>
                <div class="qr-info-content">
                    <?php
                    $total_codes = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$table_codes}");
                    $total_scans = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$table_scans}");
                    $total_groups = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$table_groups}");
                    ?>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Kodów QR:</span>
                        <span class="qr-info-value"><?php echo number_format($total_codes); ?></span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Skanów:</span>
                        <span class="qr-info-value"><?php echo number_format($total_scans); ?></span>
                    </div>
                    <div class="qr-info-item">
                        <span class="qr-info-label">Grup:</span>
                        <span class="qr-info-value"><?php echo number_format($total_groups); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Narzędzia -->
    <div class="qr-tools-section">
        <div class="qr-settings-header">
            <h3><span class="dashicons dashicons-admin-tools"></span> Narzędzia systemowe</h3>
            <p>Zaawansowane narzędzia do zarządzania systemem</p>
        </div>
        
        <div class="qr-tools-grid">
            <div class="qr-tool-card">
                <div class="qr-tool-icon">
                    <span class="dashicons dashicons-database-export"></span>
                </div>
                <div class="qr-tool-content">
                    <h4>Pełny eksport danych</h4>
                    <p>Wyeksportuj wszystkie dane systemu do pliku ZIP</p>
                    <button type="button" class="button button-secondary" onclick="fullExport()">
                        <span class="dashicons dashicons-download"></span> Eksportuj
                    </button>
                </div>
            </div>

            <div class="qr-tool-card">
                <div class="qr-tool-icon">
                    <span class="dashicons dashicons-update"></span>
                </div>
                <div class="qr-tool-content">
                    <h4>Odśwież tabele</h4>
                    <p>Napraw strukturę tabel bazy danych</p>
                    <button type="button" class="button button-secondary" onclick="refreshTables()">
                        <span class="dashicons dashicons-database"></span> Napraw
                    </button>
                </div>
            </div>

            <div class="qr-tool-card">
                <div class="qr-tool-icon">
                    <span class="dashicons dashicons-trash"></span>
                </div>
                <div class="qr-tool-content">
                    <h4>Wyczyść stare dane</h4>
                    <p>Usuń skany starsze niż 90 dni</p>
                    <button type="button" class="button button-secondary" onclick="cleanOldData()">
                        <span class="dashicons dashicons-clean"></span> Wyczyść
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetToDefaults() {
    if (confirm('Czy na pewno chcesz przywrócić domyślne ustawienia?')) {
        // Reset form to defaults
        document.querySelector('input[name="require_confirmation"]').checked = true;
        document.querySelector('input[name="allow_multiple_scans"]').checked = false;
        document.querySelector('input[name="enable_logging"]').checked = true;
        document.querySelector('input[name="auto_archive_days"]').value = 30;
        document.querySelector('input[name="max_codes_per_group"]').value = 1000;
        document.querySelector('input[name="enable_notifications"]').checked = true;
        
        // Show toast
        showToast('Przywrócono domyślne ustawienia', 'success');
    }
}

function fullExport() {
    if (confirm('Czy rozpocząć pełny eksport danych? Może to potrwać kilka minut.')) {
        // Implement full export
        showToast('Eksport rozpoczęty...', 'info');
        
        // AJAX call to export function
        jQuery.ajax({
            url: qr_ajax.url,
            type: 'POST',
            data: {
                action: 'qr_full_export',
                nonce: qr_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showToast('Eksport zakończony pomyślnie', 'success');
                    // Download file
                    window.location.href = response.data.download_url;
                } else {
                    showToast('Błąd podczas eksportu: ' + response.data, 'error');
                }
            },
            error: function() {
                showToast('Błąd połączenia z serwerem', 'error');
            }
        });
    }
}

function refreshTables() {
    if (confirm('Czy naprawić strukturę tabel? To działanie jest bezpieczne.')) {
        showToast('Naprawiam tabele...', 'info');
        
        jQuery.ajax({
            url: qr_ajax.url,
            type: 'POST',
            data: {
                action: 'qr_refresh_tables',
                nonce: qr_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showToast('Tabele zostały naprawione', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Błąd podczas naprawy: ' + response.data, 'error');
                }
            },
            error: function() {
                showToast('Błąd połączenia z serwerem', 'error');
            }
        });
    }
}

function cleanOldData() {
    if (confirm('Czy usunąć skany starsze niż 90 dni? Ta akcja jest nieodwracalna.')) {
        showToast('Czyszczę stare dane...', 'info');
        
        jQuery.ajax({
            url: qr_ajax.url,
            type: 'POST',
            data: {
                action: 'qr_clean_old_data',
                nonce: qr_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showToast(`Usunięto ${response.data.deleted_count} starych skanów`, 'success');
                } else {
                    showToast('Błąd podczas czyszczenia: ' + response.data, 'error');
                }
            },
            error: function() {
                showToast('Błąd połączenia z serwerem', 'error');
            }
        });
    }
}

function showToast(message, type) {
    // Simple toast implementation
    const toast = document.createElement('div');
    toast.className = `qr-toast qr-toast-${type}`;
    toast.innerHTML = `
        <div class="qr-toast-content">
            <span class="qr-toast-message">${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('qr-toast-show'), 100);
    setTimeout(() => {
        toast.classList.remove('qr-toast-show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>