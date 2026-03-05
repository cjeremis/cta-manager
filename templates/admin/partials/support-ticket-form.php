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

$privacy_page_id = (int) get_option( 'tda_shared_privacy_page_id' );
$terms_page_id   = (int) get_option( 'tda_shared_terms_page_id' );
$privacy_url     = $privacy_page_id ? get_permalink( $privacy_page_id ) : 'https://topdevamerica.com/privacy-policy';
$terms_url       = $terms_page_id ? get_permalink( $terms_page_id ) : 'https://topdevamerica.com/terms';
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
	            <?php
	            $is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
	            if ( $is_pro ) : ?>
	                <div class="cta-support-status-banner cta-support-status-banner--pro">
                    <div class="cta-support-status-icon">
                        <span class="dashicons dashicons-star-filled"></span>
                    </div>
                    <div class="cta-support-status-text">
                        <strong><?php esc_html_e( 'Priority Support Active', 'cta-manager' ); ?></strong>
                        <p><?php esc_html_e( 'As a Pro user, your tickets are prioritized and you can expect a response within 3–4 hours.', 'cta-manager' ); ?></p>
                    </div>
                </div>
	            <?php else : ?>
	                <div class="cta-support-status-banner cta-support-status-banner--free">
	                    <div class="cta-support-status-icon">
	                        <span class="dashicons dashicons-clock"></span>
	                    </div>
	                    <div class="cta-support-status-text">
	                        <strong><?php esc_html_e( 'Priority Support Requires Pro', 'cta-manager' ); ?></strong>
	                        <p><?php esc_html_e( 'Ticket-based support from this modal is available in CTA Manager Pro.', 'cta-manager' ); ?></p>
	                        <p class="cta-support-upgrade-hint">
	                            <?php esc_html_e( 'Upgrade to unlock full ticket management, faster response, and threaded replies.', 'cta-manager' ); ?>
	                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager&modal=features&tab=overview' ) ); ?>" class="cta-support-upgrade-link"><?php esc_html_e( 'Upgrade to Pro', 'cta-manager' ); ?> &rarr;</a>
	                        </p>
	                    </div>
	                </div>
	            <?php endif; ?>

	            <?php if ( $is_pro ) : ?>
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
                        <p>
                            <a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy Policy', 'cta-manager' ); ?></a>
                            |
                            <a href="<?php echo esc_url( $terms_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Terms of Service', 'cta-manager' ); ?></a>
                        </p>
                    </div>
                </div>

	                <div id="cta-ticket-form-message" class="cta-message" style="display: none;"></div>
	            </form>
	            <?php else : ?>
	                <div class="cta-info-box cta-info-box--info">
	                    <span class="dashicons dashicons-info"></span>
	                    <div>
	                        <strong><?php esc_html_e( 'Need setup help first?', 'cta-manager' ); ?></strong>
	                        <p><?php esc_html_e( 'Open the docs modal for onboarding guides and implementation walkthroughs.', 'cta-manager' ); ?></p>
	                        <p>
	                            <a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy Policy', 'cta-manager' ); ?></a>
	                            |
	                            <a href="<?php echo esc_url( $terms_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Terms of Service', 'cta-manager' ); ?></a>
	                        </p>
	                    </div>
	                </div>
	            <?php endif; ?>
	        </div>

	        <div class="cta-modal-footer">
	            <?php if ( $is_pro ) : ?>
	            <button type="submit" form="cta-support-ticket-form" class="cta-button-primary" id="cta-submit-ticket-btn">
	                <?php esc_html_e('Submit Ticket', 'cta-manager'); ?>
	            </button>
	            <?php else : ?>
	            <a class="cta-button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager&modal=features&tab=overview' ) ); ?>">
	                <?php esc_html_e( 'Upgrade to Pro', 'cta-manager' ); ?>
	            </a>
	            <?php endif; ?>
	        </div>
	    </div>
	</div>
