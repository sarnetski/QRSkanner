<?php
/**
 * Template główny motywu Sweet Gallery QR Theme
 */

get_header(); ?>

<div class="main-content">
    <div class="content-area">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                
                <?php if (is_page('skanowanie')) : ?>
                    <!-- Specjalna obsługa strony skanowania -->
                    <div class="qr-scanner-wrapper">
                        <div class="qr-scanner-title">
                            <h1><?php the_title(); ?></h1>
                            <p>Sweet Gallery - System skanowania kodów QR</p>
                        </div>
                        <div class="qr-scanner-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                    
                <?php else : ?>
                    <!-- Standardowa strona -->
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <h1 class="entry-title"><?php the_title(); ?></h1>
                        </header>
                        
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endif; ?>
                
            <?php endwhile; ?>
            
        <?php else : ?>
            
            <div class="no-content">
                <h2>Brak treści</h2>
                <p>Przepraszamy, nie znaleziono żądanej treści.</p>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
