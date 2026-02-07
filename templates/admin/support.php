<?php
/**
 * Priority Support Page Template
 *
 * Main template for customer support ticket management.
 * Tickets are loaded via AJAX from the TopDevAmerica API.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// User tickets will be loaded via JavaScript/AJAX from the TopDevAmerica API
$user_tickets = array();
?>

<div class="cta-support-container" data-load-tickets="true">
    <?php include CTA_PLUGIN_DIR . 'templates/admin/partials/support-ticket-form.php'; ?>

    <div id="cta-tickets-container">
        <!-- Loading state - JavaScript will replace this with ticket list -->
        <div class="cta-support-tickets-list cta-loading-state">
            <div class="cta-section">
                <h2 class="cta-section-title"><?php esc_html_e('Your Support Tickets', 'cta-manager'); ?></h2>
                <div class="cta-loading-spinner">
                    <span class="spinner is-active"></span>
                    <p><?php esc_html_e('Loading your tickets...', 'cta-manager'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
