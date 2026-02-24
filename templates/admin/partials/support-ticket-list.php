<?php
/**
 * Admin Partial Template - Support Ticket List
 *
 * Handles markup rendering for the support ticket list admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cta-support-tickets-list">
    <?php if (empty($user_tickets)) : ?>
        <?php
        $title = __('No Support Tickets Yet', 'cta-manager');
        $message = __('You haven\'t submitted any support tickets. Click the "New Ticket" button above to get started.', 'cta-manager');
        $icon = 'tickets-alt';
        include CTA_PLUGIN_DIR . 'templates/admin/partials/empty-state.php';
        ?>
    <?php else : ?>
        <div class="cta-section">
            <h2 class="cta-section-title"><?php esc_html_e('Your Support Tickets', 'cta-manager'); ?></h2>

            <div class="cta-data-table-wrapper">
                <table class="cta-data-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Ticket #', 'cta-manager'); ?></th>
                            <th><?php esc_html_e('Subject', 'cta-manager'); ?></th>
                            <th><?php esc_html_e('Status', 'cta-manager'); ?></th>
                            <th><?php esc_html_e('Priority', 'cta-manager'); ?></th>
                            <th><?php esc_html_e('Last Updated', 'cta-manager'); ?></th>
                            <th><?php esc_html_e('Actions', 'cta-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_tickets as $ticket) : ?>
                            <tr>
                                <td>
                                    <code class="cta-ticket-number"><?php echo esc_html($ticket->ticket_number); ?></code>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($ticket->subject); ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $variant = CTA_Support::get_status_badge_class($ticket->status);
                                    $text = CTA_Support::get_status_label($ticket->status);
                                    include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
                                    unset($variant, $text);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $variant = CTA_Support::get_priority_badge_class($ticket->priority);
                                    $text = CTA_Support::get_priority_label($ticket->priority);
                                    include CTA_PLUGIN_DIR . 'templates/admin/partials/status-badge.php';
                                    unset($variant, $text);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $updated = $ticket->last_reply_at ? $ticket->last_reply_at : $ticket->created_at;
                                    echo esc_html(human_time_diff(strtotime($updated), current_time('timestamp'))) . ' ' . esc_html__('ago', 'cta-manager');
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $view_url = add_query_arg(
                                        array(
                                            'page' => 'cta-manager-support',
                                            'view' => 'detail',
                                            'ticket_id' => $ticket->id,
                                        ),
                                        admin_url('admin.php')
                                    );
                                    ?>
                                    <a href="<?php echo esc_url($view_url); ?>" class="cta-button-link">
                                        <span class="dashicons dashicons-visibility"></span>
                                        <?php esc_html_e('View', 'cta-manager'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
