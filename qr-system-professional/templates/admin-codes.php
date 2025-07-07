<?php
/**
 * Admin Codes Management Template - Professional
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('ABSPATH')) {
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$code_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Pobierz wybrany kod do edycji
$edit_code = null;
if ($action === 'edit' && $code_id) {
    global $wpdb;
    $edit_code = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$this->table_codes} WHERE id = %d", $code_id
    ));
}
?>

<div class="wrap qr-admin-wrap">
    <?php if ($action === 'list'): ?>
        <!-- Lista kodów -->
        <div class="qr-header">
            <div class="qr-header-left">
                <h1><span class="dashicons dashicons-qrcode"></span> Zarządzanie kodami QR</h1>
                <p class="qr-subtitle">Twórz, edytuj i monitoruj kody QR dla swojej franczyzy</p>
            </div>
            <div class="qr-header-right">
                <div class="qr-quick-actions">
                    <a href="<?php echo admin_url('admin.php?page=qr-system-codes&action=add'); ?>" class="button button-primary button-hero">
                        <span class="dashicons dashicons-plus"></span> Dodaj kod QR
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=qr-system-codes&action=import'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-upload"></span> Importuj CSV
                    </a>
                    <button type="button" class="button button-secondary" onclick="qrExportCodes()">
                        <span class="dashicons dashicons-download"></span> Eksportuj
                    </button>
                </div>
            </div>
        </div>

        <!-- Filtry i wyszukiwanie -->
        <div class="qr-filters-bar">
            <div class="qr-filters-left">
                <div class="qr-search-box">
                    <input type="text" id="qr-search-codes" placeholder="Szukaj kodów..." class="qr-search-input">
                    <button type="button" class="qr-search-btn">
                        <span class="dashicons dashicons-search"></span>
                    </button>
                </div>
            </div>
            <div class="qr-filters-right">
                <select id="qr-filter-group" class="qr-filter-select">
                    <option value="">Wszystkie grupy</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group->id; ?>"><?php echo esc_html($group->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="qr-filter-status" class="qr-filter-select">
                    <option value="">Wszystkie statusy</option>
                    <option value="active">Aktywny</option>
                    <option value="inactive">Nieaktywny</option>
                    <option value="used">Wykorzystany</option>
                </select>
                <select id="qr-filter-type" class="qr-filter-select">
                    <option value="">Wszystkie typy</option>
                    <option value="normal">Normalny</option>
                    <option value="special">Specjalny</option>
                </select>
            </div>
        </div>

        <!-- Tabela kodów -->
        <div class="qr-codes-table-container">
            <table class="qr-table">
                <thead>
                    <tr>
                        <th class="qr-th-checkbox">
                            <input type="checkbox" id="qr-select-all">
                        </th>
                        <th class="qr-th-code">Kod QR</th>
                        <th class="qr-th-group">Grupa</th>
                        <th class="qr-th-type">Typ</th>
                        <th class="qr-th-status">Status</th>
                        <th class="qr-th-usage">Użycia</th>
                        <th class="qr-th-expiry">Ważność</th>
                        <th class="qr-th-created">Utworzony</th>
                        <th class="qr-th-actions">Akcje</th>
                    </tr>
                </thead>
                <tbody id="qr-codes-tbody">
                    <?php if (empty($codes)): ?>
                        <tr class="qr-empty-row">
                            <td colspan="9" class="qr-empty-cell">
                                <div class="qr-empty-state">
                                    <span class="dashicons dashicons-qrcode"></span>
                                    <h3>Brak kodów QR</h3>
                                    <p>Rozpocznij od utworzenia pierwszego kodu QR dla swojej franczyzy</p>
                                    <a href="<?php echo admin_url('admin.php?page=qr-system-codes&action=add'); ?>" class="button button-primary">
                                        <span class="dashicons dashicons-plus"></span> Utwórz pierwszy kod
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($codes as $code): ?>
                        <tr class="qr-code-row" data-id="<?php echo $code->id; ?>" data-group="<?php echo $code->group_id; ?>" data-status="<?php echo $code->status; ?>" data-type="<?php echo $code->type; ?>">
                            <td class="qr-td-checkbox">
                                <input type="checkbox" class="qr-code-checkbox" value="<?php echo $code->id; ?>">
                            </td>
                            <td class="qr-td-code">
                                <div class="qr-code-info">
                                    <strong class="qr-code-value"><?php echo esc_html($code->code); ?></strong>
                                    <?php if ($code->message): ?>
                                        <div class="qr-code-message"><?php echo esc_html(substr($code->message, 0, 50)); ?><?php echo strlen($code->message) > 50 ? '...' : ''; ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="qr-td-group">
                                <?php if ($code->group_name): ?>
                                    <div class="qr-group-badge" style="background-color: <?php echo esc_attr($code->group_color); ?>">
                                        <?php echo esc_html($code->group_name); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="qr-no-group">Bez grupy</span>
                                <?php endif; ?>
                            </td>
                            <td class="qr-td-type">
                                <span class="qr-type-badge qr-type-<?php echo esc_attr($code->type); ?>">
                                    <?php echo $code->type === 'special' ? 'Specjalny' : 'Normalny'; ?>
                                </span>
                            </td>
                            <td class="qr-td-status">
                                <span class="qr-status-badge qr-status-<?php echo esc_attr($code->status); ?>">
                                    <?php 
                                    switch($code->status) {
                                        case 'active': echo 'Aktywny'; break;
                                        case 'inactive': echo 'Nieaktywny'; break;
                                        case 'used': echo 'Wykorzystany'; break;
                                        default: echo $code->status;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="qr-td-usage">
                                <div class="qr-usage-info">
                                    <div class="qr-usage-count">
                                        <?php echo intval($code->current_uses); ?> / 
                                        <?php echo $code->max_uses > 0 ? intval($code->max_uses) : '∞'; ?>
                                    </div>
                                    <?php if ($code->max_uses > 0): ?>
                                        <div class="qr-usage-bar">
                                            <div class="qr-usage-progress" style="width: <?php echo min(100, ($code->current_uses / $code->max_uses) * 100); ?>%"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="qr-td-expiry">
                                <?php if ($code->expiry_date): ?>
                                    <?php 
                                    $expiry = strtotime($code->expiry_date);
                                    $now = time();
                                    $is_expired = $expiry < $now;
                                    ?>
                                    <div class="qr-expiry-info <?php echo $is_expired ? 'qr-expired' : ''; ?>">
                                        <div class="qr-expiry-date">
                                            <?php echo date('d.m.Y', $expiry); ?>
                                        </div>
                                        <div class="qr-expiry-time">
                                            <?php echo date('H:i', $expiry); ?>
                                        </div>
                                        <?php if ($is_expired): ?>
                                            <div class="qr-expiry-status">Wygasł</div>
                                        <?php else: ?>
                                            <div class="qr-expiry-remaining">
                                                <?php echo human_time_diff($now, $expiry); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="qr-no-expiry">Bezterminowy</span>
                                <?php endif; ?>
                            </td>
                            <td class="qr-td-created">
                                <div class="qr-created-info">
                                    <div class="qr-created-date">
                                        <?php echo date('d.m.Y', strtotime($code->created_at)); ?>
                                    </div>
                                    <div class="qr-created-time">
                                        <?php echo date('H:i', strtotime($code->created_at)); ?>
                                    </div>
                                </div>
                            </td>
                            <td class="qr-td-actions">
                                <div class="qr-actions-menu">
                                    <button type="button" class="qr-action-btn qr-btn-menu" onclick="toggleActionMenu(this)">
                                        <span class="dashicons dashicons-menu"></span>
                                    </button>
                                    <div class="qr-actions-dropdown">
                                        <a href="<?php echo admin_url('admin.php?page=qr-system-codes&action=edit&id=' . $code->id); ?>" class="qr-action-item">
                                            <span class="dashicons dashicons-edit"></span> Edytuj
                                        </a>
                                        <a href="javascript:void(0)" onclick="duplicateCode(<?php echo $code->id; ?>)" class="qr-action-item">
                                            <span class="dashicons dashicons-admin-page"></span> Duplikuj
                                        </a>
                                        <a href="javascript:void(0)" onclick="generateQRImage('<?php echo esc_js($code->code); ?>')" class="qr-action-item">
                                            <span class="dashicons dashicons-download"></span> Pobierz QR
                                        </a>
                                        <div class="qr-action-separator"></div>
                                        <a href="javascript:void(0)" onclick="deleteCode(<?php echo $code->id; ?>)" class="qr-action-item qr-action-danger">
                                            <span class="dashicons dashicons-trash"></span> Usuń
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Bulk actions -->
        <?php if (!empty($codes)): ?>
        <div class="qr-bulk-actions" id="qr-bulk-actions" style="display: none;">
            <div class="qr-bulk-info">
                <span id="qr-selected-count">0</span> kodów zaznaczonych
            </div>
            <div class="qr-bulk-buttons">
                <button type="button" class="button" onclick="bulkAction('activate')">
                    <span class="dashicons dashicons-yes"></span> Aktywuj
                </button>
                <button type="button" class="button" onclick="bulkAction('deactivate')">
                    <span class="dashicons dashicons-dismiss"></span> Dezaktywuj
                </button>
                <button type="button" class="button" onclick="bulkAction('delete')">
                    <span class="dashicons dashicons-trash"></span> Usuń
                </button>
            </div>
        </div>
        <?php endif; ?>

    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Formularz dodawania/edycji kodu -->
        <div class="qr-form-container">
            <div class="qr-form-header">
                <h1>
                    <span class="dashicons dashicons-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></span>
                    <?php echo $action === 'add' ? 'Dodaj nowy kod QR' : 'Edytuj kod QR'; ?>
                </h1>
                <a href="<?php echo admin_url('admin.php?page=qr-system-codes'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-arrow-left"></span> Powrót do listy
                </a>
            </div>

            <form method="post" class="qr-form" action="">
                <?php wp_nonce_field('qr_add_code', 'qr_nonce'); ?>
                
                <div class="qr-form-grid">
                    <!-- Lewa kolumna -->
                    <div class="qr-form-left">
                        <div class="qr-form-section">
                            <h3><span class="dashicons dashicons-admin-settings"></span> Podstawowe informacje</h3>
                            
                            <div class="qr-field-group">
                                <label for="code" class="qr-field-label required">
                                    Kod QR
                                    <span class="qr-field-required">*</span>
                                </label>
                                <div class="qr-input-group">
                                    <input type="text" 
                                           id="code" 
                                           name="code" 
                                           class="qr-input" 
                                           value="<?php echo $edit_code ? esc_attr($edit_code->code) : ''; ?>"
                                           required 
                                           maxlength="50"
                                           pattern="[A-Za-z0-9]+"
                                           title="Tylko litery i cyfry">
                                    <button type="button" class="qr-input-btn" onclick="generateRandomCode()">
                                        <span class="dashicons dashicons-randomize"></span>
                                        Generuj
                                    </button>
                                </div>
                                <p class="qr-field-help">Unikalny kod alfanumeryczny (tylko litery i cyfry)</p>
                            </div>

                            <div class="qr-field-group">
                                <label for="message" class="qr-field-label">
                                    Wiadomość po zeskanowaniu
                                </label>
                                <textarea id="message" 
                                          name="message" 
                                          class="qr-textarea" 
                                          rows="3" 
                                          maxlength="500"
                                          placeholder="np. Rabat 50% na tortillę MAX"><?php echo $edit_code ? esc_textarea($edit_code->message) : ''; ?></textarea>
                                <p class="qr-field-help">Tekst wyświetlany użytkownikowi po pomyślnym zeskanowaniu kodu</p>
                            </div>

                            <div class="qr-field-row">
                                <div class="qr-field-group">
                                    <label for="type" class="qr-field-label">
                                        Typ kodu
                                    </label>
                                    <select id="type" name="type" class="qr-select">
                                        <option value="normal" <?php echo ($edit_code && $edit_code->type === 'normal') ? 'selected' : ''; ?>>
                                            Normalny
                                        </option>
                                        <option value="special" <?php echo ($edit_code && $edit_code->type === 'special') ? 'selected' : ''; ?>>
                                            Specjalny (tylko uprawnieni)
                                        </option>
                                    </select>
                                    <p class="qr-field-help">Kody specjalne mogą skanować tylko uprawnieni użytkownicy</p>
                                </div>

                                <div class="qr-field-group">
                                    <label for="group_id" class="qr-field-label">
                                        Grupa
                                    </label>
                                    <select id="group_id" name="group_id" class="qr-select">
                                        <option value="">Bez grupy</option>
                                        <?php foreach ($groups as $group): ?>
                                            <option value="<?php echo $group->id; ?>" 
                                                    <?php echo ($edit_code && $edit_code->group_id == $group->id) ? 'selected' : ''; ?>>
                                                <?php echo esc_html($group->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="qr-field-help">Grupowanie kodów ułatwia organizację</p>
                                </div>
                            </div>
                        </div>

                        <div class="qr-form-section">
                            <h3><span class="dashicons dashicons-clock"></span> Ważność i limity</h3>
                            
                            <div class="qr-field-group">
                                <label for="expiry_date" class="qr-field-label">
                                    Data i czas ważności
                                </label>
                                <input type="datetime-local" 
                                       id="expiry_date" 
                                       name="expiry_date" 
                                       class="qr-input"
                                       value="<?php echo $edit_code && $edit_code->expiry_date ? date('Y-m-d\TH:i', strtotime($edit_code->expiry_date)) : ''; ?>">
                                <p class="qr-field-help">Pozostaw puste dla kodów bezterminowych</p>
                            </div>

                            <div class="qr-field-group">
                                <label for="max_uses" class="qr-field-label">
                                    Maksymalna liczba użyć
                                </label>
                                <div class="qr-number-input">
                                    <input type="number" 
                                           id="max_uses" 
                                           name="max_uses" 
                                           class="qr-input" 
                                           value="<?php echo $edit_code ? intval($edit_code->max_uses) : '1'; ?>"
                                           min="1" 
                                           max="10000">
                                    <div class="qr-number-controls">
                                        <button type="button" onclick="adjustNumber('max_uses', 1)">+</button>
                                        <button type="button" onclick="adjustNumber('max_uses', -1)">-</button>
                                    </div>
                                </div>
                                <p class="qr-field-help">Ile razy kod może być pomyślnie zeskanowany</p>
                            </div>
                        </div>
                    </div>

                    <!-- Prawa kolumna -->
                    <div class="qr-form-right">
                        <div class="qr-form-section">
                            <h3><span class="dashicons dashicons-visibility"></span> Podgląd kodu QR</h3>
                            <div class="qr-preview-container">
                                <div id="qr-preview" class="qr-preview-placeholder">
                                    <span class="dashicons dashicons-qrcode"></span>
                                    <p>Wpisz kod aby zobaczyć podgląd</p>
                                </div>
                                <div class="qr-preview-actions">
                                    <button type="button" class="button button-small" onclick="refreshPreview()">
                                        <span class="dashicons dashicons-update"></span> Odśwież
                                    </button>
                                    <button type="button" class="button button-small" onclick="downloadQRCode()">
                                        <span class="dashicons dashicons-download"></span> Pobierz
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="qr-form-section">
                            <h3><span class="dashicons dashicons-info"></span> Informacje</h3>
                            <div class="qr-info-list">
                                <?php if ($edit_code): ?>
                                    <div class="qr-info-item">
                                        <span class="qr-info-label">Utworzony:</span>
                                        <span class="qr-info-value">
                                            <?php echo date('d.m.Y H:i', strtotime($edit_code->created_at)); ?>
                                        </span>
                                    </div>
                                    <div class="qr-info-item">
                                        <span class="qr-info-label">Autor:</span>
                                        <span class="qr-info-value">
                                            <?php 
                                            $author = get_user_by('id', $edit_code->created_by);
                                            echo $author ? esc_html($author->display_name) : 'Nieznany';
                                            ?>
                                        </span>
                                    </div>
                                    <div class="qr-info-item">
                                        <span class="qr-info-label">Użycia:</span>
                                        <span class="qr-info-value">
                                            <?php echo intval($edit_code->current_uses); ?> z <?php echo $edit_code->max_uses > 0 ? intval($edit_code->max_uses) : '∞'; ?>
                                        </span>
                                    </div>
                                    <div class="qr-info-item">
                                        <span class="qr-info-label">Status:</span>
                                        <span class="qr-info-value">
                                            <span class="qr-status-badge qr-status-<?php echo esc_attr($edit_code->status); ?>">
                                                <?php 
                                                switch($edit_code->status) {
                                                    case 'active': echo 'Aktywny'; break;
                                                    case 'inactive': echo 'Nieaktywny'; break;
                                                    case 'used': echo 'Wykorzystany'; break;
                                                }
                                                ?>
                                            </span>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="qr-info-placeholder">
                                        <p>Informacje pojawią się po zapisaniu kodu</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="qr-form-footer">
                    <div class="qr-form-actions">
                        <button type="submit" name="submit" class="button button-primary button-large">
                            <span class="dashicons dashicons-yes"></span>
                            <?php echo $action === 'add' ? 'Utwórz kod QR' : 'Zaktualizuj kod'; ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=qr-system-codes'); ?>" class="button button-secondary button-large">
                            <span class="dashicons dashicons-no"></span>
                            Anuluj
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
    <?php elseif ($action === 'import'): ?>
        <!-- Import CSV -->
        <div class="qr-header">
            <div class="qr-header-left">
                <h1><span class="dashicons dashicons-upload"></span> Import kodów z CSV</h1>
                <p class="qr-subtitle">Importuj kody z pliku CSV (jeden kod na linię)</p>
            </div>
            <div class="qr-header-right">
                <a href="<?php echo admin_url('admin.php?page=qr-system-codes'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-arrow-left-alt"></span> Powrót do listy
                </a>
            </div>
        </div>
        
        <div class="qr-form-container">
            <form method="post" enctype="multipart/form-data" class="qr-form">
                <?php wp_nonce_field('qr_import_csv', 'qr_import_nonce'); ?>
                
                <div class="qr-form-section">
                    <h3>Ustawienia importu</h3>
                    
                    <div class="qr-form-group">
                        <label for="csv_file">Plik CSV z kodami QR</label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                        <p class="qr-help-text">
                            Wybierz plik CSV z kodami QR. Każda linia powinna zawierać jeden kod.
                        </p>
                    </div>
                    
                    <div class="qr-form-group">
                        <label for="import_group_id">Grupa dla importowanych kodów</label>
                        <select id="import_group_id" name="group_id">
                            <option value="">Bez grupy</option>
                            <?php
                            $groups = $this->get_groups();
                            foreach ($groups as $group) {
                                echo '<option value="' . $group->id . '">' . esc_html($group->name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="qr-form-group">
                        <label for="import_expiry_date">Data ważności</label>
                        <input type="date" id="import_expiry_date" name="expiry_date" min="<?php echo date('Y-m-d'); ?>">
                        <p class="qr-help-text">Zostaw puste jeśli kody nie mają wygasnąć</p>
                    </div>
                    
                    <div class="qr-form-group">
                        <label for="import_max_uses">Maksymalne użycia</label>
                        <input type="number" id="import_max_uses" name="max_uses" value="1" min="1">
                    </div>
                    
                    <div class="qr-form-group">
                        <label for="import_type">Typ kodów</label>
                        <select id="import_type" name="type">
                            <option value="normal">Normalny</option>
                            <option value="special">Specjalny (tylko dla uprawnionch)</option>
                        </select>
                    </div>
                    
                    <div class="qr-form-group">
                        <label for="import_message">Wiadomość po zeskanowaniu (opcjonalna)</label>
                        <textarea id="import_message" name="message" rows="3" placeholder="Np. Klient uzyskał 50% rabatu"></textarea>
                        <p class="qr-help-text">
                            Zostaw puste aby używać standardowych statusów: Powodzenie, Niepowodzenie, Przedawniony
                        </p>
                    </div>
                </div>
                
                <div class="qr-form-actions">
                    <button type="submit" name="import_csv" class="button button-primary button-hero">
                        <span class="dashicons dashicons-upload"></span> Importuj kody
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=qr-system-codes'); ?>" class="button button-secondary">
                        Anuluj
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
// JavaScript functions będą w admin.js
document.addEventListener('DOMContentLoaded', function() {
    // Inicjalizacja formularza kodów
    if (document.querySelector('.qr-form')) {
        initCodeForm();
    }
    
    // Inicjalizacja tabeli kodów
    if (document.querySelector('.qr-codes-table-container')) {
        initCodesTable();
    }
});
</script>