<?php
/**
 * Plugin Name: QR-System Professional
 * Plugin URI: https://szymonsarnecki.pl/
 * Description: Profesjonalny system kodów QR dla franczyz. Zarządzanie kodami rabatowymi, skanowanie i zaawansowane statystyki.
 * Version: 1.0.1
 * Author: Szymon Sarnecki
 * Author URI: https://szymonsarnecki.pl/
 * License: GPL v2 or later
 * Text Domain: qr-system
 * Domain Path: /languages
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('ABSPATH')) {
    exit;
}

// Definicje stałych
define('QR_SYSTEM_VERSION', '1.0.1');
define('QR_SYSTEM_PLUGIN_FILE', __FILE__);
define('QR_SYSTEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QR_SYSTEM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Sprawdzenie wersji PHP
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>QR-System:</strong> Ta wtyczka wymaga PHP 7.4 lub nowszej wersji. ';
        echo 'Aktualna wersja: ' . PHP_VERSION;
        echo '</p></div>';
    });
    return;
}

/**
 * Główna klasa wtyczki
 */
class QR_System_Professional {
    
    private static $instance = null;
    private $wpdb;
    private $table_codes;
    private $table_scans;
    private $table_groups;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_codes = $wpdb->prefix . 'qr_codes';
        $this->table_scans = $wpdb->prefix . 'qr_scans';
        $this->table_groups = $wpdb->prefix . 'qr_groups';
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_qr_add_code', array($this, 'ajax_add_code'));
        add_action('wp_ajax_qr_scan_code', array($this, 'ajax_scan_code'));
        add_action('wp_ajax_nopriv_qr_scan_code', array($this, 'ajax_scan_code'));
        add_action('wp_ajax_qr_delete_code', array($this, 'ajax_delete_code'));
        add_action('wp_ajax_qr_add_group', array($this, 'ajax_add_group'));
        add_action('wp_ajax_qr_export_data', array($this, 'ajax_export_data'));
        add_action('wp_ajax_qr_refresh_tables', array($this, 'ajax_refresh_tables'));
        add_action('wp_ajax_qr_clean_old_data', array($this, 'ajax_clean_old_data'));
        add_action('wp_ajax_qr_full_export', array($this, 'ajax_full_export'));
        
        // Legacy AJAX handlers (backwards compatibility)
        add_action('wp_ajax_sprawdz_kod_qr', array($this, 'legacy_ajax_scan_qr'));
        add_action('wp_ajax_nopriv_sprawdz_kod_qr', array($this, 'legacy_ajax_scan_qr'));
        add_action('wp_ajax_sprawdz_kod_manual', array($this, 'legacy_ajax_scan_manual'));
        add_action('wp_ajax_nopriv_sprawdz_kod_manual', array($this, 'legacy_ajax_scan_manual'));
        
