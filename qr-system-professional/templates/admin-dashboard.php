<?php
/**
 * Admin Dashboard Template - Professional
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap qr-admin-wrap">
    <div class="qr-header">
        <div class="qr-header-left">
            <h1><span class="dashicons dashicons-qrcode"></span> QR-System</h1>
            <p class="qr-subtitle">Zaawansowany system zarządzania kodami QR dla franczyz</p>
        </div>
        <div class="qr-header-right">
            <div class="qr-quick-actions">
                <a href="<?php echo admin_url('admin.php?page=qr-system-codes&action=add'); ?>" class="button button-primary button-hero">
                    <span class="dashicons dashicons-plus"></span> Dodaj kod QR
                </a>
                <a href="<?php echo home_url('/skanowanie/'); ?>" target="_blank" class="button button-secondary">
                    <span class="dashicons dashicons-smartphone"></span> Strona skanowania
                </a>
            </div>
        </div>
    </div>

    <!-- Statystyki główne -->
    <div class="qr-stats-grid">
        <div class="qr-stat-card qr-stat-primary">
            <div class="qr-stat-icon">
                <span class="dashicons dashicons-qrcode"></span>
            </div>
            <div class="qr-stat-content">
                <div class="qr-stat-number"><?php echo number_format($stats['total_codes']); ?></div>
                <div class="qr-stat-label">Wszystkich kodów</div>
                <div class="qr-stat-change">
                    <span class="qr-stat-active"><?php echo number_format($stats['active_codes']); ?> aktywnych</span>
                </div>
            </div>
        </div>

        <div class="qr-stat-card qr-stat-success">
            <div class="qr-stat-icon">
                <span class="dashicons dashicons-camera"></span>
            </div>
            <div class="qr-stat-content">
                <div class="qr-stat-number"><?php echo number_format($stats['total_scans']); ?></div>
                <div class="qr-stat-label">Wszystkich skanów</div>
                <div class="qr-stat-change">
                    <span class="qr-stat-today"><?php echo number_format($stats['today_scans']); ?> dziś</span>
                </div>
            </div>
        </div>

        <div class="qr-stat-card qr-stat-info">
            <div class="qr-stat-icon">
                <span class="dashicons dashicons-chart-area"></span>
            </div>
            <div class="qr-stat-content">
                <div class="qr-stat-number"><?php echo $stats['success_rate']; ?>%</div>
                <div class="qr-stat-label">Skuteczność skanów</div>
                <div class="qr-stat-change">
                    <span class="qr-stat-info">ostatnie 30 dni</span>
                </div>
            </div>
        </div>

        <div class="qr-stat-card qr-stat-warning">
            <div class="qr-stat-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="qr-stat-content">
                <div class="qr-stat-number"><?php echo count($groups); ?></div>
                <div class="qr-stat-label">Aktywnych grup</div>
                <div class="qr-stat-change">
                    <a href="<?php echo admin_url('admin.php?page=qr-system-groups'); ?>">Zarządzaj</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Główny obszar -->
    <div class="qr-dashboard-content">
        <div class="qr-dashboard-left">
            <!-- Ostatnie skany -->
            <div class="qr-widget">
                <div class="qr-widget-header">
                    <h3><span class="dashicons dashicons-clock"></span> Ostatnie skany</h3>
                    <a href="<?php echo admin_url('admin.php?page=qr-system-scans'); ?>" class="qr-widget-link">Zobacz wszystkie</a>
                </div>
                <div class="qr-widget-content">
                    <?php if (!empty($recent_scans)): ?>
                        <div class="qr-recent-scans">
                            <?php foreach ($recent_scans as $scan): ?>
                                <div class="qr-scan-item">
                                    <div class="qr-scan-code">
                                        <strong><?php echo esc_html($scan->code); ?></strong>
                                    </div>
                                    <div class="qr-scan-user">
                                        <?php echo esc_html($scan->user_name ?: 'Nieznany użytkownik'); ?>
                                    </div>
                                    <div class="qr-scan-time">
                                        <?php echo human_time_diff(strtotime($scan->scan_time), time()) . ' temu'; ?>
                                    </div>
                                    <div class="qr-scan-location">
                                        <small><?php echo esc_html($scan->location ?: 'Brak lokalizacji'); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="qr-empty-state">
                            <span class="dashicons dashicons-camera"></span>
                            <p>Brak ostatnich skanów</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Grupy kodów -->
            <div class="qr-widget">
                <div class="qr-widget-header">
                    <h3><span class="dashicons dashicons-category"></span> Grupy kodów</h3>
                    <a href="<?php echo admin_url('admin.php?page=qr-system-groups&action=add'); ?>" class="qr-widget-link">Dodaj grupę</a>
                </div>
                <div class="qr-widget-content">
                    <?php if (!empty($groups)): ?>
                        <div class="qr-groups-list">
                            <?php foreach (array_slice($groups, 0, 5) as $group): ?>
                                <div class="qr-group-item">
                                    <div class="qr-group-color" style="background-color: <?php echo esc_attr($group->color); ?>"></div>
                                    <div class="qr-group-name">
                                        <strong><?php echo esc_html($group->name); ?></strong>
                                    </div>
                                    <div class="qr-group-stats">
                                        <span class="qr-group-count">
                                            <?php 
                                            $codes_count = $this->wpdb->get_var($this->wpdb->prepare(
                                                "SELECT COUNT(*) FROM {$this->table_codes} WHERE group_id = %d", 
                                                $group->id
                                            )); 
                                            echo intval($codes_count);
                                            ?> kodów
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($groups) > 5): ?>
                            <div class="qr-widget-footer">
                                <a href="<?php echo admin_url('admin.php?page=qr-system-groups'); ?>">Zobacz wszystkie grupy (<?php echo count($groups); ?>)</a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="qr-empty-state">
                            <span class="dashicons dashicons-category"></span>
                            <p>Brak grup kodów</p>
                            <a href="<?php echo admin_url('admin.php?page=qr-system-groups&action=add'); ?>" class="button button-small">Utwórz pierwszą grupę</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="qr-dashboard-right">
            <!-- Wykres aktywności -->
            <div class="qr-widget">
                <div class="qr-widget-header">
                    <h3><span class="dashicons dashicons-chart-line"></span> Aktywność (ostatnie 7 dni)</h3>
                </div>
                <div class="qr-widget-content">
                    <canvas id="qr-activity-chart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- System info -->
            <div class="qr-widget">
                <div class="qr-widget-header">
                    <h3><span class="dashicons dashicons-info"></span> Informacje systemowe</h3>
                </div>
                <div class="qr-widget-content">
                    <div class="qr-system-info">
                        <div class="qr-info-item">
                            <span class="qr-info-label">Wersja wtyczki:</span>
                            <span class="qr-info-value"><?php echo QR_SYSTEM_VERSION; ?></span>
                        </div>
                        <div class="qr-info-item">
                            <span class="qr-info-label">Strona skanowania:</span>
                            <span class="qr-info-value">
                                <a href="<?php echo home_url('/skanowanie/'); ?>" target="_blank">
                                    <?php echo home_url('/skanowanie/'); ?>
                                </a>
                            </span>
                        </div>
                        <div class="qr-info-item">
                            <span class="qr-info-label">Status bazy danych:</span>
                            <span class="qr-info-value qr-status-ok">
                                <span class="dashicons dashicons-yes-alt"></span> Połączono
                            </span>
                        </div>
                        <div class="qr-info-item">
                            <span class="qr-info-label">Ostatnia aktywność:</span>
                            <span class="qr-info-value">
                                <?php 
                                $last_scan = $this->wpdb->get_var("SELECT scan_time FROM {$this->table_scans} ORDER BY scan_time DESC LIMIT 1");
                                if ($last_scan) {
                                    echo human_time_diff(strtotime($last_scan), time()) . ' temu';
                                } else {
                                    echo 'Brak aktywności';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Szybkie akcje -->
            <div class="qr-widget">
                <div class="qr-widget-header">
                    <h3><span class="dashicons dashicons-admin-tools"></span> Szybkie akcje</h3>
                </div>
                <div class="qr-widget-content">
                    <div class="qr-quick-actions-grid">
                        <a href="<?php echo admin_url('admin.php?page=qr-system-codes&action=add'); ?>" class="qr-quick-action">
                            <span class="dashicons dashicons-plus"></span>
                            <span>Dodaj kod</span>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=qr-system-groups&action=add'); ?>" class="qr-quick-action">
                            <span class="dashicons dashicons-category"></span>
                            <span>Nowa grupa</span>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=qr-system-scans'); ?>" class="qr-quick-action">
                            <span class="dashicons dashicons-download"></span>
                            <span>Eksport skanów</span>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=qr-system-settings'); ?>" class="qr-quick-action">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <span>Ustawienia</span>
                        </a>
                        <a href="<?php echo admin_url('users.php'); ?>" class="qr-quick-action">
                            <span class="dashicons dashicons-groups"></span>
                            <span>Użytkownicy</span>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=qr-system-stats'); ?>" class="qr-quick-action">
                            <span class="dashicons dashicons-chart-area"></span>
                            <span>Statystyki</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wykres aktywności
    var ctx = document.getElementById('qr-activity-chart');
    if (ctx) {
        ctx = ctx.getContext('2d');
        
        // Dane dla ostatnich 7 dni
        var dates = [];
        var scans = [];
        
        for (var i = 6; i >= 0; i--) {
            var date = new Date();
            date.setDate(date.getDate() - i);
            dates.push(date.toLocaleDateString('pl-PL', { month: 'short', day: 'numeric' }));
            
            // Symulowane dane - w rzeczywistej implementacji pobrać z AJAX
            scans.push(Math.floor(Math.random() * 50) + 10);
        }
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Skany',
                    data: scans,
                    borderColor: '#007cba',
                    backgroundColor: 'rgba(0, 124, 186, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f0f0f1'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });
    }
});
</script>