<?php
/**
 * Funkcje motywu Sweet Gallery QR Theme
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Konfiguracja motywu
 */
function sweet_gallery_setup() {
    // Wsparcie dla logo
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    // Wsparcie dla tytułu strony
    add_theme_support('title-tag');
    
    // Wsparcie dla miniaturek
    add_theme_support('post-thumbnails');
    
    // Wsparcie dla HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Rejestracja menu
    register_nav_menus(array(
        'primary' => 'Menu główne',
    ));
}
add_action('after_setup_theme', 'sweet_gallery_setup');

/**
 * Ładowanie stylów i skryptów
 */
function sweet_gallery_scripts() {
    // Style główne
    wp_enqueue_style('sweet-gallery-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // HTML5 QR Code tylko na stronie skanowania
    if (is_page('skanowanie')) {
        wp_enqueue_script('html5-qrcode', get_template_directory_uri() . '/assets/js/html5-qrcode.min.js', array(), '2.3.8', true);
    }
}
add_action('wp_enqueue_scripts', 'sweet_gallery_scripts');

/**
 * Fallback menu jeśli nie ustawiono
 */
function sweet_gallery_fallback_menu() {
    echo '<ul class="nav-menu">';
    echo '<li><a href="' . home_url('/') . '">Strona główna</a></li>';
    echo '<li><a href="' . home_url('/skanowanie/') . '">Skanowanie QR</a></li>';
    if (current_user_can('manage_options')) {
        echo '<li><a href="' . admin_url() . '">Panel administratora</a></li>';
    }
    echo '</ul>';
}

/**
 * Konfiguracja wyświetlania tytułu
 */
function sweet_gallery_wp_title($title, $sep) {
    global $page, $paged;

    if (is_feed()) {
        return $title;
    }

    // Dodaj nazwę strony
    $title .= get_bloginfo('name', 'display');

    // Dodaj opis strony dla strony głównej
    $site_description = get_bloginfo('description', 'display');
    if ($site_description && (is_home() || is_front_page())) {
        $title .= " $sep $site_description";
    }

    // Dodaj numer strony jeśli dotyczy
    if (($paged >= 2 || $page >= 2) && !is_404()) {
        $title .= " $sep " . sprintf('Strona %s', max($paged, $page));
    }

    return $title;
}
add_filter('wp_title', 'sweet_gallery_wp_title', 10, 2);

/**
 * Customowy excerpt
 */
function sweet_gallery_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'sweet_gallery_excerpt_more');

/**
 * Długość excerpt
 */
function sweet_gallery_excerpt_length($length) {
    return 40;
}
add_filter('excerpt_length', 'sweet_gallery_excerpt_length');

/**
 * Dodanie logo Sweet Gallery do customizera
 */
function sweet_gallery_customize_register($wp_customize) {
    $wp_customize->add_section('sweet_gallery_options', array(
        'title'    => 'Opcje Sweet Gallery',
        'priority' => 30,
    ));
    
    $wp_customize->add_setting('sweet_gallery_show_brands', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    
    $wp_customize->add_control('sweet_gallery_show_brands', array(
        'label'   => 'Pokaż marki w stopce',
        'section' => 'sweet_gallery_options',
        'type'    => 'checkbox',
    ));
}
add_action('customize_register', 'sweet_gallery_customize_register');

/**
 * Dodaj obsługę QR Scanner CSS
 */
function sweet_gallery_qr_scanner_styles() {
    if (is_page('skanowanie')) {
        ?>
        <style>
        /* Dodatkowe style dla integracji QR Scanner */
        .qr-scanner-app {
            background: none !important;
            min-height: auto !important;
            padding: 0 !important;
            font-family: inherit !important;
        }
        
        .qr-scanner-app .qr-scanner-header,
        .qr-scanner-app .qr-status-bar {
            display: none !important;
        }
        
        .qr-scanner-main {
            background: none !important;
            padding: 0 !important;
        }
        
        .qr-scanner-container {
            background: none !important;
        }
        
        /* Responsywność dla QR Scanner */
        @media (max-width: 768px) {
            .qr-scanner-wrapper {
                margin: 10px !important;
                padding: 15px !important;
            }
        }
        </style>
        <?php
    }
}
add_action('wp_head', 'sweet_gallery_qr_scanner_styles');

/**
 * Wymuś przekierowanie na HTTPS dla strony skanowania (wymagane dla kamery)
 */
function sweet_gallery_force_ssl_scanner() {
    if (is_page('skanowanie') && !is_ssl() && !is_admin()) {
        $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        wp_redirect($redirect_url, 301);
        exit();
    }
}
add_action('template_redirect', 'sweet_gallery_force_ssl_scanner');

/**
 * Dodaj meta tagi dla lepszego SEO
 */
function sweet_gallery_meta_tags() {
    if (is_page('skanowanie')) {
        echo '<meta name="description" content="System skanowania kodów QR dla franczyz Sweet Gallery - Bafra Kebab, Lodolandia, Kołacz na okrągło">';
        echo '<meta name="keywords" content="QR, kod QR, skanowanie, Sweet Gallery, Bafra Kebab, Lodolandia, rabat">';
        echo '<meta name="robots" content="noindex, nofollow">';
    }
}
add_action('wp_head', 'sweet_gallery_meta_tags');

/**
 * Wyczyść header z niepotrzebnych elementów na stronie skanowania
 */
function sweet_gallery_clean_scanner_head() {
    if (is_page('skanowanie')) {
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
    }
}
add_action('init', 'sweet_gallery_clean_scanner_head');
