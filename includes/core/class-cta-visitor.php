<?php
/**
 * Visitor Tracking Handler
 *
 * Handles persistent visitor identification and visitor tracking data.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Visitor {

	use CTA_Singleton;

	const COOKIE_NAME     = 'cta_visitor_id';
	const COOKIE_DURATION = 30 * DAY_IN_SECONDS; // 30 days
	const TABLE_VISITORS  = 'cta_manager_visitors';

	/**
	 * Current visitor ID
	 *
	 * @var int|null
	 */
	private $visitor_id = null;

	/**
	 * Current visitor data
	 *
	 * @var array|null
	 */
	private $visitor_data = null;

	/**
	 * Initialize visitor tracking
	 *
	 * @return void
	 */
	public function init(): void {
		// Ensure database tables exist before initializing visitor tracking
		if ( ! class_exists( 'CTA_Database' ) ) {
			require_once CTA_PLUGIN_DIR . 'includes/core/class-cta-database.php';
		}
		CTA_Database::maybe_create_tables();

		$this->resolve_visitor();
		$this->set_visitor_cookie();

		// Hook into WordPress login to merge visitor records
		add_action( 'wp_login', array( $this, 'on_user_login' ), 10, 2 );
		add_action( 'user_register', array( $this, 'on_user_register' ), 10, 1 );
	}

	/**
	 * Get current visitor ID
	 *
	 * @return int|null
	 */
	public function get_visitor_id(): ?int {
		return $this->visitor_id;
	}

	/**
	 * Get current visitor data
	 *
	 * @return array|null
	 */
	public function get_visitor_data(): ?array {
		return $this->visitor_data;
	}

	/**
	 * Resolve visitor identity using priority: WordPress user → Cookie → New visitor
	 *
	 * @return void
	 */
	private function resolve_visitor(): void {
		// Priority 1: WordPress user ID
		if ( is_user_logged_in() ) {
			$user_id         = get_current_user_id();
			$this->visitor_id = $this->get_or_create_visitor_by_wp_user( $user_id );
			return;
		}

		// Priority 2: Cookie
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			$cookie_visitor_id = (int) $_COOKIE[ self::COOKIE_NAME ];
			if ( $this->visitor_exists( $cookie_visitor_id ) ) {
				$this->visitor_id = $cookie_visitor_id;
				$this->update_last_seen( $cookie_visitor_id );
				return;
			}
		}

		// Priority 3: Create new visitor
		$this->visitor_id = $this->create_visitor();
	}

	/**
	 * Check if visitor exists
	 *
	 * @param int $visitor_id Visitor ID.
	 *
	 * @return bool
	 */
	private function visitor_exists( int $visitor_id ): bool {
		return CTA_Database::exists(
			CTA_Database::table( self::TABLE_VISITORS ),
			array( 'id' => $visitor_id )
		);
	}

	/**
	 * Get or create visitor by WordPress user ID
	 *
	 * @param int $user_id WordPress user ID.
	 *
	 * @return int Visitor ID
	 */
	private function get_or_create_visitor_by_wp_user( int $user_id ): int {
		$visitor = CTA_Database::get_row(
			CTA_Database::table( self::TABLE_VISITORS ),
			array( 'wp_user_id' => $user_id )
		);

		if ( $visitor ) {
			$this->visitor_data = $visitor;
			$this->update_last_seen( (int) $visitor['id'] );
			return (int) $visitor['id'];
		}

		// Create new visitor with WP user ID
		return $this->create_visitor( $user_id );
	}

	/**
	 * Create new visitor record
	 *
	 * @param int|null $wp_user_id WordPress user ID.
	 *
	 * @return int Visitor ID
	 */
	private function create_visitor( ?int $wp_user_id = null ): int {
		$ip_address = $this->get_client_ip();
		$user_agent = $this->get_user_agent();
		$geo_data   = $this->get_geo_data( $ip_address );

		$data = array(
			'wp_user_id'  => $wp_user_id,
			'ip_address'  => $ip_address,
			'user_agent'  => $user_agent,
			'country'     => $geo_data['country'] ?? null,
			'region'      => $geo_data['region'] ?? null,
			'city'        => $geo_data['city'] ?? null,
			'first_seen'  => current_time( 'mysql' ),
			'last_seen'   => current_time( 'mysql' ),
			'visits'      => 1,
		);

		$visitor_id = CTA_Database::insert( CTA_Database::table( self::TABLE_VISITORS ), $data );

		if ( $visitor_id ) {
			$this->visitor_data = array_merge( $data, array( 'id' => $visitor_id ) );
		}

		return $visitor_id;
	}

	/**
	 * Update last seen timestamp for visitor
	 *
	 * @param int $visitor_id Visitor ID.
	 *
	 * @return void
	 */
	private function update_last_seen( int $visitor_id ): void {
		CTA_Database::update(
			CTA_Database::table( self::TABLE_VISITORS ),
			array(
				'last_seen'  => current_time( 'mysql' ),
			),
			array( 'id' => $visitor_id )
		);

		// Increment visit count
		CTA_Database::increment(
			CTA_Database::table( self::TABLE_VISITORS ),
			'visits',
			array( 'id' => $visitor_id )
		);
	}

	/**
	 * Set visitor cookie
	 *
	 * @return void
	 */
	private function set_visitor_cookie(): void {
		if ( ! $this->visitor_id ) {
			return;
		}

		// Only set cookie if not already set or different
		if ( ! isset( $_COOKIE[ self::COOKIE_NAME ] ) || (int) $_COOKIE[ self::COOKIE_NAME ] !== $this->visitor_id ) {
			setcookie(
				self::COOKIE_NAME,
				(string) $this->visitor_id,
				time() + self::COOKIE_DURATION,
				COOKIEPATH,
				COOKIE_DOMAIN,
				is_ssl(),
				true // HTTP only
			);
		}
	}

	/**
	 * Handle user login - merge cookied visitor with WP user
	 *
	 * @param string  $user_login Username.
	 * @param WP_User $user User object.
	 *
	 * @return void
	 */
	public function on_user_login( string $user_login, WP_User $user ): void {
		$user_id = $user->ID;

		// Check if there's a cookied visitor to merge
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			$cookie_visitor_id = (int) $_COOKIE[ self::COOKIE_NAME ];
			$this->merge_visitor_with_wp_user( $cookie_visitor_id, $user_id );
		}
	}

	/**
	 * Handle user registration
	 *
	 * @param int $user_id WordPress user ID.
	 *
	 * @return void
	 */
	public function on_user_register( int $user_id ): void {
		// Same logic as login
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			$cookie_visitor_id = (int) $_COOKIE[ self::COOKIE_NAME ];
			$this->merge_visitor_with_wp_user( $cookie_visitor_id, $user_id );
		}
	}

	/**
	 * Merge cookied visitor record with WordPress user ID
	 *
	 * @param int $visitor_id Visitor ID from cookie.
	 * @param int $wp_user_id WordPress user ID.
	 *
	 * @return void
	 */
	private function merge_visitor_with_wp_user( int $visitor_id, int $wp_user_id ): void {
		// Check if WP user already has a visitor record
		$existing_visitor = CTA_Database::get_row(
			CTA_Database::table( self::TABLE_VISITORS ),
			array( 'wp_user_id' => $wp_user_id )
		);

		if ( $existing_visitor ) {
			// WP user already has visitor record - merge the cookied visitor into it
			$this->merge_visitors( $visitor_id, (int) $existing_visitor['id'] );
		} else {
			// Simply associate the cookied visitor with the WP user
			CTA_Database::update(
				CTA_Database::table( self::TABLE_VISITORS ),
				array( 'wp_user_id' => $wp_user_id ),
				array( 'id' => $visitor_id )
			);
		}
	}

	/**
	 * Merge two visitor records (transfer all events from old to new)
	 *
	 * @param int $old_visitor_id Visitor ID to merge from.
	 * @param int $new_visitor_id Visitor ID to merge into.
	 *
	 * @return void
	 */
	private function merge_visitors( int $old_visitor_id, int $new_visitor_id ): void {
		global $wpdb;

		// Update all events to point to the new visitor
		$wpdb->update(
			CTA_Database::table( CTA_Database::TABLE_EVENTS ),
			array( 'visitor_id' => $new_visitor_id ),
			array( 'visitor_id' => $old_visitor_id ),
			array( '%d' ),
			array( '%d' )
		);

		// Delete the old visitor record
		CTA_Database::delete(
			CTA_Database::table( self::TABLE_VISITORS ),
			array( 'id' => $old_visitor_id )
		);
	}

	/**
	 * Get client IP address
	 *
	 * @return string
	 */
	private function get_client_ip(): string {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return sanitize_text_field( $ip );
	}

	/**
	 * Get user agent
	 *
	 * @return string
	 */
	private function get_user_agent(): string {
		return isset( $_SERVER['HTTP_USER_AGENT'] )
			? substr( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ), 0, 512 )
			: '';
	}

	/**
	 * Get geographic data from IP address
	 *
	 * Basic implementation - can be enhanced with IP geolocation service
	 *
	 * @param string $ip IP address.
	 *
	 * @return array
	 */
	private function get_geo_data( string $ip ): array {
		// TODO: Integrate with IP geolocation service (e.g., MaxMind, ipapi.co)
		// For now, return empty array
		return array();
	}

	/**
	 * Get visitor ID for analytics tracking (JavaScript-callable via AJAX)
	 *
	 * @return array
	 */
	public static function get_visitor_id_for_js(): array {
		$instance   = self::get_instance();
		$visitor_id = $instance->get_visitor_id();

		return array(
			'visitor_id' => $visitor_id,
			'cookie_name' => self::COOKIE_NAME,
			'cookie_duration' => self::COOKIE_DURATION,
		);
	}

	/**
	 * Create visitors table
	 *
	 * @return void
	 */
	public static function create_table(): void {
		global $wpdb;

		$table           = CTA_Database::table( self::TABLE_VISITORS );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			wp_user_id bigint(20) unsigned DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			user_agent varchar(512) DEFAULT NULL,
			country varchar(2) DEFAULT NULL,
			region varchar(100) DEFAULT NULL,
			city varchar(100) DEFAULT NULL,
			first_seen datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			last_seen datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			visits int unsigned NOT NULL DEFAULT 1,
			meta_json longtext DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY wp_user_id (wp_user_id),
			KEY ip_address (ip_address),
			KEY first_seen (first_seen),
			KEY last_seen (last_seen)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Migrate existing visitor_id data from events table
	 *
	 * This creates visitor records for existing string-based visitor IDs
	 *
	 * @return array Migration result
	 */
	public static function migrate_existing_visitors(): array {
		global $wpdb;

		$events_table = CTA_Database::table( CTA_Database::TABLE_EVENTS );
		$visitors_table = CTA_Database::table( self::TABLE_VISITORS );

		// Get distinct visitor IDs from events table
		$existing_visitor_ids = $wpdb->get_col(
			"SELECT DISTINCT visitor_id FROM {$events_table} WHERE visitor_id IS NOT NULL AND visitor_id != ''"
		);

		$migrated = 0;
		$skipped = 0;

		foreach ( $existing_visitor_ids as $old_visitor_id ) {
			// Get first event for this visitor to extract metadata
			$first_event = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$events_table} WHERE visitor_id = %s ORDER BY occurred_at ASC LIMIT 1",
					$old_visitor_id
				),
				ARRAY_A
			);

			if ( ! $first_event ) {
				$skipped++;
				continue;
			}

			// Create visitor record
			$visitor_data = array(
				'wp_user_id' => $first_event['user_id'] ?: null,
				'ip_address' => $first_event['ip_address'],
				'user_agent' => $first_event['user_agent'],
				'first_seen' => $first_event['occurred_at'],
				'last_seen'  => current_time( 'mysql' ),
				'visits'     => 1,
			);

			$new_visitor_id = CTA_Database::insert( $visitors_table, $visitor_data );

			if ( $new_visitor_id ) {
				// Update all events with this old visitor_id to use the new numeric ID
				$wpdb->update(
					$events_table,
					array( 'visitor_id' => $new_visitor_id ),
					array( 'visitor_id' => $old_visitor_id ),
					array( '%d' ),
					array( '%s' )
				);
				$migrated++;
			} else {
				$skipped++;
			}
		}

		return array(
			'migrated' => $migrated,
			'skipped'  => $skipped,
			'total'    => count( $existing_visitor_ids ),
		);
	}
}
