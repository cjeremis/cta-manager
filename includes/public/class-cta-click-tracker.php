<?php
/**
 * Click Tracker Handler
 *
 * Handles public click tracking and analytics event submission.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Click_Tracker {

	use CTA_Singleton;

	private const RATE_LIMIT = 10;

	/**
	 * Track button click
	 *
	 * @return void
	 */
	public function track_click(): void {
		if ( ! check_ajax_referer( 'cta_public_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		if ( $this->is_rate_limited() ) {
			wp_send_json_error( [ 'message' => 'Rate limited' ], 429 );
		}

		$payload = isset( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : [];
		$cta_id  = isset( $payload['cta_id'] ) ? absint( $payload['cta_id'] ) : 0;
		$cta_title = isset( $payload['cta_title'] ) ? sanitize_text_field( $payload['cta_title'] ) : '';
		$page_url = isset( $payload['page_url'] ) ? esc_url_raw( $payload['page_url'] ) : '';
		$page_title = isset( $payload['page_title'] ) ? sanitize_text_field( $payload['page_title'] ) : '';
		$context = $this->build_request_context();

		$data    = CTA_Data::get_instance();
		$result  = $data->record_analytics_event(
			[
				'type'       => 'click',
				'cta_id'     => $cta_id,
				'cta_title'  => $cta_title,
				'page_url'   => $page_url,
				'page_title' => $page_title,
				'referrer'   => $context['referrer'],
				'ip_address' => $context['ip_address'],
				'user_agent' => $context['user_agent'],
				'device'     => $context['device'],
				'visitor_id' => $context['visitor_id'],
				'session_id' => $context['session_id'],
				'context'    => [],
			]
		);

		if ( $result ) {
			CTA_Debug::tracking( 'click', $cta_id, [
				'page_url'   => $page_url,
				'visitor_id' => $context['visitor_id'],
				'device'     => $context['device'],
			] );
			wp_send_json_success( [ 'tracked' => true ] );
		} else {
			CTA_Debug::error( 'Failed to track click event', 'tracking', [ 'cta_id' => $cta_id ] );
			wp_send_json_error( [ 'message' => 'Failed to track' ], 500 );
		}
	}

	/**
	 * Check if request is rate limited
	 *
	 * @return bool
	 */
	private function is_rate_limited(): bool {
		$ip             = $this->get_client_ip();
		$transient_key = 'cta_rate_' . md5( $ip );
		$count          = get_transient( $transient_key );

		if ( false === $count ) {
			set_transient( $transient_key, 1, MINUTE_IN_SECONDS );
			return false;
		}

		if ( $count >= self::RATE_LIMIT ) {
			return true;
		}

		set_transient( $transient_key, $count + 1, MINUTE_IN_SECONDS );
		return false;
	}

	/**
	 * Get client IP address
	 *
	 * @return string
	 */
	private function get_client_ip(): string {
		$ip = '';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			if ( strpos( $ip, ',' ) !== false ) {
				$ip = trim( explode( ',', $ip )[0] );
			}
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
	}

	/**
	 * Track impressions (batch).
	 *
	 * @return void
	 */
	public function track_impression(): void {
		if ( ! check_ajax_referer( 'cta_public_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$payloads = isset( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : [];
		if ( ! is_array( $payloads ) ) {
			wp_send_json_error( [ 'message' => 'Invalid payload' ], 400 );
		}

		$data   = CTA_Data::get_instance();
		$result = true;
		$context = $this->build_request_context();

		foreach ( $payloads as $payload ) {
			$cta_id = isset( $payload['cta_id'] ) ? absint( $payload['cta_id'] ) : 0;
			if ( ! $cta_id ) {
				continue;
			}
			$cta_title = isset( $payload['cta_title'] ) ? sanitize_text_field( $payload['cta_title'] ) : '';
			$page_url = isset( $payload['page_url'] ) ? esc_url_raw( $payload['page_url'] ) : '';
			$page_title = isset( $payload['page_title'] ) ? sanitize_text_field( $payload['page_title'] ) : '';

			$result = $result && $data->record_analytics_event(
				[
					'type'       => 'impression',
					'cta_id'     => $cta_id,
					'cta_title'  => $cta_title,
					'page_url'   => $page_url,
					'page_title' => $page_title,
					'referrer'   => $context['referrer'],
					'ip_address' => $context['ip_address'],
					'user_agent' => $context['user_agent'],
					'device'     => $context['device'],
					'visitor_id' => $context['visitor_id'],
					'session_id' => $context['session_id'],
					'context'    => [],
				]
			);
		}

		if ( $result ) {
			$cta_ids = array_filter( array_map( function( $p ) {
				return isset( $p['cta_id'] ) ? absint( $p['cta_id'] ) : 0;
			}, $payloads ) );
			CTA_Debug::log( sprintf( 'Tracked %d impressions for CTAs: %s', count( $cta_ids ), implode( ', ', $cta_ids ) ), 'tracking' );
			wp_send_json_success( [ 'tracked' => true ] );
		} else {
			CTA_Debug::error( 'Failed to track impressions', 'tracking' );
			wp_send_json_error( [ 'message' => 'Failed to track impressions' ], 500 );
		}
	}

	/**
	 * Track CTA page views (batch).
	 *
	 * @return void
	 */
	public function track_page_view(): void {
		if ( ! check_ajax_referer( 'cta_public_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
		}

		$payloads = isset( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : [];
		if ( ! is_array( $payloads ) ) {
			wp_send_json_error( [ 'message' => 'Invalid payload' ], 400 );
		}

		$data    = CTA_Data::get_instance();
		$result  = true;
		$context = $this->build_request_context();

		foreach ( $payloads as $payload ) {
			$cta_id = isset( $payload['cta_id'] ) ? absint( $payload['cta_id'] ) : 0;
			if ( ! $cta_id ) {
				continue;
			}
			$cta_title  = isset( $payload['cta_title'] ) ? sanitize_text_field( $payload['cta_title'] ) : '';
			$page_url   = isset( $payload['page_url'] ) ? esc_url_raw( $payload['page_url'] ) : '';
			$page_title = isset( $payload['page_title'] ) ? sanitize_text_field( $payload['page_title'] ) : '';

			$result = $result && $data->record_analytics_event(
				[
					'type'       => 'page_view',
					'cta_id'     => $cta_id,
					'cta_title'  => $cta_title,
					'page_url'   => $page_url,
					'page_title' => $page_title,
					'referrer'   => $context['referrer'],
					'ip_address' => $context['ip_address'],
					'user_agent' => $context['user_agent'],
					'device'     => $context['device'],
					'visitor_id' => $context['visitor_id'],
					'session_id' => $context['session_id'],
					'context'    => [],
				]
			);
		}

		if ( $result ) {
			$cta_ids = array_filter( array_map( function( $p ) {
				return isset( $p['cta_id'] ) ? absint( $p['cta_id'] ) : 0;
			}, $payloads ) );
			CTA_Debug::log( sprintf( 'Tracked %d page views for CTAs: %s', count( $cta_ids ), implode( ', ', $cta_ids ) ), 'tracking' );
			wp_send_json_success( [ 'tracked' => true ] );
		} else {
			CTA_Debug::error( 'Failed to track page views', 'tracking' );
			wp_send_json_error( [ 'message' => 'Failed to track page views' ], 500 );
		}
	}

	/**
	 * Build context details for tracking (IP, UA, referrer, device, visitor/session IDs)
	 *
	 * @return array
	 */
	private function build_request_context(): array {
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		return [
			'ip_address' => $this->get_client_ip(),
			'user_agent' => $user_agent,
			'referrer'   => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
			'device'     => $this->detect_device( $user_agent ),
			'visitor_id' => $this->get_visitor_id(),
			'session_id' => $this->get_session_id(),
		];
	}

	/**
	 * Get visitor ID using CTA_Visitor service
	 *
	 * @return int|null
	 */
	private function get_visitor_id(): ?int {
		return CTA_Visitor::get_instance()->get_visitor_id();
	}

	/**
	 * Get session ID (session-scoped identifier)
	 *
	 * @return string
	 */
	private function get_session_id(): string {
		$cookie_name = 'cta_session_id';

		if ( isset( $_COOKIE[ $cookie_name ] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
		}

		// Generate new session ID
		$session_id = 's_' . bin2hex( random_bytes( 16 ) );

		// Set session cookie (expires when browser closes)
		if ( ! headers_sent() ) {
			setcookie(
				$cookie_name,
				$session_id,
				[
					'expires'  => 0, // Session cookie
					'path'     => '/',
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Lax',
				]
			);
		}

		return $session_id;
	}

	/**
	 * Detect device type from user agent
	 *
	 * @param string $user_agent User agent string
	 *
	 * @return string
	 */
	private function detect_device( string $user_agent ): string {
		if ( preg_match( '/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $user_agent ) ) {
			if ( preg_match( '/ipad|tablet|kindle|playbook|silk|nexus 7|nexus 10/i', $user_agent ) ) {
				return 'tablet';
			}
			return 'mobile';
		}
		return 'desktop';
	}
}
