<?php
/**
 * Admin Scans Template - Professional
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap qr-admin-wrap">
    <div class="qr-header">
        <div class="qr-header-left">
            <h1><span class="dashicons dashicons-camera"></span> Historia skanów</h1>
            <p class="qr-subtitle">Monitoruj wszystkie skany kodów QR w systemie</p>
        </div>
        <div class="qr-header-right">
            <div class="qr-quick-actions">
                <button type="button" class="button button-secondary" onclick="qrExportScans()">
                    <span class="dashicons dashicons-download"></span> Eksportuj skany
                </button>
                <button type="button" class="button button-secondary" onclick="refreshScans()">
                    <span class="dashicons dashicons-update"></span> Odśwież
                </button>
            </div>
        </div>
    </div>

    <!-- Statystyki skanów -->
    <div class="qr-scan-stats">
        <div class="qr-stat-card qr-stat-success">
            <div class="qr-stat-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="qr-stat-content">
                <div class="qr-stat-number"><?php echo isset($stats['success_scans']) ? number_format($stats['success_scans']) : '0'; ?></div>
                <div class="qr-stat-label">Pomyślnych skanów</div>
            </div>
        </div>

        <div class="qr-stat-card qr-stat-warning">
            <div class="qr-stat-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="qr-stat-content">
                <div class="qr-stat-number"><?php echo isset($stats['error_scans']) ? number_format($stats['error_scans']) : '0'; ?></div>
                <div class="qr-stat-label">Nieudanych skanów</div>
            </div>
        </div>

        <div class="qr-stat-card qr-stat-info">
            <div class="qr-stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="qr-stat-content">
                <div class="qr-stat-number"><?php echo isset($stats['today_scans']) ? number_format($stats['today_scans']) : '0'; ?></div>
                <div class="qr-stat-label">Skany dziś</div>
            </div>
        </div>

        <div class="qr-stat-card qr-stat-primary">
            <div class="qr-stat-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="qr-stat-content">
                <div class="qr-stat-number"><?php echo isset($stats['success_rate']) ? $stats['success_rate'] : '0'; ?>%</div>
                <div class="qr-stat-label">Skuteczność</div>
            </div>
        </div>
    </div>

    <!-- Filtry -->
    <div class="qr-filters-bar">
        <div class="qr-filters-left">
            <div class="qr-search-box">
                <input type="text" id="qr-search-scans" placeholder="Szukaj skanów..." class="qr-search-input">
                <button type="button" class="qr-search-btn">
                    <span class="dashicons dashicons-search"></span>
                </button>
            </div>
        </div>
        <div class="qr-filters-right">
            <select id="qr-filter-result" class="qr-filter-select">
                <option value="">Wszystkie wyniki</option>
                <option value="success">Pomyślne</option>
                <option value="error">Błędy</option>
                <option value="expired">Wygasłe</option>
                <option value="used">Wykorzystane</option>
            </select>
            <input type="date" id="qr-filter-date" class="qr-filter-date">
        </div>
    </div>

    <!-- Tabela skanów -->
    <div class="qr-scans-table-container">
        <table class="qr-table">
            <thead>
                <tr>
                    <th class="qr-th-code">Kod QR</th>
                    <th class="qr-th-user">Użytkownik</th>
                    <th class="qr-th-location">Lokalizacja</th>
                    <th class="qr-th-result">Wynik</th>
                    <th class="qr-th-time">Data i czas</th>
                    <th class="qr-th-message">Wiadomość</th>
                    <th class="qr-th-ip">IP</th>
                </tr>
            </thead>
            <tbody id="qr-scans-tbody">
                <?php if (empty($scans)): ?>
                    <tr class="qr-empty-row">
                        <td colspan="7" class="qr-empty-cell">
                            <div class="qr-empty-state">
                                <span class="dashicons dashicons-camera"></span>
                                <h3>Brak skanów</h3>
                                <p>Gdy użytkownicy zaczną skanować kody, tutaj pojawią się wyniki</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($scans as $scan): ?>
                    <tr class="qr-scan-row" data-result="<?php echo esc_attr($scan->scan_result); ?>" data-date="<?php echo date('Y-m-d', strtotime($scan->scan_time)); ?>">
                        <td class="qr-td-code">
                            <div class="qr-scan-code">
                                <strong><?php echo esc_html($scan->code); ?></strong>
                                <?php if ($scan->code_message): ?>
                                    <div class="qr-scan-code-message">
                                        <?php echo esc_html(substr($scan->code_message, 0, 30)); ?><?php echo strlen($scan->code_message) > 30 ? '...' : ''; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="qr-td-user">
                            <div class="qr-scan-user">
                                <?php if ($scan->user_name): ?>
                                    <strong><?php echo esc_html($scan->user_name); ?></strong>
                                    <div class="qr-scan-user-id">ID: <?php echo intval($scan->user_id); ?></div>
                                <?php else: ?>
                                    <span class="qr-unknown-user">Nieznany użytkownik</span>
                                    <div class="qr-scan-user-id">ID: <?php echo intval($scan->user_id); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="qr-td-location">
                            <?php if ($scan->location): ?>
                                <div class="qr-scan-location">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php echo esc_html($scan->location); ?>
                                </div>
                            <?php else: ?>
                                <span class="qr-no-location">Brak lokalizacji</span>
                            <?php endif; ?>
                        </td>
                        <td class="qr-td-result">
                            <span class="qr-result-badge qr-result-<?php echo esc_attr($scan->scan_result); ?>">
                                <?php 
                                switch($scan->scan_result) {
                                    case 'success': 
                                        echo '<span class="dashicons dashicons-yes-alt"></span> Sukces'; 
                                        break;
                                    case 'error': 
                                        echo '<span class="dashicons dashicons-warning"></span> Błąd'; 
                                        break;
                                    case 'expired': 
                                        echo '<span class="dashicons dashicons-clock"></span> Wygasł'; 
                                        break;
                                    case 'used': 
                                        echo '<span class="dashicons dashicons-dismiss"></span> Wykorzystany'; 
                                        break;
                                    default: 
                                        echo esc_html($scan->scan_result);
                                }
                                ?>
                            </span>
                        </td>
                        <td class="qr-td-time">
                            <div class="qr-scan-time">
                                <div class="qr-scan-date">
                                    <?php echo date('d.m.Y', strtotime($scan->scan_time)); ?>
                                </div>
                                <div class="qr-scan-hour">
                                    <?php echo date('H:i:s', strtotime($scan->scan_time)); ?>
                                </div>
                                <div class="qr-scan-relative">
                                    <?php echo human_time_diff(strtotime($scan->scan_time), time()); ?> temu
                                </div>
                            </div>
                        </td>
                        <td class="qr-td-message">
                            <?php if ($scan->error_message): ?>
                                <div class="qr-scan-error-message" title="<?php echo esc_attr($scan->error_message); ?>">
                                    <?php echo esc_html(substr($scan->error_message, 0, 50)); ?><?php echo strlen($scan->error_message) > 50 ? '...' : ''; ?>
                                </div>
                            <?php else: ?>
                                <span class="qr-no-message">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="qr-td-ip">
                            <?php if ($scan->ip_address): ?>
                                <code class="qr-ip-address"><?php echo esc_html($scan->ip_address); ?></code>
                            <?php else: ?>
                                <span class="qr-no-ip">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination (jeśli potrzebna) -->
    <?php if (count($scans) >= 50): ?>
        <div class="qr-pagination">
            <p>Wyświetlane: ostatnie 50 skanów. <a href="javascript:void(0)" onclick="loadMoreScans()">Załaduj więcej</a></p>
        </div>
    <?php endif; ?>
</div>

<script>
// Funkcje filtrowania skanów
document.addEventListener('DOMContentLoaded', function() {
    initScansFilters();
});

function initScansFilters() {
    const searchInput = document.getElementById('qr-search-scans');
    const filterResult = document.getElementById('qr-filter-result');
    const filterDate = document.getElementById('qr-filter-date');
    
    let searchTimeout;
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterScans, 300);
    });
    
    // Filter dropdowns
    filterResult.addEventListener('change', filterScans);
    filterDate.addEventListener('change', filterScans);
}

function filterScans() {
    const searchTerm = document.getElementById('qr-search-scans').value.toLowerCase();
    const filterResult = document.getElementById('qr-filter-result').value;
    const filterDate = document.getElementById('qr-filter-date').value;
    
    const rows = document.querySelectorAll('.qr-scan-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const code = row.querySelector('.qr-scan-code strong').textContent.toLowerCase();
        const user = row.querySelector('.qr-scan-user strong')?.textContent.toLowerCase() || '';
        const location = row.querySelector('.qr-scan-location')?.textContent.toLowerCase() || '';
        const result = row.dataset.result;
        const date = row.dataset.date;
        
        let visible = true;
        
        // Search filter
        if (searchTerm && !code.includes(searchTerm) && !user.includes(searchTerm) && !location.includes(searchTerm)) {
            visible = false;
        }
        
        // Result filter
        if (filterResult && result !== filterResult) {
            visible = false;
        }
        
        // Date filter
        if (filterDate && date !== filterDate) {
            visible = false;
        }
        
        if (visible) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Show/hide empty state for filtered results
    const emptyRow = document.querySelector('.qr-empty-row');
    const tbody = document.getElementById('qr-scans-tbody');
    
    if (visibleCount === 0 && !emptyRow) {
        tbody.insertAdjacentHTML('beforeend', `
            <tr class="qr-empty-row qr-filtered-empty">
                <td colspan="7" class="qr-empty-cell">
                    <div class="qr-empty-state">
                        <span class="dashicons dashicons-search"></span>
                        <h3>Brak wyników</h3>
                        <p>Nie znaleziono skanów spełniających kryteria</p>
                        <button type="button" class="button" onclick="clearScansFilters()">Wyczyść filtry</button>
                    </div>
                </td>
            </tr>
        `);
    } else if (visibleCount > 0) {
        const filteredEmpty = document.querySelector('.qr-filtered-empty');
        if (filteredEmpty) {
            filteredEmpty.remove();
        }
    }
}

function clearScansFilters() {
    document.getElementById('qr-search-scans').value = '';
    document.getElementById('qr-filter-result').value = '';
    document.getElementById('qr-filter-date').value = '';
    
    const filteredEmpty = document.querySelector('.qr-filtered-empty');
    if (filteredEmpty) {
        filteredEmpty.remove();
    }
    
    document.querySelectorAll('.qr-scan-row').forEach(row => {
        row.style.display = '';
    });
}

function qrExportScans() {
    const visibleRows = document.querySelectorAll('.qr-scan-row:not([style*="display: none"])');
    
    if (visibleRows.length === 0) {
        alert('Brak skanów do eksportu');
        return;
    }
    
    const data = [];
    
    // Header
    data.push([
        'Kod QR',
        'Użytkownik', 
        'ID Użytkownika',
        'Lokalizacja',
        'Wynik',
        'Data',
        'Godzina',
        'Wiadomość błędu',
        'Adres IP'
    ]);
    
    // Data
    visibleRows.forEach(row => {
        const code = row.querySelector('.qr-scan-code strong').textContent.trim();
        const userName = row.querySelector('.qr-scan-user strong')?.textContent.trim() || 'Nieznany';
        const userId = row.querySelector('.qr-scan-user-id').textContent.replace('ID: ', '').trim();
        const location = row.querySelector('.qr-scan-location')?.textContent.trim() || '-';
        const result = row.querySelector('.qr-result-badge').textContent.trim();
        const date = row.querySelector('.qr-scan-date').textContent.trim();
        const time = row.querySelector('.qr-scan-hour').textContent.trim();
        const errorMessage = row.querySelector('.qr-scan-error-message')?.textContent.trim() || '-';
        const ip = row.querySelector('.qr-ip-address')?.textContent.trim() || '-';
        
        data.push([code, userName, userId, location, result, date, time, errorMessage, ip]);
    });
    
    downloadCSV(data, `qr-skany-${getCurrentDate()}.csv`);
}

function refreshScans() {
    location.reload();
}

function loadMoreScans() {
    // Implementacja ładowania kolejnych skanów przez AJAX
    alert('Funkcja w przygotowaniu');
}

function getCurrentDate() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function downloadCSV(data, filename) {
    const csv = data.map(row => 
        row.map(cell => `"${String(cell).replace(/"/g, '""')}"`)
           .join(',')
    ).join('\n');
    
    const BOM = '\uFEFF';
    const blob = new Blob([BOM + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>