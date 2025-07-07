<?php
/**
 * Admin Groups Template - Professional
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('ABSPATH')) {
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
?>

<div class="wrap qr-admin-wrap">
    <?php if ($action === 'list'): ?>
        <div class="qr-header">
            <div class="qr-header-left">
                <h1><span class="dashicons dashicons-category"></span> Grupy kodów QR</h1>
                <p class="qr-subtitle">Organizuj kody QR w logiczne grupy dla lepszego zarządzania</p>
            </div>
            <div class="qr-header-right">
                <div class="qr-quick-actions">
                    <a href="<?php echo admin_url('admin.php?page=qr-system-groups&action=add'); ?>" class="button button-primary button-hero">
                        <span class="dashicons dashicons-plus"></span> Dodaj grupę
                    </a>
                </div>
            </div>
        </div>

        <div class="qr-groups-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $group): ?>
                    <div class="qr-group-card">
                        <div class="qr-group-header">
                            <div class="qr-group-color-indicator" style="background-color: <?php echo esc_attr($group->color); ?>"></div>
                            <h3><?php echo esc_html($group->name); ?></h3>
                            <div class="qr-group-actions">
                                <button type="button" class="qr-group-menu-btn" onclick="toggleGroupMenu(this)">
                                    <span class="dashicons dashicons-menu"></span>
                                </button>
                                <div class="qr-group-menu">
                                    <a href="<?php echo admin_url('admin.php?page=qr-system-groups&action=edit&id=' . $group->id); ?>">
                                        <span class="dashicons dashicons-edit"></span> Edytuj
                                    </a>
                                    <a href="javascript:void(0)" onclick="deleteGroup(<?php echo $group->id; ?>)">
                                        <span class="dashicons dashicons-trash"></span> Usuń
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="qr-group-content">
                            <p class="qr-group-description"><?php echo esc_html($group->description ?: 'Brak opisu'); ?></p>
                            <div class="qr-group-stats">
                                <div class="qr-group-stat">
                                    <span class="qr-stat-number"><?php echo intval($group->codes_count); ?></span>
                                    <span class="qr-stat-label">Kodów</span>
                                </div>
                                <div class="qr-group-stat">
                                    <span class="qr-stat-number"><?php echo intval($group->active_codes); ?></span>
                                    <span class="qr-stat-label">Aktywnych</span>
                                </div>
                            </div>
                        </div>
                        <div class="qr-group-footer">
                            <a href="<?php echo admin_url('admin.php?page=qr-system-codes&filter_group=' . $group->id); ?>" class="qr-group-link">
                                Zobacz kody <span class="dashicons dashicons-arrow-right"></span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="qr-empty-state">
                    <span class="dashicons dashicons-category"></span>
                    <h3>Brak grup</h3>
                    <p>Utwórz pierwszą grupę aby organizować kody QR</p>
                    <a href="<?php echo admin_url('admin.php?page=qr-system-groups&action=add'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-plus"></span> Utwórz grupę
                    </a>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($action === 'add'): ?>
        <div class="qr-form-container">
            <div class="qr-form-header">
                <h1><span class="dashicons dashicons-plus"></span> Dodaj nową grupę</h1>
                <a href="<?php echo admin_url('admin.php?page=qr-system-groups'); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-arrow-left"></span> Powrót
                </a>
            </div>

            <form method="post" class="qr-form">
                <?php wp_nonce_field('qr_add_group', 'qr_group_nonce'); ?>
                
                <div class="qr-form-section">
                    <div class="qr-field-group">
                        <label for="name" class="qr-field-label required">
                            Nazwa grupy <span class="qr-field-required">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="qr-input" required maxlength="100">
                    </div>

                    <div class="qr-field-group">
                        <label for="description" class="qr-field-label">Opis</label>
                        <textarea id="description" name="description" class="qr-textarea" rows="3"></textarea>
                    </div>

                    <div class="qr-field-group">
                        <label for="color" class="qr-field-label">Kolor</label>
                        <input type="color" id="color" name="color" class="qr-color-input" value="#007cba">
                    </div>
                </div>

                <div class="qr-form-footer">
                    <button type="submit" name="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-yes"></span> Utwórz grupę
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=qr-system-groups'); ?>" class="button button-secondary button-large">
                        Anuluj
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>