        // Shortcodes
        add_shortcode('qr_scanner', array($this, 'scanner_shortcode'));
        add_shortcode('qr_user_locations', array($this, 'user_locations_shortcode'));
    }
    
    public function activate() {
        $this->create_tables();
        $this->set_default_options();
        $this->create_scanner_page();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        wp_cache_flush();
    }
    
    private function create_tables() {
        $charset_collate = $this->wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Usuń istniejące tabele jeśli istnieją (dla pełnego reset)
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->table_scans}");
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->table_codes}");
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->table_groups}");
        
        // Tabela grup (najpierw, bo inne tabele się do niej odnoszą)
        $sql_groups = "CREATE TABLE {$this->table_groups} (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            description text,
            color varchar(7) DEFAULT '#007cba',
            created_by int(11) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY name_idx (name)
        ) $charset_collate;";
        
        // Tabela kodów QR
        $sql_codes = "CREATE TABLE {$this->table_codes} (
            id int(11) NOT NULL AUTO_INCREMENT,
            code varchar(50) NOT NULL,
            type enum('normal', 'special') DEFAULT 'normal',
            status enum('active', 'inactive', 'used') DEFAULT 'active',
            is_active tinyint(1) DEFAULT 1,
            message text,
            expiry_date datetime NULL,
            max_uses int(11) DEFAULT 1,
            current_uses int(11) DEFAULT 0,
            group_id int(11) DEFAULT NULL,
            created_by int(11) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code_unique (code),
            KEY status_idx (status),
            KEY is_active_idx (is_active),
            KEY group_idx (group_id),
            KEY created_by_idx (created_by),
            KEY expiry_idx (expiry_date)
        ) $charset_collate;";
        
        // Tabela skanów
        $sql_scans = "CREATE TABLE {$this->table_scans} (
            id int(11) NOT NULL AUTO_INCREMENT,
            code varchar(50) NOT NULL,
            user_id int(11) NOT NULL,
            location varchar(255),
            scan_time datetime DEFAULT CURRENT_TIMESTAMP,
            ip_address varchar(45),
            user_agent text,
            confirmed tinyint(1) DEFAULT 1,
            scan_result enum('success', 'error', 'expired', 'used', 'special') DEFAULT 'success',
            error_message text,
            PRIMARY KEY (id),
            KEY code_idx (code),
            KEY user_idx (user_id),
            KEY scan_time_idx (scan_time),
            KEY result_idx (scan_result),
            KEY location_idx (location)
        ) $charset_collate;";
        
        // Stwórz tabele w odpowiedniej kolejności
        $result1 = $this->wpdb->query($sql_groups);
        $result2 = $this->wpdb->query($sql_codes);
        $result3 = $this->wpdb->query($sql_scans);
        
        // Log błędów jeśli wystąpią
        if (!$result1 || !$result2 || !$result3) {
            error_log('QR System: Błąd tworzenia tabel. Groups: ' . ($result1 ? 'OK' : 'BŁĄD') . 
                     ', Codes: ' . ($result2 ? 'OK' : 'BŁĄD') . 
                     ', Scans: ' . ($result3 ? 'OK' : 'BŁĄD'));
        }
        
        // Dodaj domyślne grupy
        $this->insert_default_groups();
        
        // Dodaj przykładowe kody dla testów
        $this->insert_sample_codes();
    }
    
    private function insert_default_groups() {
        $default_groups = array(
            array('name' => 'Rabaty ogólne', 'description' => 'Kody rabatowe ogólnego przeznaczenia', 'color' => '#007cba'),
            array('name' => 'Promocje specjalne', 'description' => 'Limitowane promocje czasowe', 'color' => '#e74c3c'),
            array('name' => 'Kody lojalnościowe', 'description' => 'Nagrody dla stałych klientów', 'color' => '#f39c12'),
            array('name' => 'Kody testowe', 'description' => 'Kody do testowania systemu', 'color' => '#95a5a6')
        );
        
        foreach ($default_groups as $group) {
            $this->wpdb->insert(
                $this->table_groups,
                array(
                    'name' => $group['name'],
                    'description' => $group['description'],
                    'color' => $group['color'],
                    'created_by' => 1
                ),
                array('%s', '%s', '%s', '%d')
            );
        }
    }
    
    private function insert_sample_codes() {
        // Pobierz ID pierwszej grupy dla przykładów
        $group_id = $this->wpdb->get_var("SELECT id FROM {$this->table_groups} LIMIT 1");
        
        $sample_codes = array(
            array(
                'code' => 'TESTQR001',
                'type' => 'normal',
                'message' => 'Rabat 10% na pierwsze zamówienie',
                'expiry_date' => date('Y-m-d H:i:s', strtotime('+30 days')),
                'max_uses' => 100,
                'group_id' => $group_id
            ),
            array(
                'code' => 'PROMO50',
                'type' => 'normal', 
                'message' => 'Rabat 50% na tortillę MAX',
                'expiry_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'max_uses' => 1,
                'group_id' => $group_id
            ),
            array(
                'code' => 'SPECIALVIP',
                'type' => 'special',
                'message' => 'Kod specjalny tylko dla uprzywilejowanych',
                'expiry_date' => date('Y-m-d H:i:s', strtotime('+60 days')),
                'max_uses' => 50,
                'group_id' => $group_id
            )
        );
        
        foreach ($sample_codes as $code_data) {
            $this->wpdb->insert(
                $this->table_codes,
                array(
                    'code' => $code_data['code'],
                    'type' => $code_data['type'],
                    'status' => 'active',
                    'is_active' => 1,
                    'message' => $code_data['message'],
                    'expiry_date' => $code_data['expiry_date'],
                    'max_uses' => $code_data['max_uses'],
                    'current_uses' => 0,
                    'group_id' => $code_data['group_id'],
                    'created_by' => 1
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%d')
            );
        }
    }
    
    private function create_scanner_page() {
        // Sprawdź czy strona już istnieje
        $existing_page = get_page_by_path('skanowanie');
        if (!$existing_page) {
            $page_data = array(
                'post_title' => 'Skanowanie kodów QR',
                'post_content' => '[qr_scanner]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'skanowanie'
            );
            wp_insert_post($page_data);
        }
    }
    
    private function set_default_options() {
        add_option('qr_system_settings', array(
            'require_confirmation' => true,
            'allow_multiple_scans' => false,
            'enable_logging' => true,
            'auto_archive_days' => 30,
            'max_codes_per_group' => 1000,
            'enable_notifications' => true
        ));
    }
    
    public function init() {
        load_plugin_textdomain('qr-system', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function admin_menu() {
        // Główne menu
        add_menu_page(
            'QR-System Professional',
            'QR-System Pro', 
            'manage_options',
            'qr-system-pro',
            array($this, 'admin_dashboard'),
            'dashicons-qrcode',
            30
        );
        
        // Dashboard (duplikat głównego menu)
        add_submenu_page(
            'qr-system-pro',
            'Dashboard',
            'Dashboard',
            'manage_options', 
            'qr-system-pro',
            array($this, 'admin_dashboard')
        );
        
        // Kody QR
        add_submenu_page(
            'qr-system-pro',
            'Kody QR',
            'Kody QR',
            'manage_options', 
            'qr-system-codes',
            array($this, 'admin_codes')
        );
        
        // Grupy
        add_submenu_page(
            'qr-system-pro',
            'Grupy kodów',
            'Grupy',
            'manage_options',
            'qr-system-groups', 
            array($this, 'admin_groups')
        );
        
        // Skany
        add_submenu_page(
            'qr-system-pro',
            'Historia skanów',
            'Skany',
            'manage_options',
            'qr-system-scans', 
            array($this, 'admin_scans')
        );
        
        // Statystyki
        add_submenu_page(
            'qr-system-pro',
            'Statystyki',
            'Statystyki',
            'manage_options',
            'qr-system-stats',
            array($this, 'admin_stats')
        );
        
        // Ustawienia
        add_submenu_page(
            'qr-system-pro',
            'Ustawienia',
            'Ustawienia',
            'manage_options',
            'qr-system-settings',
            array($this, 'admin_settings')
        );
    }
    
    public function admin_scripts($hook) {
        if (strpos($hook, 'qr-system') === false) return;
        
        // Podstawowe skrypty
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-datepicker');
        
        // Chart.js dla wykresów
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', array(), '3.9.1', true);
        
        // Własne skrypty
        wp_enqueue_script('qr-admin', QR_SYSTEM_PLUGIN_URL . 'assets/admin.js', array('jquery', 'chartjs'), QR_SYSTEM_VERSION, true);
        wp_enqueue_style('qr-admin', QR_SYSTEM_PLUGIN_URL . 'assets/admin.css', array(), QR_SYSTEM_VERSION);
        
        // jQuery UI CSS
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.13.1/themes/ui-lightness/jquery-ui.css');
        
        wp_localize_script('qr-admin', 'qr_ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('qr_nonce'),
            'messages' => array(
                'confirm_delete' => 'Czy na pewno chcesz usunąć ten element?',
                'code_added' => 'Kod został dodany pomyślnie',
                'code_deleted' => 'Kod został usunięty',
                'group_added' => 'Grupa została dodana',
                'export_started' => 'Eksport rozpoczęty...',
                'error' => 'Wystąpił błąd'
            )
        ));
    }
    
    public function frontend_scripts() {
        if (is_page('skanowanie') || (is_page() && has_shortcode(get_post()->post_content, 'qr_scanner'))) {
            wp_enqueue_script('html5-qrcode', QR_SYSTEM_PLUGIN_URL . 'assets/html5-qrcode.min.js', array(), '2.3.8', true);
            wp_enqueue_script('qr-scanner', QR_SYSTEM_PLUGIN_URL . 'assets/scanner.js', array('jquery'), QR_SYSTEM_VERSION, true);
            wp_enqueue_style('qr-scanner', QR_SYSTEM_PLUGIN_URL . 'assets/scanner.css', array(), QR_SYSTEM_VERSION);
            
            wp_localize_script('qr-scanner', 'qr_scanner', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('qr_scanner_nonce'),
                'messages' => array(
                    'camera_error' => 'Błąd dostępu do kamery',
                    'code_success' => 'Kod zeskanowany pomyślnie!',
                    'code_error' => 'Błąd skanowania kodu',
                    'scanning' => 'Skanowanie...'
                )
            ));
        }
    }
    
    // === ADMIN PAGES ===
    
    public function admin_dashboard() {
        $stats = $this->get_dashboard_stats();
        $recent_scans = $this->get_recent_scans(10);
        $groups = $this->get_groups();
        include QR_SYSTEM_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }
    
    public function admin_codes() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        
        if ($action === 'add' && isset($_POST['submit'])) {
            $this->handle_add_code();
        } elseif ($action === 'edit' && isset($_POST['submit'])) {
            $this->handle_edit_code();
        } elseif ($action === 'import' && isset($_POST['import_csv'])) {
            $this->handle_import_csv();
        } elseif ($action === 'delete' && isset($_GET['id'])) {
            $this->handle_delete_code();
        }
        
        $codes = $this->get_codes();
        $groups = $this->get_groups();
        include QR_SYSTEM_PLUGIN_DIR . 'templates/admin-codes.php';
    }
    
    public function admin_groups() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        
        if ($action === 'add' && isset($_POST['submit'])) {
            $this->handle_add_group();
        } elseif ($action === 'delete' && isset($_GET['id'])) {
            $this->handle_delete_group();
        }
        
        $groups = $this->get_groups_with_stats();
        include QR_SYSTEM_PLUGIN_DIR . 'templates/admin-groups.php';
    }
    
    public function admin_scans() {
        $scans = $this->get_scans();
        $stats = $this->get_scan_stats();
        include QR_SYSTEM_PLUGIN_DIR . 'templates/admin-scans.php';
    }
    
    public function get_scan_stats() {
        $today = date('Y-m-d');
        
        $total_scans = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_scans}");
        $success_scans = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_scans} WHERE scan_result = 'success'");
        $error_scans = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_scans} WHERE scan_result != 'success'");
        $today_scans = (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_scans} WHERE DATE(scan_time) = %s", $today
        ));
        
        $success_rate = $total_scans > 0 ? round(($success_scans / $total_scans) * 100, 1) : 0;
        
        return array(
            'total_scans' => $total_scans,
            'success_scans' => $success_scans,
            'error_scans' => $error_scans,
            'today_scans' => $today_scans,
            'success_rate' => $success_rate
        );
    }
    
    public function admin_stats() {
        $stats = $this->get_detailed_stats();
        include QR_SYSTEM_PLUGIN_DIR . 'templates/admin-stats.php';
    }
    
    private function get_detailed_stats() {
        $today = date('Y-m-d');
        $week_ago = date('Y-m-d', strtotime('-7 days'));
        
        $total_codes = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_codes}");
        $active_codes = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_codes} WHERE status = 'active' AND is_active = 1");
        $total_scans = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_scans}");
        $successful_scans = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_scans} WHERE scan_result = 'success'");
        $today_scans = (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_scans} WHERE DATE(scan_time) = %s", $today
        ));
        $today_success = (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_scans} WHERE DATE(scan_time) = %s AND scan_result = 'success'", $today
        ));
        $week_scans = (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_scans} WHERE DATE(scan_time) >= %s", $week_ago
        ));
        
        $success_rate = $total_scans > 0 ? round(($successful_scans / $total_scans) * 100, 1) : 0;
        
        return array(
            'total_codes' => $total_codes,
            'active_codes' => $active_codes,
            'total_scans' => $total_scans,
            'successful_scans' => $successful_scans,
            'today_scans' => $today_scans,
            'today_success' => $today_success,
            'week_scans' => $week_scans,
            'success_rate' => $success_rate
        );
    }
    
    public function admin_settings() {
        if (isset($_POST['submit'])) {
            $this->handle_save_settings();
        }
        
        $settings = get_option('qr_system_settings', array());
        include QR_SYSTEM_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    // === AJAX HANDLERS ===
    
    public function ajax_scan_code() {
        check_ajax_referer('qr_scanner_nonce', 'nonce');
        
        $code = sanitize_text_field($_POST['code']);
        $location = sanitize_text_field($_POST['location']);
        
        if (empty($code)) {
            wp_send_json_error('Brak kodu');
        }
        
        // Znajdź kod w bazie
        $qr_code = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_codes} WHERE code = %s", $code
        ));
        
        if (!$qr_code) {
            $this->log_scan($code, 'error', 'Kod nie istnieje');
            wp_send_json_error('Kod nie istnieje');
        }
        
        // Sprawdź status
        if ($qr_code->status !== 'active') {
            $this->log_scan($code, 'error', 'Kod nieaktywny');
            wp_send_json_error('Kod nieaktywny');
        }
        
        // Sprawdź datę ważności
        if ($qr_code->expiry_date && strtotime($qr_code->expiry_date) < time()) {
            $this->log_scan($code, 'expired', 'Kod wygasł');
            wp_send_json_error('Kod wygasł');
        }
        
        // Sprawdź limity użycia
        if ($qr_code->max_uses > 0 && $qr_code->current_uses >= $qr_code->max_uses) {
            $this->log_scan($code, 'used', 'Kod został już wykorzystany');
            wp_send_json_error('Kod został już wykorzystany');
        }
        
        // Sprawdź czy specjalny kod i czy user ma uprawnienia
        if ($qr_code->type === 'special') {
            $allowed_emails = array(
                'tomasz.szulik@sweetgallery.pl',
                'kontakt@sarnetski.pl'
            );
            
            $current_user = wp_get_current_user();
            if (!in_array($current_user->user_email, $allowed_emails)) {
                $this->log_scan($code, 'error', 'Brak uprawnień do tego kodu');
                wp_send_json_error('Brak uprawnień do tego kodu');
            }
        }
        
        // Zapisz pomyślny skan
        $this->log_scan($code, 'success', 'Kod zeskanowany pomyślnie', $location);
        
        // Zwiększ licznik użyć
        $this->wpdb->update(
            $this->table_codes,
            array('current_uses' => $qr_code->current_uses + 1),
            array('id' => $qr_code->id),
            array('%d'),
            array('%d')
        );
        
        // Jeśli osiągnięto limit, dezaktywuj
        if ($qr_code->max_uses > 0 && ($qr_code->current_uses + 1) >= $qr_code->max_uses) {
            $this->wpdb->update(
                $this->table_codes,
                array('status' => 'used'),
                array('id' => $qr_code->id),
                array('%s'),
                array('%d')
            );
        }
        
        wp_send_json_success(array(
            'message' => $qr_code->message ?: 'Kod zeskanowany pomyślnie',
            'code' => $code,
            'type' => $qr_code->type
        ));
    }
    
    private function log_scan($code, $result, $message = '', $location = '') {
        $this->wpdb->insert(
            $this->table_scans,
            array(
                'code' => $code,
                'user_id' => get_current_user_id(),
                'location' => $location,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'scan_result' => $result,
                'error_message' => $message,
                'confirmed' => 1
            ),
            array('%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d')
        );
    }
    
    // === LEGACY COMPATIBILITY ===
    
    public function legacy_ajax_scan_qr() {
        $kod_qr = sanitize_text_field($_POST['kodQR']);
        $current_user = wp_get_current_user();
        $current_date = current_time('mysql');
        $location = sanitize_text_field($_POST['lokalizacja']);

        // Lista uprzywilejowanych e-maili
        $allowed_emails = array(
            'tomasz.szulik@sweetgallery.pl',
            'kontakt@sarnetski.pl',
        );

        if (empty($kod_qr)) {
            echo 'false';
            wp_die();
        }

        // Sprawdź w nowym systemie
        $qr_code = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_codes} WHERE code = %s", $kod_qr
        ));

        if ($qr_code) {
            // Znaleziono w nowym systemie
            if ($qr_code->type === 'special' && !in_array($current_user->user_email, $allowed_emails)) {
                echo 'special';
                wp_die();
            }

            if ($qr_code->expiry_date && strtotime($qr_code->expiry_date) < time()) {
                echo 'expired';
                wp_die();
            }

            if ($qr_code->status !== 'active' || $qr_code->is_active != 1) {
                echo 'false';
                wp_die();
            }

            if ($qr_code->max_uses > 0 && $qr_code->current_uses >= $qr_code->max_uses) {
                echo 'false';
                wp_die();
            }

            // Aktualizuj użycia
            $this->wpdb->update(
                $this->table_codes,
                array('current_uses' => $qr_code->current_uses + 1),
                array('id' => $qr_code->id),
                array('%d'),
                array('%d')
            );

            // Zapisz skan
            $this->log_scan($kod_qr, 'success', 'Legacy skan pomyślny', $location);

            // Zwróć customową wiadomość lub standardowy status
            if (!empty($qr_code->message)) {
                echo 'success:' . $qr_code->message;
            } else {
                echo 'true';
            }
            wp_die();
        }

        // Legacy fallback - sprawdź w starych post_types
        $posts_manual = get_posts(array(
            'post_type'   => 'kody-rabatowem',
            'meta_key'    => 'kod_alfanumeryczny1',
            'meta_value'  => $kod_qr,
            'numberposts' => 1,
        ));

        if (!empty($posts_manual)) {
            $post_obj = $posts_manual[0];

            if (!in_array($current_user->user_email, $allowed_emails)) {
                echo 'special';
                wp_die();
            }

            $expiry_date = get_post_meta($post_obj->ID, 'data_waznosci1', true);
            if ($expiry_date) {
                $expiry_full = $expiry_date . " 23:59:59";
                if (strtotime($current_date) > strtotime($expiry_full)) {
                    echo 'expired';
                    wp_die();
                }
            }

            echo 'true';

            // Utwórz wpis w starym systemie
            $postarr = array(
                'post_title'  => 'Zeskanowane (m) ' . $kod_qr,
                'post_type'   => 'zeskanowanem',
                'post_status' => 'publish',
                'meta_input'  => array(
                    'kod_alfanumeryczny2' => $kod_qr,
                    'data_waznosci2'      => $expiry_date,
                    'data_skanowania2'    => $current_date,
                    'imie_i_nazwisko2'    => $current_user->first_name . ' ' . $current_user->last_name,
                    'adres_punktu2'       => $location,
                    'wykorzystany2'       => 'true',
                )
            );
            wp_insert_post($postarr);
            wp_die();
        }

        // Sprawdź w normalnych kodach
        $posts_normal = get_posts(array(
            'post_type'   => 'kody-rabatowe',
            'meta_key'    => 'kod-alfanumeryczny-',
            'meta_value'  => $kod_qr,
            'numberposts' => 1,
        ));

        if (!empty($posts_normal)) {
            $post_obj = $posts_normal[0];

            $expiry_date = get_post_meta($post_obj->ID, 'data-waznosci', true);
            if ($expiry_date) {
                $expiry_full = $expiry_date . " 23:59:59";
                if (strtotime($current_date) > strtotime($expiry_full)) {
                    echo 'expired';
                    wp_die();
                }
            }

            $is_used = get_post_meta($post_obj->ID, 'wykorzystany', true);
            if ($is_used === 'true') {
                echo 'false';
                wp_die();
            }

            update_post_meta($post_obj->ID, 'wykorzystany', 'true');
            echo 'true';

            $postarr = array(
                'post_title'  => 'Zeskanowane ' . $kod_qr,
                'post_type'   => 'zeskanowane',
                'post_status' => 'publish',
                'meta_input'  => array(
                    'kod-alfanumeryczny-' => $kod_qr,
                    'data-waznosci'       => get_post_meta($post_obj->ID, 'data-waznosci', true),
                    'data-skanowania'     => $current_date,
                    'imie-i-nazwisko'     => $current_user->first_name . ' ' . $current_user->last_name,
                    'adres-punktu'        => $location,
                    'wykorzystany'        => 'true',
                )
            );
            wp_insert_post($postarr);
            wp_die();
        }

        echo 'false';
        wp_die();
    }

    public function legacy_ajax_scan_manual() {
        $kod_manual = sanitize_text_field($_POST['kodManual']);
        $current_user = wp_get_current_user();
        $current_date = current_time('mysql');
        $location = sanitize_text_field($_POST['lokalizacja']);

        $allowed_emails = array(
            'tomasz.szulik@sweetgallery.pl',
            'kontakt@sarnetski.pl',
        );

        // Przekieruj to samo co skan QR
        $_POST['kodQR'] = $kod_manual;
        $this->legacy_ajax_scan_qr();
    }
    
    // === NEW AJAX HANDLERS ===
    
    public function ajax_refresh_tables() {
        check_ajax_referer('qr_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        // Odśwież tabele
        $this->create_tables();
        
        wp_send_json_success(array(
            'message' => 'Tabele zostały odświeżone'
        ));
    }
    
    public function ajax_clean_old_data() {
        check_ajax_referer('qr_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        // Usuń skany starsze niż 90 dni
        $deleted = $this->wpdb->query(
            "DELETE FROM {$this->table_scans} WHERE scan_time < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );
        
        wp_send_json_success(array(
            'deleted_count' => $deleted,
            'message' => "Usunięto $deleted starych skanów"
        ));
    }
    
    public function ajax_full_export() {
        check_ajax_referer('qr_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        // Proste rozwiązanie - przekieruj do CSV
        $export_url = admin_url('admin.php?page=qr-system-scans&export=1');
        
        wp_send_json_success(array(
            'download_url' => $export_url,
            'message' => 'Eksport rozpoczęty'
        ));
    }
    
    // === SHORTCODES ===
    
    public function scanner_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<div class="qr-login-notice"><p>Musisz być zalogowany aby skanować kody QR.</p><p><a href="' . wp_login_url(get_permalink()) . '" class="button">Zaloguj się</a></p></div>';
        }
        
        ob_start();
        include QR_SYSTEM_PLUGIN_DIR . 'templates/scanner.php';
        return ob_get_clean();
    }
    
    public function user_locations_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '';
        }
        
        $user_id = get_current_user_id();
        $locations = array();
        
        // Pobierz lokalizacje (kompatybilność z legacy systemem)
        $location_keys = array('adres-punktu', 'adres-punktu2', 'adres-punktu3', 'adres-punktu4', 'adres-punktu5');
        
        foreach ($location_keys as $key) {
            $location = get_user_meta($user_id, $key, true);
            if (!empty($location)) {
                $locations[] = $location;
            }
        }
        
        if (empty($locations)) {
            return '<div class="qr-no-locations"><p>Brak skonfigurowanych lokalizacji. Skontaktuj się z administratorem.</p></div>';
        }
        
        $output = '<select id="lokalizacjaSelect" class="qr-location-select">';
        $output .= '<option value="">Wybierz lokalizację</option>';
        foreach ($locations as $location) {
            $output .= '<option value="' . esc_attr($location) . '">' . esc_html($location) . '</option>';
        }
        $output .= '</select>';
        
        return $output;
    }
    
    // === HELPER METHODS ===
    
    private function get_dashboard_stats() {
        $total_codes = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_codes}");
        $active_codes = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_codes} WHERE status = 'active'");
        $total_scans = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_scans}");
        $today_scans = (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_scans} WHERE DATE(scan_time) = %s",
            date('Y-m-d')
        ));
        $success_rate = $total_scans > 0 ? round(($this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_scans} WHERE scan_result = 'success'") / $total_scans) * 100, 1) : 0;
        
        return array(
            'total_codes' => $total_codes,
            'active_codes' => $active_codes,
            'total_scans' => $total_scans,
            'today_scans' => $today_scans,
            'success_rate' => $success_rate
        );
    }
    
    private function get_codes($limit = 50) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT c.*, g.name as group_name, g.color as group_color 
             FROM {$this->table_codes} c 
             LEFT JOIN {$this->table_groups} g ON c.group_id = g.id 
             ORDER BY c.created_at DESC 
             LIMIT %d", $limit
        ));
    }
    
    private function get_scans($limit = 50) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT s.*, u.display_name as user_name, c.message as code_message
             FROM {$this->table_scans} s 
             LEFT JOIN {$this->wpdb->users} u ON s.user_id = u.ID 
             LEFT JOIN {$this->table_codes} c ON s.code = c.code
             ORDER BY s.scan_time DESC 
             LIMIT %d", $limit
        ));
    }
    
    private function get_recent_scans($limit = 5) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT s.*, u.display_name as user_name
             FROM {$this->table_scans} s 
             LEFT JOIN {$this->wpdb->users} u ON s.user_id = u.ID 
             WHERE s.scan_result = 'success'
             ORDER BY s.scan_time DESC 
             LIMIT %d", $limit
        ));
    }
    
    private function get_groups() {
        return $this->wpdb->get_results("SELECT * FROM {$this->table_groups} ORDER BY name ASC");
    }
    
    private function get_groups_with_stats() {
        return $this->wpdb->get_results(
            "SELECT g.*, 
                    COUNT(c.id) as codes_count,
                    COUNT(CASE WHEN c.status = 'active' THEN 1 END) as active_codes
             FROM {$this->table_groups} g 
             LEFT JOIN {$this->table_codes} c ON g.id = c.group_id
             GROUP BY g.id 
             ORDER BY g.name ASC"
        );
    }
    
    private function handle_add_code() {
        if (!wp_verify_nonce($_POST['qr_nonce'], 'qr_add_code')) {
            wp_die('Błąd bezpieczeństwa');
        }
        
        $code = sanitize_text_field($_POST['code']);
        $type = sanitize_text_field($_POST['type']);
        $message = sanitize_textarea_field($_POST['message']);
        $expiry = sanitize_text_field($_POST['expiry_date']);
        $max_uses = intval($_POST['max_uses']);
        $group_id = intval($_POST['group_id']);
        
        if (empty($code)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Kod jest wymagany!</p></div>';
            });
            return;
        }
        
        // Sprawdź czy kod już istnieje
        $exists = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->table_codes} WHERE code = %s", $code
        ));
        
        if ($exists) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Kod już istnieje!</p></div>';
            });
            return;
        }
        
        $result = $this->wpdb->insert(
            $this->table_codes,
            array(
                'code' => $code,
                'type' => $type,
                'message' => $message,
                'expiry_date' => $expiry ? $expiry : null,
                'max_uses' => $max_uses > 0 ? $max_uses : 1,
                'group_id' => $group_id > 0 ? $group_id : null,
                'created_by' => get_current_user_id()
            ),
            array('%s', '%s', '%s', '%s', '%d', '%d', '%d')
        );
        
        if ($result) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Kod został dodany pomyślnie!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Błąd podczas dodawania kodu!</p></div>';
            });
        }
    }
    
    private function handle_add_group() {
        if (!wp_verify_nonce($_POST['qr_group_nonce'], 'qr_add_group')) {
            wp_die('Błąd bezpieczeństwa');
        }
        
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $color = sanitize_hex_color($_POST['color']);
        
        if (empty($name)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Nazwa grupy jest wymagana!</p></div>';
            });
            return;
        }
        
        $result = $this->wpdb->insert(
            $this->table_groups,
            array(
                'name' => $name,
                'description' => $description,
                'color' => $color ?: '#007cba',
                'created_by' => get_current_user_id()
            ),
            array('%s', '%s', '%s', '%d')
        );
        
        if ($result) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Grupa została dodana pomyślnie!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Błąd podczas dodawania grupy!</p></div>';
            });
        }
    }
    
    private function handle_save_settings() {
        if (!wp_verify_nonce($_POST['qr_settings_nonce'], 'qr_save_settings')) {
            wp_die('Błąd bezpieczeństwa');
        }
        
        $settings = array(
            'require_confirmation' => isset($_POST['require_confirmation']),
            'allow_multiple_scans' => isset($_POST['allow_multiple_scans']),
            'enable_logging' => isset($_POST['enable_logging']),
            'auto_archive_days' => intval($_POST['auto_archive_days']),
            'max_codes_per_group' => intval($_POST['max_codes_per_group']),
            'enable_notifications' => isset($_POST['enable_notifications'])
        );
        
        update_option('qr_system_settings', $settings);
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>Ustawienia zostały zapisane!</p></div>';
        });
    }
    
    private function get_advanced_stats() {
        $stats = array();
        
        // Statystyki dzienne (ostatnie 30 dni)
        $daily_stats = $this->wpdb->get_results("
            SELECT DATE(scan_time) as date, COUNT(*) as count 
            FROM {$this->table_scans} 
            WHERE scan_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(scan_time) 
            ORDER BY date DESC
        ");
        
        // Statystyki według grup
        $group_stats = $this->wpdb->get_results("
            SELECT g.name, g.color, COUNT(s.id) as scan_count
            FROM {$this->table_groups} g
            LEFT JOIN {$this->table_codes} c ON g.id = c.group_id
            LEFT JOIN {$this->table_scans} s ON c.code = s.code AND s.scan_result = 'success'
            GROUP BY g.id
            ORDER BY scan_count DESC
        ");
        
        // Top użytkownicy
        $top_users = $this->wpdb->get_results("
            SELECT u.display_name, COUNT(s.id) as scan_count
            FROM {$this->wpdb->users} u
            JOIN {$this->table_scans} s ON u.ID = s.user_id
            WHERE s.scan_result = 'success'
            GROUP BY u.ID
            ORDER BY scan_count DESC
            LIMIT 10
        ");
        
        return array(
            'daily_stats' => $daily_stats,
            'group_stats' => $group_stats,
            'top_users' => $top_users
        );
    }
    
    private function handle_import_csv() {
        if (!wp_verify_nonce($_POST['qr_import_nonce'], 'qr_import_csv')) {
            wp_die('Nieprawidłowy token bezpieczeństwa');
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Błąd podczas przesyłania pliku CSV.</p></div>';
            });
            return;
        }
        
        $file = $_FILES['csv_file'];
        $group_id = intval($_POST['group_id']);
        $expiry_date = sanitize_text_field($_POST['expiry_date']);
        $max_uses = intval($_POST['max_uses']) ?: 1;
        $type = sanitize_text_field($_POST['type']);
        $message = sanitize_textarea_field($_POST['message']);
        
        // Sprawdź typ pliku
        $file_info = pathinfo($file['name']);
        if (strtolower($file_info['extension']) !== 'csv') {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Dozwolone są tylko pliki CSV.</p></div>';
            });
            return;
        }
        
        // Odczytaj plik CSV
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Nie można odczytać pliku CSV.</p></div>';
            });
            return;
        }
        
        $imported_count = 0;
        $skipped_count = 0;
        $line_number = 0;
        
        while (($line = fgets($handle)) !== false) {
            $line_number++;
            $code = trim($line);
            
            // Pomiń puste linie
            if (empty($code)) {
                continue;
            }
            
            // Sprawdź czy kod już istnieje
            $existing = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT id FROM {$this->table_codes} WHERE code = %s",
                $code
            ));
            
            if ($existing) {
                $skipped_count++;
                continue;
            }
            
            // Dodaj kod do bazy
            $result = $this->wpdb->insert(
                $this->table_codes,
                array(
                    'code' => $code,
                    'type' => $type,
                    'group_id' => $group_id ?: null,
                    'message' => $message,
                    'expiry_date' => $expiry_date ?: null,
                    'max_uses' => $max_uses,
                    'current_uses' => 0,
                    'is_active' => 1,
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%s')
            );
            
            if ($result) {
                $imported_count++;
            }
        }
        
        fclose($handle);
        
        // Dodaj notice z wynikami
        add_action('admin_notices', function() use ($imported_count, $skipped_count) {
            if ($imported_count > 0) {
                echo '<div class="notice notice-success"><p>';
                echo "Pomyślnie zaimportowano {$imported_count} kodów QR.";
                if ($skipped_count > 0) {
                    echo " Pominięto {$skipped_count} duplikatów.";
                }
                echo '</p></div>';
            } else {
                echo '<div class="notice notice-warning"><p>Nie zaimportowano żadnych kodów. Sprawdź plik CSV.</p></div>';
            }
        });
        
        // Przekieruj po imporcie
        wp_redirect(admin_url('admin.php?page=qr-system-codes'));
        exit;
    }
}

