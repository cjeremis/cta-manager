<?php
/**
 * Support AJAX Handler
 *
 * Handles AJAX requests for support tickets and support API operations.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CTA_Support_AJAX {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Rate limit transient prefix
     */
    const RATE_LIMIT_PREFIX = 'cta_support_rate_limit_';

    /**
     * API base URL
     */
	private $api_base_url;

	/**
	 * Option key for tracking last synced support replies per user
	 */
	private const USER_REPLY_META_KEY = 'cta_support_last_reply_ids';

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
		// Set API base URL - can be filtered for different environments
		$this->api_base_url = apply_filters(
			'cta_support_api_base_url',
			'https://topdevamerica.com/wp-json/support-manager/v1'
		);
	}

	/**
	 * Sync remote support replies into local notifications for a user.
	 * Called before returning notifications so users see new replies in the panel.
	 *
	 * @param int $user_id
	 * @return void
	 */
	public function sync_notifications_for_user( int $user_id ): void {
		if ( ! class_exists( 'CTA_Notifications' ) ) {
			return;
		}

		// Pro required
		if ( ! class_exists( 'CTA_Pro_Feature_Gate' ) || ! CTA_Pro_Feature_Gate::is_pro_enabled() ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user || ! $user->user_email ) {
			return;
		}

		// Fetch tickets for this user
		$response = $this->api_request( 'GET', '/tickets', [
			'customer_email' => $user->user_email,
			'limit'          => 50,
			'offset'         => 0,
		] );

		if ( is_wp_error( $response ) || empty( $response['tickets'] ) ) {
			return;
		}

		$notifications = CTA_Notifications::get_instance();
		$last_meta     = get_user_meta( $user_id, self::USER_REPLY_META_KEY, true );
		$last_meta     = is_array( $last_meta ) ? $last_meta : [];

		foreach ( $response['tickets'] as $ticket ) {
			$ticket_id    = (int) ( $ticket['id'] ?? 0 );
			$ticket_num   = $ticket['ticket_number'] ?? $ticket_id;
			$ticket_title = $ticket['subject'] ?? __( 'Support Ticket', 'cta-manager' );

			if ( ! $ticket_id ) {
				continue;
			}

			// Get replies for this ticket
				$replies = $this->api_request( 'GET', "{$this->api_base_url}/tickets/{$ticket_id}/replies", [
					'customer_email' => $user->user_email,
				] );

			if ( is_wp_error( $replies ) || empty( $replies['replies'] ) ) {
				continue;
			}

				$last_seen_reply = isset( $last_meta[ $ticket_id ] ) ? (int) $last_meta[ $ticket_id ] : 0;
				$new_last_seen  = $last_seen_reply;

				foreach ( $replies['replies'] as $reply ) {
					$reply_id = (int) ( $reply['id'] ?? 0 );
					if ( ! $reply_id || $reply_id <= $last_seen_reply ) {
						continue;
					}

					$author_email  = isset( $reply['author_email'] ) ? strtolower( $reply['author_email'] ) : '';
					$is_admin_reply = $author_email !== strtolower( $user->user_email );
					$display_name  = $is_admin_reply ? 'TopDevAmerica' : ( $reply['author_name'] ?? 'You' );

					// Create notification for new reply
					$notifications->add_notification(
						'support_reply_' . $ticket_id . '_' . $reply_id,
						sprintf( __( 'New Reply on Ticket #%s', 'cta-manager' ), $ticket_num ),
						$display_name . ': ' . mb_substr( $reply['message'] ?? '', 0, 140 ),
						'email-alt',
					[
						[
							'label' => __( 'View Ticket', 'cta-manager' ),
							'url'   => admin_url( 'admin.php?page=cta-manager-support' ),
						],
					],
					$user_id
				);

				if ( $reply_id > $new_last_seen ) {
					$new_last_seen = $reply_id;
				}
			}

			// Update last seen reply id for this ticket
			if ( $new_last_seen > $last_seen_reply ) {
				$last_meta[ $ticket_id ] = $new_last_seen;
			}
		}

		update_user_meta( $user_id, self::USER_REPLY_META_KEY, $last_meta );
	}

    /**
     * Submit a new support ticket
     */
    public function submit_ticket() {
        // Verify nonce
        check_ajax_referer('cta_support_nonce', 'nonce');

        // Verify Pro is enabled
        if (!class_exists('CTA_Pro_Feature_Gate') || !CTA_Pro_Feature_Gate::is_pro_enabled()) {
            wp_send_json_error(
                array('message' => __('Priority support requires an active Pro license.', 'cta-manager')),
                403
            );
        }

        // Check rate limit for ticket submissions (5 per hour)
        $system_info = CTA_System_Info::get_instance();
        $user_email = $system_info->get_current_user_email();

        if (!$this->check_rate_limit('ticket_submit_' . md5($user_email), 5, HOUR_IN_SECONDS)) {
            wp_send_json_error(
                array('message' => __('You have submitted too many tickets recently. Please try again later.', 'cta-manager')),
                429
            );
        }

        // Validate required fields
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $description = isset($_POST['description']) ? wp_kses_post($_POST['description']) : '';
        $priority = isset($_POST['priority']) ? sanitize_text_field($_POST['priority']) : 'normal';

        if (empty($subject) || strlen($subject) < 5) {
            wp_send_json_error(
                array('message' => __('Subject must be at least 5 characters long.', 'cta-manager')),
                400
            );
        }

        if (empty($description) || strlen($description) < 10) {
            wp_send_json_error(
                array('message' => __('Description must be at least 10 characters long.', 'cta-manager')),
                400
            );
        }

        // Get system information
        $system_data = $system_info->get_all_info();

        // Generate ticket number locally for immediate reference
        $ticket_number = $this->generate_ticket_number();

        // Prepare ticket data for API
        $ticket_data = array(
            'ticket_number' => $ticket_number,
            'customer_email' => $user_email,
            'customer_site_url' => home_url(),
            'subject' => $subject,
            'description' => $description,
            'priority' => $priority,
            'status' => 'new',
            'wp_version' => $system_data['wp_version'] ?? '',
            'php_version' => $system_data['php_version'] ?? '',
            'theme_name' => $system_data['theme_name'] ?? '',
            'theme_version' => $system_data['theme_version'] ?? '',
            'active_plugins' => $system_data['active_plugins'] ?? array(),
            'cta_manager_version' => $system_data['cta_manager_version'] ?? '',
            'cta_manager_pro_version' => $system_data['cta_manager_pro_version'] ?? '',
            'customer_ip' => $system_info->get_client_ip(),
            'customer_user_agent' => $system_info->get_user_agent(),
        );

        // Send to TopDevAmerica API
        $response = $this->api_request('POST', '/tickets', $ticket_data);

        if (is_wp_error($response)) {
            wp_send_json_error(
                array('message' => $response->get_error_message()),
                500
            );
        }

        if (!isset($response['success']) || !$response['success']) {
            wp_send_json_error(
                array('message' => $response['message'] ?? __('Failed to create ticket.', 'cta-manager')),
                500
            );
        }

        wp_send_json_success(array(
            'message' => __('Support ticket submitted successfully.', 'cta-manager'),
            'ticket_number' => $response['ticket_number'] ?? $ticket_number,
            'ticket_id' => $response['ticket_id'] ?? null,
        ));
    }

    /**
     * Get support tickets for current user
     */
    public function get_tickets() {
        // Verify nonce
        check_ajax_referer('cta_support_nonce', 'nonce');

        // Verify Pro is enabled
        if (!class_exists('CTA_Pro_Feature_Gate') || !CTA_Pro_Feature_Gate::is_pro_enabled()) {
            wp_send_json_error(
                array('message' => __('Priority support requires an active Pro license.', 'cta-manager')),
                403
            );
        }

        // Get current user email
        $system_info = CTA_System_Info::get_instance();
        $user_email = $system_info->get_current_user_email();

        if (!$user_email) {
            wp_send_json_error(
                array('message' => __('User email not found.', 'cta-manager')),
                400
            );
        }

        // Get pagination parameters
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 20;
        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;

        // Get tickets from API
        $response = $this->api_request('GET', '/tickets', array(
            'customer_email' => $user_email,
            'limit' => $limit,
            'offset' => $offset,
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(
                array('message' => $response->get_error_message()),
                500
            );
        }

        wp_send_json_success(array(
            'tickets' => $response['tickets'] ?? array(),
            'total' => $response['total'] ?? 0,
            'count' => count($response['tickets'] ?? array()),
        ));
    }

    /**
     * Get ticket detail with replies
     */
    public function get_ticket_detail() {
        // Verify nonce
        check_ajax_referer('cta_support_nonce', 'nonce');

        // Verify Pro is enabled
        if (!class_exists('CTA_Pro_Feature_Gate') || !CTA_Pro_Feature_Gate::is_pro_enabled()) {
            wp_send_json_error(
                array('message' => __('Priority support requires an active Pro license.', 'cta-manager')),
                403
            );
        }

        // Get ticket ID
        $ticket_id = isset($_POST['ticket_id']) ? absint($_POST['ticket_id']) : 0;

        if (!$ticket_id) {
            wp_send_json_error(
                array('message' => __('Invalid ticket ID.', 'cta-manager')),
                400
            );
        }

        // Get current user email for ownership verification
        $system_info = CTA_System_Info::get_instance();
        $user_email = $system_info->get_current_user_email();

        // Get ticket from API
        $response = $this->api_request('GET', "/tickets/{$ticket_id}", array(
            'customer_email' => $user_email,
        ));

        if (is_wp_error($response)) {
            $error_data = $response->get_error_data();
            $status = is_array($error_data) && isset($error_data['status']) ? $error_data['status'] : 500;
            wp_send_json_error(
                array('message' => $response->get_error_message()),
                $status
            );
        }

        $replies = $response['replies'] ?? array();

        // Normalize author for replies: remote replies show as TopDevAmerica/Support Team
        foreach ($replies as &$reply) {
            $author_email = isset($reply['author_email']) ? strtolower($reply['author_email']) : '';
            $reply['author_type'] = ($author_email === strtolower($user_email)) ? 'user' : 'admin';
            $reply['author_name'] = ($reply['author_type'] === 'admin') ? 'TopDevAmerica' : ($reply['author_name'] ?? 'You');
        }
        unset($reply);

        wp_send_json_success(array(
            'ticket' => $response['ticket'] ?? null,
            'replies' => $replies,
        ));
    }

    /**
     * Submit a reply to a ticket
     */
    public function submit_reply() {
        // Verify nonce
        check_ajax_referer('cta_support_nonce', 'nonce');

        // Verify Pro is enabled
        if (!class_exists('CTA_Pro_Feature_Gate') || !CTA_Pro_Feature_Gate::is_pro_enabled()) {
            wp_send_json_error(
                array('message' => __('Priority support requires an active Pro license.', 'cta-manager')),
                403
            );
        }

        // Get system info
        $system_info = CTA_System_Info::get_instance();
        $user_email = $system_info->get_current_user_email();

        // Check rate limit for replies (10 per hour)
        if (!$this->check_rate_limit('reply_submit_' . md5($user_email), 10, HOUR_IN_SECONDS)) {
            wp_send_json_error(
                array('message' => __('You have submitted too many replies recently. Please try again later.', 'cta-manager')),
                429
            );
        }

        // Get ticket ID and message
        $ticket_id = isset($_POST['ticket_id']) ? absint($_POST['ticket_id']) : 0;
        $message = isset($_POST['message']) ? wp_kses_post($_POST['message']) : '';

        if (!$ticket_id) {
            wp_send_json_error(
                array('message' => __('Invalid ticket ID.', 'cta-manager')),
                400
            );
        }

        if (empty($message) || strlen($message) < 5) {
            wp_send_json_error(
                array('message' => __('Message must be at least 5 characters long.', 'cta-manager')),
                400
            );
        }

        // Prepare reply data
        $reply_data = array(
            'author_name' => $system_info->get_current_user_name(),
            'author_email' => $user_email,
            'message' => $message,
            'ip_address' => $system_info->get_client_ip(),
            'user_agent' => $system_info->get_user_agent(),
        );

        // Send reply to API
        $response = $this->api_request('POST', "/tickets/{$ticket_id}/replies", $reply_data);

        if (is_wp_error($response)) {
            $error_data = $response->get_error_data();
            $status = is_array($error_data) && isset($error_data['status']) ? $error_data['status'] : 500;
            wp_send_json_error(
                array('message' => $response->get_error_message()),
                $status
            );
        }

        wp_send_json_success(array(
            'message' => __('Reply submitted successfully.', 'cta-manager'),
            'reply_id' => $response['reply_id'] ?? null,
        ));
    }

    /**
     * Make an API request to TopDevAmerica
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint API endpoint (e.g., '/tickets')
     * @param array $data Request data
     * @return array|WP_Error Response data or error
     */
    private function api_request($method, $endpoint, $data = array()) {
        $url = $this->api_base_url . $endpoint;

        // For GET requests, add data as query params
        if ($method === 'GET' && !empty($data)) {
            $url = add_query_arg($data, $url);
        }

        // Generate authentication headers
        $timestamp = time();
        $site_url = home_url();
        $signature = $this->generate_signature($data, $timestamp);

        $args = array(
            'method' => $method,
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Site-URL' => $site_url,
                'X-Timestamp' => $timestamp,
                'X-Signature' => $signature,
            ),
            'timeout' => 30,
        );

        // For POST requests, add body
        if ($method === 'POST') {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return new WP_Error(
                'api_error',
                __('Failed to connect to support server. Please try again later.', 'cta-manager')
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if ($status_code >= 400) {
            $message = $decoded['message'] ?? __('An error occurred.', 'cta-manager');
            return new WP_Error('api_error', $message, array('status' => $status_code));
        }

        return $decoded ?: array();
    }

    /**
     * Generate ticket number
     * Format: CTASUP-YYYYMMDD-####
     *
     * @return string Ticket number
     */
    private function generate_ticket_number() {
        $date_prefix = 'CTASUP-' . gmdate('Ymd') . '-';
        $random_suffix = str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $date_prefix . $random_suffix;
    }

    /**
     * Check rate limit
     *
     * @param string $key Rate limit key
     * @param int $limit Maximum requests
     * @param int $window Time window in seconds
     * @return bool True if within limit
     */
    private function check_rate_limit($key, $limit, $window) {
        $transient_key = self::RATE_LIMIT_PREFIX . $key;
        $count = get_transient($transient_key);

        if ($count === false) {
            set_transient($transient_key, 1, $window);
            return true;
        }

        if ($count >= $limit) {
            return false;
        }

        set_transient($transient_key, $count + 1, $window);
        return true;
    }

    /**
     * Generate HMAC signature for API request
     *
     * @param array $payload Request payload
     * @param int $timestamp Unix timestamp
     * @return string HMAC signature
     */
    private function generate_signature($payload, $timestamp) {
        $secret = CTA_Pro_Feature_Gate::get_license_key();
        $data = wp_json_encode($payload) . $timestamp;
        return hash_hmac('sha256', $data, $secret);
    }
}
