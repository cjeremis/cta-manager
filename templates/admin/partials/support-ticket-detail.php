<?php
/**
 * Admin Partial Template - Support Ticket Detail
 *
 * Handles markup rendering for the support ticket detail admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cta-support-ticket-detail">
    <!-- Ticket Header -->
    <div class="cta-card cta-ticket-header">
        <div class="cta-ticket-meta">
            <h2><?php echo esc_html($ticket->subject); ?></h2>
            <div class="cta-ticket-badges">
                <span class="cta-ticket-number">
                    <span class="dashicons dashicons-tickets-alt"></span>
                    <?php echo esc_html($ticket->ticket_number); ?>
                </span>
                <?php
                $variant = CTA_Support::get_status_badge_class($ticket->status);
                $text = CTA_Support::get_status_label($ticket->status);
                include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
                unset($variant, $text);
                ?>
                <?php
                $variant = CTA_Support::get_priority_badge_class($ticket->priority);
                $text = CTA_Support::get_priority_label($ticket->priority);
                include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
                unset($variant, $text);
                ?>
            </div>
            <div class="cta-ticket-timestamps">
                <span class="cta-ticket-created">
                    <strong><?php esc_html_e('Created:', 'cta-manager'); ?></strong>
                    <?php echo esc_html(human_time_diff(strtotime($ticket->created_at), current_time('timestamp'))); ?> <?php esc_html_e('ago', 'cta-manager'); ?>
                </span>
                <?php if ($ticket->last_reply_at) : ?>
                    <span class="cta-ticket-updated">
                        <strong><?php esc_html_e('Last Updated:', 'cta-manager'); ?></strong>
                        <?php echo esc_html(human_time_diff(strtotime($ticket->last_reply_at), current_time('timestamp'))); ?> <?php esc_html_e('ago', 'cta-manager'); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Conversation Thread -->
    <div class="cta-card cta-ticket-conversation">
        <h3 class="cta-section-title"><?php esc_html_e('Conversation', 'cta-manager'); ?></h3>

        <div class="cta-conversation-thread">
            <!-- Original Ticket -->
            <div class="cta-conversation-message cta-conversation-message--customer cta-conversation-message--original">
                <div class="cta-message-avatar">
                    <span class="dashicons dashicons-admin-users"></span>
                </div>
                <div class="cta-message-content">
                    <div class="cta-message-header">
                        <strong><?php esc_html_e('You', 'cta-manager'); ?></strong>
                        <span class="cta-message-date">
                            <?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $ticket->created_at)); ?>
                        </span>
                    </div>
                    <div class="cta-message-body">
                        <?php echo wp_kses_post($ticket->description); ?>
                    </div>
                </div>
            </div>

            <!-- Replies -->
            <?php if (!empty($replies)) : ?>
                <?php foreach ($replies as $reply) : ?>
                    <div class="cta-conversation-message cta-conversation-message--<?php echo esc_attr($reply->author_type); ?>">
                        <div class="cta-message-avatar">
                            <?php if ($reply->author_type === 'admin') : ?>
                                <span class="dashicons dashicons-businessman"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-admin-users"></span>
                            <?php endif; ?>
                        </div>
                        <div class="cta-message-content">
                            <div class="cta-message-header">
                                <strong>
                                    <?php
                                    if ($reply->author_type === 'admin') {
                                        esc_html_e('Support Team', 'cta-manager');
                                    } else {
                                        esc_html_e('You', 'cta-manager');
                                    }
                                    ?>
                                </strong>
                                <span class="cta-message-date">
                                    <?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $reply->created_at)); ?>
                                </span>
                            </div>
                            <div class="cta-message-body">
                                <?php echo wp_kses_post($reply->message); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reply Form -->
    <?php if ($ticket->status !== 'closed' && $ticket->status !== 'archived') : ?>
        <div class="cta-card cta-ticket-reply-form">
            <h3 class="cta-section-title"><?php esc_html_e('Add Reply', 'cta-manager'); ?></h3>
            <form id="cta-ticket-reply-form" class="cta-form">
                <?php wp_nonce_field('cta_support_nonce', 'cta_support_reply_nonce'); ?>
                <input type="hidden" name="ticket_id" value="<?php echo esc_attr($ticket->id); ?>" />

                <div class="cta-form-group">
                    <label for="cta-reply-message"><?php esc_html_e('Your Reply', 'cta-manager'); ?></label>
                    <textarea
                        id="cta-reply-message"
                        name="message"
                        rows="6"
                        required
                        minlength="5"
                        placeholder="<?php esc_attr_e('Type your reply here...', 'cta-manager'); ?>"
                    ></textarea>
                </div>

                <div id="cta-reply-form-message" class="cta-message" style="display: none;"></div>

                <button type="submit" class="cta-button-primary" id="cta-submit-reply-btn">
                    <?php esc_html_e('Send Reply', 'cta-manager'); ?>
                </button>
            </form>
        </div>
    <?php else : ?>
        <div class="cta-info-box cta-info-box--warning">
            <span class="dashicons dashicons-lock"></span>
            <div>
                <strong><?php esc_html_e('Ticket Closed', 'cta-manager'); ?></strong>
                <p><?php esc_html_e('This ticket has been closed. If you need further assistance, please create a new ticket.', 'cta-manager'); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- System Information -->
    <div class="cta-card cta-ticket-system-info">
        <h3 class="cta-section-title"><?php esc_html_e('System Information', 'cta-manager'); ?></h3>
        <div class="cta-system-info-grid">
            <div class="cta-system-info-item">
                <strong><?php esc_html_e('WordPress:', 'cta-manager'); ?></strong>
                <span><?php echo esc_html($ticket->wp_version); ?></span>
            </div>
            <div class="cta-system-info-item">
                <strong><?php esc_html_e('PHP:', 'cta-manager'); ?></strong>
                <span><?php echo esc_html($ticket->php_version); ?></span>
            </div>
            <div class="cta-system-info-item">
                <strong><?php esc_html_e('Theme:', 'cta-manager'); ?></strong>
                <span><?php echo esc_html($ticket->theme_name . ' ' . $ticket->theme_version); ?></span>
            </div>
            <div class="cta-system-info-item">
                <strong><?php esc_html_e('CTA Manager:', 'cta-manager'); ?></strong>
                <span><?php echo esc_html($ticket->cta_manager_version ?: __('N/A', 'cta-manager')); ?></span>
            </div>
            <?php if ($ticket->cta_manager_pro_version) : ?>
                <div class="cta-system-info-item">
                    <strong><?php esc_html_e('CTA Manager Pro:', 'cta-manager'); ?></strong>
                    <span><?php echo esc_html($ticket->cta_manager_pro_version); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($ticket->active_plugins)) : ?>
            <details class="cta-system-info-details">
                <summary><?php esc_html_e('Active Plugins', 'cta-manager'); ?> (<?php echo count($ticket->active_plugins); ?>)</summary>
                <ul class="cta-plugin-list">
                    <?php foreach ($ticket->active_plugins as $plugin) : ?>
                        <li><?php echo esc_html($plugin['name'] . ' ' . $plugin['version']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>
    </div>
</div>
