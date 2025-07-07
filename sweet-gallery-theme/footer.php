<?php
/**
 * Footer motywu Sweet Gallery QR Theme
 */
?>

        <footer class="site-footer">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo.png" alt="Sweet Gallery" />
                </div>
                
                <div class="footer-info">
                    <h3>Sweet Gallery</h3>
                    <p>Franczyzy gastronomiczne</p>
                </div>
                
                <div class="footer-brands">
                    <span class="footer-brand">Bafra Kebab</span>
                    <span class="footer-brand">Lodolandia</span>
                    <span class="footer-brand">Kołacz na okrągło</span>
                </div>
                
                <div class="footer-links">
                    <a href="<?php echo home_url('/skanowanie/'); ?>">Skanowanie QR</a>
                    <a href="<?php echo home_url('/regulamin-promocji/'); ?>">Regulamin promocji</a>
                    <a href="<?php echo admin_url(); ?>">Panel administratora</a>
                </div>
                
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> Sweet Gallery. Wszystkie prawa zastrzeżone.</p>
                    <p>System QR dla franczyz gastronomicznych</p>
                </div>
            </div>
        </footer>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
