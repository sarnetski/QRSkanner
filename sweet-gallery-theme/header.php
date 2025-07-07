<?php
/**
 * Header motywu Sweet Gallery QR Theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>
    
    <?php wp_head(); ?>
    
    <!-- HTML5 QR Code Library - wymagane dla kamery -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/html5-qrcode.min.js"></script>
    
    <!-- Dodatkowe style dla QR Scanner -->
    <style>
        /* QR Scanner integration */
        .qr-scanner-app .qr-scanner-header,
        .qr-scanner-app .qr-status-bar {
            display: none !important;
        }
        
        .qr-scanner-app {
            background: none !important;
            min-height: auto !important;
        }
    </style>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    
    <div class="site-container">
        <header class="site-header">
            <div class="header-content">
                <?php if (has_custom_logo()) : ?>
                    <div class="site-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else : ?>
                    <div class="site-logo">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="Sweet Gallery" />
                    </div>
                <?php endif; ?>
                
                <h1 class="site-title">
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                        <?php bloginfo('name'); ?>
                    </a>
                </h1>
                
                <?php
                $description = get_bloginfo('description', 'display');
                if ($description || is_customize_preview()) :
                ?>
                    <p class="site-description"><?php echo $description; ?></p>
                <?php endif; ?>
            </div>
        </header>

        <nav class="main-navigation">
            <div class="nav-content">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class' => 'nav-menu',
                    'container' => false,
                    'fallback_cb' => 'sweet_gallery_fallback_menu',
                ));
                ?>
            </div>
        </nav>
