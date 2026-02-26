<?php
/**
 * Admin Partial Template - Support Ticket Form
 *
 * Handles markup rendering for the support ticket form admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="cta-new-ticket-modal" class="cta-modal cta-support-ticket-modal" style="display: none;">
    <div class="cta-modal-overlay"></div>
    <div class="cta-modal-content">
        <div class="cta-modal-header">
            <div class="cta-modal-title-wrap">
                <span class="cta-modal-title-icon">
                    <span class="dashicons dashicons-phone"></span>
                </span>
                <h2><?php esc_html_e('Submit New Support Ticket', 'cta-manager'); ?></h2>
            </div>
            <button type="button" class="cta-modal-close" aria-label="<?php esc_attr_e('Close', 'cta-manager'); ?>">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>

        <div class="cta-modal-body">
            <form id="cta-support-ticket-form" class="cta-form">
                <?php wp_nonce_field('cta_support_nonce', 'cta_support_nonce'); ?>

                <div class="cta-form-group">
                    <label for="cta-ticket-subject"><?php esc_html_e('Subject', 'cta-manager'); ?> <span class="cta-required">*</span></label>
                    <input
                        type="text"
                        id="cta-ticket-subject"
                        name="subject"
                        required
                        minlength="5"
                        maxlength="500"
                        placeholder="<?php esc_attr_e('Brief description of your issue', 'cta-manager'); ?>"
                    />
                </div>

                <div class="cta-form-group">
                    <label for="cta-ticket-priority"><?php esc_html_e('Priority', 'cta-manager'); ?></label>
                    <select id="cta-ticket-priority" name="priority">
                        <option value="normal"><?php esc_html_e('Normal', 'cta-manager'); ?></option>
                        <option value="low"><?php esc_html_e('Low', 'cta-manager'); ?></option>
                        <option value="high"><?php esc_html_e('High', 'cta-manager'); ?></option>
                        <option value="critical"><?php esc_html_e('Critical', 'cta-manager'); ?></option>
                    </select>
                </div>

                <div class="cta-form-group">
                    <label for="cta-ticket-description"><?php esc_html_e('Description', 'cta-manager'); ?> <span class="cta-required">*</span></label>
                    <textarea
                        id="cta-ticket-description"
                        name="description"
                        rows="8"
                        required
                        minlength="10"
                        placeholder="<?php esc_attr_e('Provide detailed information about your issue, including steps to reproduce if applicable...', 'cta-manager'); ?>"
                    ></textarea>
                </div>

                <div class="cta-info-box cta-info-box--info">
                    <span class="dashicons dashicons-info"></span>
                    <div>
                        <strong><?php esc_html_e('System Information Auto-Collected', 'cta-manager'); ?></strong>
                        <p><?php esc_html_e('Your WordPress version, theme, active plugins, and other system details will be automatically included to help us diagnose your issue faster.', 'cta-manager'); ?></p>
                    </div>
                </div>

                <div id="cta-ticket-form-message" class="cta-message" style="display: none;"></div>
            </form>
        </div>

        <div class="cta-modal-footer">
            <button type="submit" form="cta-support-ticket-form" class="cta-button-primary" id="cta-submit-ticket-btn">
                <?php esc_html_e('Submit Ticket', 'cta-manager'); ?>
            </button>
        </div>
    </div>
</div>
