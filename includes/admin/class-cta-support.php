<?php
/**
 * Support Page Handler
 *
 * Handles rendering and setup for the CTA Manager support page.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CTA_Support {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct() {
        // Constructor
    }

    /**
     * Render the support page
     */
    public function render() {
        // Check if Pro is enabled
        if (!class_exists('CTA_Pro_Feature_Gate') || !CTA_Pro_Feature_Gate::is_pro_enabled()) {
            $this->render_pro_required();
            return;
        }

        // Get current view
        $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'list';
        $ticket_id = isset($_GET['ticket_id']) ? absint($_GET['ticket_id']) : 0;

        // Page configuration
        $current_page = 'support';
        $header_title = __('Priority Support', 'cta-manager');
        $header_description = __('Submit and manage your priority support tickets', 'cta-manager');

        // Topbar actions
        $topbar_actions = array();

        if ($view === 'list') {
            $topbar_actions[] = '<button type="button" class="cta-button-primary" id="cta-new-ticket-btn">' . esc_html__('New Ticket', 'cta-manager') . '</button>';
        } elseif ($view === 'detail' && $ticket_id) {
            $list_url = admin_url('admin.php?page=cta-manager-support');
            $topbar_actions[] = '<a href="' . esc_url($list_url) . '" class="cta-button-secondary">' . esc_html__('Back to Tickets', 'cta-manager') . '</a>';
        }

        // Include page wrapper start
        include CTA_PLUGIN_DIR . 'templates/admin/partials/page-wrapper-start.php';

        // Render view
        if ($view === 'detail' && $ticket_id) {
            $this->render_ticket_detail($ticket_id);
        } else {
            $this->render_ticket_list();
        }

        // Include page wrapper end
        include CTA_PLUGIN_DIR . 'templates/admin/partials/page-wrapper-end.php';
    }

    /**
     * Render Pro required message
     */
    private function render_pro_required() {
        $current_page = 'support';
        $header_title = __('Priority Support', 'cta-manager');
        $header_description = __('Priority support is a Pro feature', 'cta-manager');

        include CTA_PLUGIN_DIR . 'templates/admin/partials/page-wrapper-start.php';
        ?>
        <div class="cta-card" style="text-align: center; padding: 40px;">
            <h2><?php esc_html_e('Priority Support is a Pro Feature', 'cta-manager'); ?></h2>
            <p><?php esc_html_e('Upgrade to CTA Manager Pro to access priority support with direct assistance from our team.', 'cta-manager'); ?></p>
            <?php
            $label = __('Upgrade to Pro', 'cta-manager');
            $url = admin_url('admin.php?page=cta-manager-settings#cta-pro-license-key');
            $variant = 'primary';
            include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-button.php';
            ?>
        </div>
        <?php
        include CTA_PLUGIN_DIR . 'templates/admin/partials/page-wrapper-end.php';
    }

    /**
     * Render ticket list view
     */
    private function render_ticket_list() {
        include CTA_PLUGIN_DIR . 'templates/admin/support.php';
    }

    /**
     * Render ticket detail view
     *
     * Ticket data is loaded via AJAX from the TopDevAmerica API.
     *
     * @param int $ticket_id Ticket ID
     */
    private function render_ticket_detail($ticket_id) {
        // Ticket and replies will be loaded via JavaScript/AJAX
        // We just render a container with loading state
        ?>
        <div id="cta-ticket-detail-container" data-ticket-id="<?php echo esc_attr($ticket_id); ?>">
            <div class="cta-card cta-loading-state">
                <div class="cta-loading-spinner">
                    <span class="spinner is-active"></span>
                    <p><?php esc_html_e('Loading ticket details...', 'cta-manager'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get status badge class
     *
     * @param string $status Ticket status
     * @return string Badge class
     */
    public static function get_status_badge_class($status) {
        $classes = array(
            'new' => 'primary',
            'in_progress' => 'info',
            'on_hold' => 'warning',
            'waiting_for_reply' => 'warning',
            'closed' => 'secondary',
            'archived' => 'secondary',
        );

        return isset($classes[$status]) ? $classes[$status] : 'secondary';
    }

    /**
     * Get status label
     *
     * @param string $status Ticket status
     * @return string Status label
     */
    public static function get_status_label($status) {
        $labels = array(
            'new' => __('New', 'cta-manager'),
            'in_progress' => __('In Progress', 'cta-manager'),
            'on_hold' => __('On Hold', 'cta-manager'),
            'waiting_for_reply' => __('Waiting for Reply', 'cta-manager'),
            'closed' => __('Closed', 'cta-manager'),
            'archived' => __('Archived', 'cta-manager'),
        );

        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }

    /**
     * Get priority badge class
     *
     * @param string $priority Ticket priority
     * @return string Badge class
     */
    public static function get_priority_badge_class($priority) {
        $classes = array(
            'low' => 'secondary',
            'normal' => 'info',
            'high' => 'warning',
            'critical' => 'danger',
        );

        return isset($classes[$priority]) ? $classes[$priority] : 'secondary';
    }

    /**
     * Get priority label
     *
     * @param string $priority Ticket priority
     * @return string Priority label
     */
    public static function get_priority_label($priority) {
        $labels = array(
            'low' => __('Low', 'cta-manager'),
            'normal' => __('Normal', 'cta-manager'),
            'high' => __('High', 'cta-manager'),
            'critical' => __('Critical', 'cta-manager'),
        );

        return isset($labels[$priority]) ? $labels[$priority] : ucfirst($priority);
    }
}