// Inicjalizacja wtyczki
function qr_system_professional_init() {
    return QR_System_Professional::get_instance();
}
add_action('plugins_loaded', 'qr_system_professional_init');

// Dodanie pól użytkownika dla lokalizacji
add_action('show_user_profile', 'qr_add_user_location_fields_pro');
add_action('edit_user_profile', 'qr_add_user_location_fields_pro');
add_action('personal_options_update', 'qr_save_user_location_fields_pro');
add_action('edit_user_profile_update', 'qr_save_user_location_fields_pro');

function qr_add_user_location_fields_pro($user) {
    ?>
    <h3>Lokalizacje QR-System Professional</h3>
    <table class="form-table">
        <?php for ($i = 1; $i <= 5; $i++): ?>
        <tr>
            <th><label for="adres-punktu<?php echo $i; ?>">Lokalizacja <?php echo $i; ?></label></th>
            <td>
                <input type="text" 
                       name="adres-punktu<?php echo $i; ?>" 
                       id="adres-punktu<?php echo $i; ?>" 
                       value="<?php echo esc_attr(get_user_meta($user->ID, "adres-punktu{$i}", true)); ?>" 
                       class="regular-text" 
                       placeholder="np. Warszawa - Centrum, ul. Marszałkowska 1" />
                <p class="description">Adres punktu sprzedaży</p>
            </td>
        </tr>
        <?php endfor; ?>
    </table>
    <?php
}

function qr_save_user_location_fields_pro($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    for ($i = 1; $i <= 5; $i++) {
        update_user_meta($user_id, "adres-punktu{$i}", sanitize_text_field($_POST["adres-punktu{$i}"]));
    }
}

// Wymuszenie logowania na frontend
add_action('template_redirect', function() {
    if (!is_user_logged_in() && !is_page('login') && !is_admin()) {
        $current_url = home_url(add_query_arg(array(), $GLOBALS['wp']->request));
        if (strpos($current_url, 'skanowanie') !== false) {
            auth_redirect();
        }
    }
});

// Przekierowanie po logowaniu
add_filter('login_redirect', function($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        return home_url('/skanowanie/');
    }
    return $redirect_to;
}, 10, 3);