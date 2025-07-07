<?php
/**
 * Admin Statistics Template - Professional
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('ABSPATH')) {
    exit;
}

// Pobierz dane statystyczne
global $wpdb;
$table_codes = $wpdb->prefix . 'qr_codes';
$table_scans = $wpdb->prefix . 'qr_scans';
$table_groups = $wpdb->prefix . 'qr_groups';

// Podstawowe statystyki
$total_codes = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_codes}");
$active_codes = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_codes} WHERE is_active = 1");
$total_scans = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_scans}");
$successful_scans = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_scans} WHERE scan_result = 'success'");

// Statystyki dzisiejsze
$today = date('Y-m-d');
$today_scans = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_scans} WHERE DATE(scan_time) = %s", $today));
$today_success = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_scans} WHERE DATE(scan_time) = %s AND scan_result = 'success'", $today));

// Statystyki tygodniowe
$week_ago = date('Y-m-d', strtotime('-7 days'));
$week_scans = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_scans} WHERE DATE(scan_time) >= %s", $week_ago));

// Top użytkownicy
$top_users = $wpdb->get_results("
    SELECT u.display_name, COUNT(s.id) as scan_count
    FROM {$table_scans} s
    LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
    WHERE s.scan_result = 'success'
    GROUP BY s.user_id
    ORDER BY scan_count DESC
    LIMIT 10
");

// Statystyki grup
$group_stats = $wpdb->get_results("
    SELECT g.name, g.color,
           COUNT(c.id) as total_codes,
           COUNT(CASE WHEN c.is_active = 1 THEN 1 END) as active_codes,
           (SELECT COUNT(*) FROM {$table_scans} s 
            JOIN {$table_codes} c2 ON s.code = c2.code 
            WHERE c2.group_id = g.id AND s.scan_result = 'success') as successful_scans
    FROM {$table_groups} g
    LEFT JOIN {$table_codes} c ON g.id = c.group_id
    GROUP BY g.id
    ORDER BY successful_scans DESC
");

// Statystyki dzienne (ostatnie 30 dni)
$daily_stats = $wpdb->get_results("
    SELECT DATE(scan_time) as date, 
           COUNT(*) as total_scans,
           COUNT(CASE WHEN scan_result = 'success' THEN 1 END) as success_scans
    FROM {$table_scans}
    WHERE scan_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(scan_time)
    ORDER BY date DESC
");

$success_rate = $total_scans > 0 ? round(($successful_scans / $total_scans) * 100, 1) : 0;
?>

<div class="wrap qr-admin-wrap">
    <div class="qr-header">
        <div class="qr-header-left">
            <h1><span class="dashicons dashicons-chart-line"></span> Statystyki i analityka</h1>
            <p class="qr-subtitle">Kompleksowa analiza wydajności systemu QR kodów</p>
        </div>
        <div class="qr-header-right">
            <div class="qr-quick-actions">
                <button type="button" class="button button-secondary" onclick="qrExportStats()">
                    <span class="dashicons dashicons-download"></span> Eksportuj statystyki
                </button>
                <button type="button" class="button button-secondary" onclick="refreshStats()">
                    <span class="dashicons dashicons-update"></span> Odśwież
                </button>
            </div>
        </div>
    </div>

    <!-- Główne wskaźniki KPI -->
    <div class="qr-kpi-grid">
        <div class="qr-kpi-card qr-kpi-primary">
            <div class="qr-kpi-icon">
                <span class="dashicons dashicons-admin-page"></span>
            </div>
            <div class="qr-kpi-content">
                <div class="qr-kpi-number"><?php echo number_format($total_codes); ?></div>
                <div class="qr-kpi-label">Łączna liczba kodów</div>
                <div class="qr-kpi-meta"><?php echo number_format($active_codes); ?> aktywnych</div>
            </div>
        </div>

        <div class="qr-kpi-card qr-kpi-success">
            <div class="qr-kpi-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="qr-kpi-content">
                <div class="qr-kpi-number"><?php echo number_format($successful_scans); ?></div>
                <div class="qr-kpi-label">Udane skany</div>
                <div class="qr-kpi-meta">z <?php echo number_format($total_scans); ?> łącznych</div>
            </div>
        </div>

        <div class="qr-kpi-card qr-kpi-info">
            <div class="qr-kpi-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="qr-kpi-content">
                <div class="qr-kpi-number"><?php echo number_format($today_scans); ?></div>
                <div class="qr-kpi-label">Skany dzisiaj</div>
                <div class="qr-kpi-meta"><?php echo number_format($today_success); ?> pomyślnych</div>
            </div>
        </div>

        <div class="qr-kpi-card qr-kpi-warning">
            <div class="qr-kpi-icon">
                <span class="dashicons dashicons-chart-area"></span>
            </div>
            <div class="qr-kpi-content">
                <div class="qr-kpi-number"><?php echo $success_rate; ?>%</div>
                <div class="qr-kpi-label">Wskaźnik sukcesu</div>
                <div class="qr-kpi-meta"><?php echo number_format($week_scans); ?> w tym tygodniu</div>
            </div>
        </div>
    </div>

    <div class="qr-stats-container">
        <!-- Wykres aktywności dziennej -->
        <div class="qr-stats-section">
            <div class="qr-stats-header">
                <h3><span class="dashicons dashicons-chart-line"></span> Aktywność dzienna (ostatnie 30 dni)</h3>
                <div class="qr-stats-controls">
                    <select id="chart-period" onchange="updateChart()">
                        <option value="30">Ostatnie 30 dni</option>
                        <option value="7">Ostatnie 7 dni</option>
                        <option value="90">Ostatnie 3 miesiące</option>
                    </select>
                </div>
            </div>
            <div class="qr-chart-container">
                <canvas id="dailyActivityChart" width="800" height="300"></canvas>
            </div>
        </div>

        <div class="qr-stats-row">
            <!-- Top użytkownicy -->
            <div class="qr-stats-section qr-stats-half">
                <div class="qr-stats-header">
                    <h3><span class="dashicons dashicons-admin-users"></span> Najbardziej aktywni użytkownicy</h3>
                </div>
                <div class="qr-top-users">
                    <?php if (!empty($top_users)): ?>
                        <?php foreach ($top_users as $index => $user): ?>
                            <div class="qr-top-user">
                                <div class="qr-user-rank"><?php echo $index + 1; ?></div>
                                <div class="qr-user-info">
                                    <div class="qr-user-name"><?php echo esc_html($user->display_name ?: 'Nieznany użytkownik'); ?></div>
                                    <div class="qr-user-scans"><?php echo number_format($user->scan_count); ?> pomyślnych skanów</div>
                                </div>
                                <div class="qr-user-progress">
                                    <?php $max_scans = $top_users[0]->scan_count; ?>
                                    <div class="qr-progress-bar">
                                        <div class="qr-progress-fill" style="width: <?php echo $max_scans > 0 ? ($user->scan_count / $max_scans) * 100 : 0; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="qr-empty-state">
                            <span class="dashicons dashicons-admin-users"></span>
                            <p>Brak danych o użytkownikach</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statystyki grup -->
            <div class="qr-stats-section qr-stats-half">
                <div class="qr-stats-header">
                    <h3><span class="dashicons dashicons-category"></span> Wydajność grup</h3>
                </div>
                <div class="qr-group-performance">
                    <?php if (!empty($group_stats)): ?>
                        <?php foreach ($group_stats as $group): ?>
                            <div class="qr-group-stat">
                                <div class="qr-group-indicator" style="background-color: <?php echo esc_attr($group->color); ?>"></div>
                                <div class="qr-group-details">
                                    <div class="qr-group-name"><?php echo esc_html($group->name); ?></div>
                                    <div class="qr-group-metrics">
                                        <span class="qr-metric">
                                            <strong><?php echo number_format($group->total_codes); ?></strong> kodów
                                        </span>
                                        <span class="qr-metric">
                                            <strong><?php echo number_format($group->active_codes); ?></strong> aktywnych
                                        </span>
                                        <span class="qr-metric">
                                            <strong><?php echo number_format($group->successful_scans); ?></strong> skanów
                                        </span>
                                    </div>
                                </div>
                                <div class="qr-group-chart">
                                    <?php 
                                    $success_rate = $group->total_codes > 0 ? ($group->successful_scans / $group->total_codes) * 100 : 0;
                                    ?>
                                    <div class="qr-mini-chart">
                                        <div class="qr-mini-progress" style="width: <?php echo min($success_rate, 100); ?>%"></div>
                                    </div>
                                    <div class="qr-mini-label"><?php echo round($success_rate, 1); ?>%</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="qr-empty-state">
                            <span class="dashicons dashicons-category"></span>
                            <p>Brak grup do analizy</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Szczegółowa tabela statystyk dziennych -->
        <div class="qr-stats-section">
            <div class="qr-stats-header">
                <h3><span class="dashicons dashicons-calendar-alt"></span> Szczegółowe statystyki dzienne</h3>
                <div class="qr-stats-controls">
                    <button type="button" class="button button-secondary" onclick="toggleDailyTable()">
                        <span class="dashicons dashicons-visibility"></span> Pokaż/ukryj szczegóły
                    </button>
                </div>
            </div>
            <div id="daily-stats-table" class="qr-daily-stats-table" style="display: none;">
                <table class="qr-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Łączne skany</th>
                            <th>Pomyślne skany</th>
                            <th>Wskaźnik sukcesu</th>
                            <th>Dzień tygodnia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daily_stats)): ?>
                            <?php foreach ($daily_stats as $day): ?>
                                <?php 
                                $day_success_rate = $day->total_scans > 0 ? ($day->success_scans / $day->total_scans) * 100 : 0;
                                $day_name = date('l', strtotime($day->date));
                                $day_names = [
                                    'Monday' => 'Poniedziałek',
                                    'Tuesday' => 'Wtorek', 
                                    'Wednesday' => 'Środa',
                                    'Thursday' => 'Czwartek',
                                    'Friday' => 'Piątek',
                                    'Saturday' => 'Sobota',
                                    'Sunday' => 'Niedziela'
                                ];
                                ?>
                                <tr>
                                    <td><?php echo date('d.m.Y', strtotime($day->date)); ?></td>
                                    <td><?php echo number_format($day->total_scans); ?></td>
                                    <td><?php echo number_format($day->success_scans); ?></td>
                                    <td>
                                        <div class="qr-progress-cell">
                                            <div class="qr-progress-bar">
                                                <div class="qr-progress-fill" style="width: <?php echo $day_success_rate; ?>%"></div>
                                            </div>
                                            <span class="qr-progress-text"><?php echo round($day_success_rate, 1); ?>%</span>
                                        </div>
                                    </td>
                                    <td><?php echo $day_names[$day_name] ?? $day_name; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="qr-empty-cell">
                                    <div class="qr-empty-state">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                        <p>Brak danych dziennych</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Chart data from PHP
const dailyStatsData = <?php echo json_encode($daily_stats); ?>;

document.addEventListener('DOMContentLoaded', function() {
    initializeDailyChart();
});

function initializeDailyChart() {
    const ctx = document.getElementById('dailyActivityChart');
    if (!ctx) return;
    
    const chartCtx = ctx.getContext('2d');
    
    // Prepare data
    const labels = dailyStatsData.map(day => {
        const date = new Date(day.date);
        return date.toLocaleDateString('pl-PL', { day: '2-digit', month: '2-digit' });
    }).reverse();
    
    const totalScans = dailyStatsData.map(day => parseInt(day.total_scans)).reverse();
    const successScans = dailyStatsData.map(day => parseInt(day.success_scans)).reverse();
    
    // Create chart (simple implementation without Chart.js)
    drawSimpleChart(chartCtx, labels, totalScans, successScans);
}

function drawSimpleChart(ctx, labels, totalData, successData) {
    const canvas = ctx.canvas;
    const width = canvas.width;
    const height = canvas.height;
    const padding = 40;
    
    ctx.clearRect(0, 0, width, height);
    
    const maxValue = Math.max(...totalData, 10);
    const chartWidth = width - (padding * 2);
    const chartHeight = height - (padding * 2);
    
    // Draw grid
    ctx.strokeStyle = '#e0e0e0';
    ctx.lineWidth = 1;
    
    // Horizontal grid lines
    for (let i = 0; i <= 5; i++) {
        const y = padding + (chartHeight / 5) * i;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.stroke();
        
        // Y-axis labels
        ctx.fillStyle = '#666';
        ctx.font = '12px Arial';
        ctx.textAlign = 'right';
        const value = Math.round(maxValue - (maxValue / 5) * i);
        ctx.fillText(value.toString(), padding - 10, y + 4);
    }
    
    // Draw data
    if (totalData.length > 1) {
        const stepX = chartWidth / (totalData.length - 1);
        
        // Draw total scans line
        ctx.strokeStyle = '#007cba';
        ctx.lineWidth = 2;
        ctx.beginPath();
        
        totalData.forEach((value, index) => {
            const x = padding + stepX * index;
            const y = padding + chartHeight - (value / maxValue) * chartHeight;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        ctx.stroke();
        
        // Draw success scans line
        ctx.strokeStyle = '#00a32a';
        ctx.lineWidth = 2;
        ctx.beginPath();
        
        successData.forEach((value, index) => {
            const x = padding + stepX * index;
            const y = padding + chartHeight - (value / maxValue) * chartHeight;
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        ctx.stroke();
        
        // Draw points
        totalData.forEach((value, index) => {
            const x = padding + stepX * index;
            const y = padding + chartHeight - (value / maxValue) * chartHeight;
            
            ctx.fillStyle = '#007cba';
            ctx.beginPath();
            ctx.arc(x, y, 3, 0, 2 * Math.PI);
            ctx.fill();
        });
        
        successData.forEach((value, index) => {
            const x = padding + stepX * index;
            const y = padding + chartHeight - (value / maxValue) * chartHeight;
            
            ctx.fillStyle = '#00a32a';
            ctx.beginPath();
            ctx.arc(x, y, 3, 0, 2 * Math.PI);
            ctx.fill();
        });
    }
    
    // Legend
    ctx.fillStyle = '#007cba';
    ctx.fillRect(padding, height - 30, 15, 10);
    ctx.fillStyle = '#333';
    ctx.font = '12px Arial';
    ctx.textAlign = 'left';
    ctx.fillText('Łączne skany', padding + 20, height - 22);
    
    ctx.fillStyle = '#00a32a';
    ctx.fillRect(padding + 120, height - 30, 15, 10);
    ctx.fillText('Pomyślne skany', padding + 140, height - 22);
}

function updateChart() {
    // Implement chart period update
    const period = document.getElementById('chart-period').value;
    // AJAX call to get new data
    showToast('Aktualizuję wykres...', 'info');
}

function qrExportStats() {
    showToast('Przygotowuję eksport statystyk...', 'info');
    
    // Prepare CSV data
    const csvData = [
        ['Metryka', 'Wartość'],
        ['Łączna liczba kodów', <?php echo $total_codes; ?>],
        ['Aktywne kody', <?php echo $active_codes; ?>],
        ['Łączne skany', <?php echo $total_scans; ?>],
        ['Pomyślne skany', <?php echo $successful_scans; ?>],
        ['Wskaźnik sukcesu', '<?php echo $success_rate; ?>%'],
        ['Skany dzisiaj', <?php echo $today_scans; ?>],
        ['Pomyślne skany dzisiaj', <?php echo $today_success; ?>],
        ['Skany w tym tygodniu', <?php echo $week_scans; ?>]
    ];
    
    downloadCSV(csvData, `qr-statystyki-${getCurrentDate()}.csv`);
    showToast('Statystyki zostały wyeksportowane', 'success');
}

function refreshStats() {
    location.reload();
}

function toggleDailyTable() {
    const table = document.getElementById('daily-stats-table');
    if (table.style.display === 'none') {
        table.style.display = 'block';
    } else {
        table.style.display = 'none';
    }
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

function showToast(message, type) {
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
