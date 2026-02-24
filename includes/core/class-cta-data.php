<?php
/**
 * Data Access Handler
 *
 * Handles core data access operations across CTA Manager repositories.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Data {

	use CTA_Singleton;

	/**
	 * Settings repository instance (lazy-loaded)
	 *
	 * @var CTA_Settings_Repository|null
	 */
	private ?CTA_Settings_Repository $settings_repo = null;

	/**
	 * CTA repository instance (lazy-loaded)
	 *
	 * @var CTA_Repository|null
	 */
	private ?CTA_Repository $cta_repo = null;

	/**
	 * Get the settings repository instance
	 *
	 * @return CTA_Settings_Repository
	 */
	private function get_settings_repo(): CTA_Settings_Repository {
		if ( $this->settings_repo === null ) {
			$this->settings_repo = CTA_Settings_Repository::get_instance();
		}
		return $this->settings_repo;
	}

	/**
	 * Get the CTA repository instance
	 *
	 * @return CTA_Repository
	 */
	private function get_cta_repo(): CTA_Repository {
		if ( $this->cta_repo === null ) {
			$this->cta_repo = CTA_Repository::get_instance();
		}
		return $this->cta_repo;
	}

	/**
	 * Get default settings (section-based nested structure)
	 *
	 * @return array
	 */
	private function get_default_settings_array(): array {
		$defaults = [
			'analytics'       => [
				'enabled'   => true,
				'retention' => '7',
			],
			'custom_css'      => [
				'css' => '',
			],
			'custom_icons'    => [
				'icons' => [],
			],
			'performance'     => [
				'load_scripts_footer' => false,
			],
			'data_management' => [
				'delete_on_uninstall' => true,
			],
			'debug'          => [
				'enabled' => false,
			],
		];

		/**
		 * Filter the default settings array.
		 *
		 * Allows extensions to add additional default settings.
		 *
		 * @param array $defaults Default settings.
		 */
		return apply_filters( 'cta_default_settings_array', $defaults );
	}

	/**
	 * Get default CTA structure (with i18n support)
	 *
	 * @return array
	 */
	private function get_default_cta_array(): array {
		$defaults = [
			'id'             => 0,
			'name'           => '',
			'status'         => 'published',
			'schedule_start' => '',
			'schedule_end'   => '',
			'trashed_at'     => null,
			'title'          => '',
			'tagline'        => '',
			'body_text'      => '',
			'type'           => 'phone',
			'layout'         => 'button',
			'phone_number'   => '',
			'email_to'       => '',
			'link_url'       => '',
			'link_target'    => '_self',
			'link_target_new_tab' => false,
			'button_text'    => __( 'Call Now', 'cta-manager' ),
			'icon'           => 'none',
			'button_animation' => 'none',
			'icon_animation' => 'none',
			'wrapper_id'     => '',
			'wrapper_classes' => '',
			'cta_html_id'    => '',
			'cta_classes'    => '',
			'data_attributes' => [],
			'blacklist_urls' => [],
			'visibility'     => 'all_devices',
			'show_icon'      => false,
			'enabled'        => true,
			'color'          => '#3b82f6',
			'created_at'     => '',
			'updated_at'     => '',
			'first_active_at' => '',
			// Text Formatting
			'title_font_size'     => '24px',
			'title_font_family'   => 'inherit',
			'title_font_weight'   => '700',
			'title_color'         => '#1e1e1e',
			'title_alignment'     => 'left',
			'tagline_font_size'   => '14px',
			'tagline_font_family' => 'inherit',
			'tagline_font_weight' => '400',
			'tagline_color'       => '#757575',
			'tagline_alignment'   => 'left',
			'body_font_size'      => '14px',
			'body_font_family'    => 'inherit',
			'body_font_weight'    => '400',
			'body_color'          => '#1e1e1e',
			'body_alignment'      => 'left',
			// Card Styling
			'card_background'     => '#ffffff',
			'card_background_type'=> 'solid',
			'card_gradient'       => '',
			'card_padding_top'    => '24px',
			'card_padding_right'  => '24px',
			'card_padding_bottom' => '24px',
			'card_padding_left'   => '24px',
			'card_padding_linked' => true,
			'card_margin_top'     => '0px',
			'card_margin_right'   => '0px',
			'card_margin_bottom'  => '0px',
			'card_margin_left'    => '0px',
			'card_margin_linked'  => true,
			'card_border_width'   => '1px',
			'card_border_color'   => '#dcdcde',
			'card_border_radius'  => '12px',
			// Button Styling
			'button_background'   => '',
			'button_background_type' => 'solid',
			'button_gradient'     => '',
		];

		/**
		 * Filter the default CTA array structure.
		 *
		 * Allows extensions to add additional default fields.
		 *
		 * @param array $defaults Default CTA fields.
		 */
		return apply_filters( 'cta_default_cta_array', $defaults );
	}

	/**
	 * Get unique color for a CTA based on ID
	 *
	 * @return string
	 */
	private static function get_next_color(): string {
		$colors = [
			'#3b82f6', // blue
			'#f97316', // orange
			'#22c55e', // green
			'#8b5cf6', // purple
			'#ec4899', // pink
			'#06b6d4', // cyan
			'#f59e0b', // amber
			'#ef4444', // red
			'#14b8a6', // teal
			'#d946ef', // fuchsia
		];
		$ctas = self::get_instance()->get_ctas();
		$index = count( $ctas ) % count( $colors );
		return $colors[ $index ];
	}

	/**
	 * @var array Default analytics
	 */
	private array $default_analytics = [
		'total_clicks'   => 0,
		'last_click'     => null,
		'clicks_by_date' => [],
		'ctas'           => [],
	];

	/**
	 * Get all settings (section-based nested structure)
	 *
	 * @return array
	 */
	public function get_settings(): array {
		$defaults = $this->get_default_settings_array();
		$repo     = $this->get_settings_repo();
		$settings = $repo->get( 'general', null );

		if ( $settings !== null && is_array( $settings ) ) {
			return $this->deep_merge( $defaults, $settings );
		}

		return $defaults;
	}

	/**
	 * Get a single setting by dot-notation path
	 *
	 * Supports both nested paths (e.g., 'analytics.enabled') and
	 * section keys (e.g., 'analytics' returns the whole section).
	 *
	 * @param string $path    Setting path (dot-notation supported)
	 * @param mixed  $default Default value
	 *
	 * @return mixed
	 */
	public function get_setting( string $path, mixed $default = null ): mixed {
		$settings = $this->get_settings();
		$parts    = explode( '.', $path );

		$value = $settings;
		foreach ( $parts as $part ) {
			if ( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
				return $default;
			}
			$value = $value[ $part ];
		}

		return $value;
	}

	/**
	 * Update all settings (handles nested structure)
	 *
	 * @param array $settings Settings to update (nested structure)
	 * @param bool  $replace  If true, replace all settings instead of deep-merging (default: false)
	 *
	 * @return bool
	 */
	public function update_settings( array $settings, bool $replace = false ): bool {
		if ( $replace ) {
			return $this->update_settings_raw( $settings );
		}

		$current = $this->get_settings();
		$merged  = $this->deep_merge( $current, $settings );

		return $this->update_settings_raw( $merged );
	}

	/**
	 * Write settings directly to storage (no merging)
	 *
	 * @param array $settings Complete settings array
	 *
	 * @return bool
	 */
	private function update_settings_raw( array $settings ): bool {
		$settings = $this->apply_retention_change_tracking( $settings );
		return $this->get_settings_repo()->set(
			'general',
			$settings,
			CTA_Settings_Repository::GROUP_GENERAL,
			true
		);
	}

	/**
	 * Deep merge arrays recursively
	 *
	 * @param array $defaults Default values
	 * @param array $values   User values to merge
	 *
	 * @return array Merged array
	 */
	private function deep_merge( array $defaults, array $values ): array {
		$result = $defaults;

		foreach ( $values as $key => $value ) {
			if ( is_array( $value ) && isset( $result[ $key ] ) && is_array( $result[ $key ] ) ) {
				$result[ $key ] = $this->deep_merge( $result[ $key ], $value );
			} else {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Apply analytics retention change tracking to avoid premature deletions.
	 *
	 * @param array $settings New settings to persist
	 *
	 * @return array Updated settings with retention tracking metadata
	 */
	private function apply_retention_change_tracking( array $settings ): array {
		if ( empty( $settings['analytics'] ) || ! is_array( $settings['analytics'] ) ) {
			return $settings;
		}

		$current   = $this->get_settings();
		$prev_days = $this->get_cleanup_retention_days( $current );
		$new_days  = $this->get_cleanup_retention_days( $settings );

		$current_retention = $current['analytics']['retention'] ?? '7';
		$new_retention     = $settings['analytics']['retention'] ?? $current_retention;
		$current_custom    = $current['analytics']['retention_custom_days'] ?? null;
		$new_custom        = $settings['analytics']['retention_custom_days'] ?? $current_custom;
		$existing_prev_days = absint( $current['analytics']['retention_previous_days'] ?? 0 );
		$retention_changed = $current_retention !== $new_retention
			|| ( $new_retention === 'custom' && (int) $current_custom !== (int) $new_custom );

		if ( ! $retention_changed ) {
			return $settings;
		}

		$baseline_prev_days = max( $existing_prev_days, $prev_days );

		if ( $baseline_prev_days > 0 && $new_days > 0 && $new_days < $baseline_prev_days ) {
			$now = current_time( 'timestamp', true );
			$settings['analytics']['retention_previous_days'] = $baseline_prev_days;
			if ( empty( $current['analytics']['retention_changed_at'] ) ) {
				$settings['analytics']['retention_changed_at'] = gmdate( 'Y-m-d H:i:s', $now );
			}
			$settings['analytics']['retention_grace_until'] = gmdate(
				'Y-m-d H:i:s',
				$now + ( $baseline_prev_days * DAY_IN_SECONDS )
			);
		} else {
			unset( $settings['analytics']['retention_previous_days'] );
			unset( $settings['analytics']['retention_changed_at'] );
			unset( $settings['analytics']['retention_grace_until'] );
		}

		return $settings;
	}

	/**
	 * Get analytics snapshot (defaults to current retention window)
	 *
	 * @param string|null $start_date Optional start date (Y-m-d)
	 * @param string|null $end_date   Optional end date (Y-m-d)
	 *
	 * @return array
	 */
	public function get_analytics( ?string $start_date = null, ?string $end_date = null ): array {
		$repo = CTA_Events_Repository::get_instance();
		[ $start_date, $end_date ] = $this->clamp_date_range_to_retention( $start_date, $end_date );
		$snapshot = $repo->get_snapshot( $start_date, $end_date );

		return wp_parse_args( $snapshot, $this->default_analytics );
	}

	/**
	 * Record analytics event with CTA context.
	 *
	 * @param array $event {
	 *   @type string $type        Event type: click|impression.
	 *   @type int    $cta_id      CTA ID.
	 *   @type string $cta_title   CTA title.
	 *   @type string $page_url    Page URL.
	 *   @type string $page_title  Page title.
	 * }
	 *
	 * @return bool
	 */
	public function record_analytics_event( array $event ): bool {
		$cta_id = isset( $event['cta_id'] ) ? absint( $event['cta_id'] ) : 0;
		$type   = isset( $event['type'] ) ? sanitize_text_field( $event['type'] ) : 'click';

		if ( ! $cta_id ) {
			return false;
		}

		$repo = CTA_Events_Repository::get_instance();

		$context = [];
		if ( isset( $event['context'] ) && is_array( $event['context'] ) ) {
			$context = $event['context'];
		}
		if ( isset( $event['context_json'] ) && is_array( $event['context_json'] ) ) {
			$context = array_merge( $context, $event['context_json'] );
		}

		$inserted = $repo->insert(
			[
				'event_type'    => $type,
				'cta_id'        => $cta_id,
				'cta_title'     => isset( $event['cta_title'] ) ? sanitize_text_field( $event['cta_title'] ) : '',
				'cta_uuid'      => isset( $event['cta_uuid'] ) ? sanitize_text_field( $event['cta_uuid'] ) : null,
				'page_url'      => isset( $event['page_url'] ) ? esc_url_raw( $event['page_url'] ) : null,
				'page_title'    => isset( $event['page_title'] ) ? sanitize_text_field( $event['page_title'] ) : null,
				'referrer'      => isset( $event['referrer'] ) ? esc_url_raw( $event['referrer'] ) : null,
				'device'        => isset( $event['device'] ) ? sanitize_text_field( $event['device'] ) : null,
				'ip_address'    => isset( $event['ip_address'] ) ? sanitize_text_field( $event['ip_address'] ) : null,
				'user_agent'    => isset( $event['user_agent'] ) ? sanitize_text_field( substr( $event['user_agent'], 0, 512 ) ) : null,
				'session_id'    => isset( $event['session_id'] ) ? sanitize_text_field( $event['session_id'] ) : null,
				'visitor_id'    => isset( $event['visitor_id'] ) && $event['visitor_id'] ? absint( $event['visitor_id'] ) : null,
				'experiment_key'=> isset( $event['experiment_key'] ) ? sanitize_text_field( $event['experiment_key'] ) : null,
				'variant'       => isset( $event['variant'] ) ? sanitize_text_field( $event['variant'] ) : null,
				'value'         => isset( $event['value'] ) ? (float) $event['value'] : null,
				'currency'      => isset( $event['currency'] ) ? sanitize_text_field( $event['currency'] ) : null,
				'context_json'  => $context,
				'occurred_at'   => isset( $event['occurred_at'] ) ? $event['occurred_at'] : current_time( 'mysql' ),
			]
		);

		return $inserted !== false;
	}

	/**
	 * Force cleanup of old analytics data
	 * Can be called manually or via cron job
	 *
	 * @return bool
	 */
	public function cleanup_old_analytics(): bool {
		// Cleanup is handled by cleanup_old_events() for the events table
		return true;
	}

	/**
	 * Cleanup old events from the events table based on retention settings
	 *
	 * Called by cta_cleanup_analytics cron job alongside cleanup_old_analytics().
	 *
	 * @return int Number of events deleted
	 */
	public function cleanup_old_events(): int {
		$settings = $this->get_settings();
		$days     = $this->get_cleanup_retention_days( $settings );

		if ( $days === 0 ) {
			return 0;
		}

		$repo       = CTA_Events_Repository::get_instance();
		$prev_days  = absint( $settings['analytics']['retention_previous_days'] ?? 0 );
		$changed_at = $settings['analytics']['retention_changed_at'] ?? '';

		if ( $prev_days > $days && ! empty( $changed_at ) ) {
			$deleted = 0;
			$deleted += $repo->cleanup_old_events_since( $days, $changed_at );
			$deleted += $repo->cleanup_old_events( $prev_days );
		} else {
			$deleted = $repo->cleanup_old_events( $days );
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && $deleted > 0 ) {
			error_log( sprintf( 'CTA Manager: Cleaned up %d old analytics events (retention: %d days)', $deleted, $days ) );
		}

		return $deleted;
	}

	/**
	 * Get all CTAs
	 *
	 * @return array
	 */
	public function get_ctas(): array {
		return $this->get_cta_repo()->get_all();
	}

	/**
	 * Get a single CTA by ID
	 *
	 * @param int $id CTA ID
	 *
	 * @return array|null
	 */
	public function get_cta( int $id ): ?array {
		$cta = $this->get_cta_repo()->get( $id );
		if ( $cta !== null ) {
			return wp_parse_args( $cta, $this->get_default_cta_array() );
		}

		return null;
	}

	/**
	 * Get CTA by name or slug
	 *
	 * @param string $name CTA name or slug
	 *
	 * @return array|null
	 */
	public function get_cta_by_name( string $name ): ?array {
		// Try by slug first (table uses slugs)
		$slug = sanitize_title( $name );
		$cta = $this->get_cta_repo()->get_by_slug( $slug );
		if ( $cta !== null ) {
			return wp_parse_args( $cta, $this->get_default_cta_array() );
		}

		// Try exact name match from all CTAs
		$all_ctas = $this->get_cta_repo()->get_all();
		foreach ( $all_ctas as $cta ) {
			if ( isset( $cta['name'] ) && $cta['name'] === $name ) {
				return wp_parse_args( $cta, $this->get_default_cta_array() );
			}
		}

		return null;
	}

	/**
	 * Get default/first CTA
	 *
	 * @return array|null
	 */
	public function get_default_cta(): ?array {
		$ctas = $this->get_ctas();

		if ( empty( $ctas ) ) {
			return null;
		}

		return wp_parse_args( $ctas[0], $this->get_default_cta_array() );
	}

	/**
	 * Get total CTA count (excludes demo CTAs for limit enforcement)
	 *
	 * @return int
	 */
	public function get_cta_count(): int {
		return $this->get_cta_repo()->count( [ 'exclude_demo' => true ] );
	}

	/**
	 * Get total CTA count including demo CTAs
	 *
	 * @return int
	 */
	public function get_total_cta_count(): int {
		return $this->get_cta_repo()->count();
	}

	/**
	 * Check if can add more CTAs
	 *
	 * @return bool
	 */
	public function can_add_cta(): bool {
		return apply_filters( 'cta_can_add_cta', true );
	}


	/**
	 * Create a new CTA
	 *
	 * @param array $data CTA data
	 *
	 * @return int|false CTA ID on success, false on failure
	 */
	public function create_cta( array $data ) {
		if ( ! $this->can_add_cta() ) {
			return false;
		}

		$now = current_time( 'mysql' );

		$cta = wp_parse_args( $data, $this->get_default_cta_array() );
		$cta['created_at'] = $now;
		$cta['updated_at'] = $now;

		// Auto-assign color if not provided
		if ( empty( $cta['color'] ) || $cta['color'] === '#3b82f6' ) {
			$cta['color'] = self::get_next_color();
		}

		return $this->get_cta_repo()->create( $cta );
	}

	/**
	 * Update a CTA
	 *
	 * @param int   $id CTA ID
	 * @param array $data CTA data
	 *
	 * @return bool
	 */
	public function update_cta( int $id, array $data ): bool {
		return $this->get_cta_repo()->update( $id, $data );
	}

	/**
	 * Delete a CTA (permanent delete)
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool
	 */
	public function delete_cta( int $id ): bool {
		return $this->get_cta_repo()->delete( $id );
	}

	/**
	 * Move a CTA to trash
	 *
	 * Demo CTAs cannot be trashed - they will be permanently deleted instead.
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool|string True on success, 'demo_cta' if demo CTA (should use delete), false on failure
	 */
	public function trash_cta( int $id ): bool|string {
		return $this->get_cta_repo()->trash( $id );
	}

	/**
	 * Restore a CTA from trash
	 *
	 * @param int    $id             CTA ID
	 * @param string $restore_status Status to restore to (default: draft)
	 *
	 * @return bool
	 */
	public function restore_cta_from_trash( int $id, string $restore_status = 'draft' ): bool {
		return $this->get_cta_repo()->restore_from_trash( $id, $restore_status );
	}

	/**
	 * Archive a CTA
	 *
	 * Demo CTAs cannot be archived - they should be permanently deleted instead.
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool|string True on success, 'demo_cta' if demo CTA (should use delete), false on failure
	 */
	public function archive_cta( int $id ): bool|string {
		return $this->get_cta_repo()->archive( $id );
	}

	/**
	 * Get all trashed CTAs
	 *
	 * @return array
	 */
	public function get_trashed_ctas(): array {
		return $this->get_cta_repo()->get_trashed();
	}

	/**
	 * Get active CTAs (Published + Scheduled)
	 *
	 * @return array
	 */
	public function get_active_ctas(): array {
		return $this->get_cta_repo()->get_all( [ 'status' => 'active' ] );
	}

	/**
	 * Count trashed CTAs
	 *
	 * @return int
	 */
	public function get_trashed_cta_count(): int {
		return $this->get_cta_repo()->count_trashed();
	}

	/**
	 * Empty all trashed CTAs (permanent delete)
	 *
	 * @return int Number of CTAs permanently deleted
	 */
	public function empty_trash(): int {
		return $this->get_cta_repo()->empty_trash();
	}

	/**
	 * Empty trashed CTAs that have exceeded retention period
	 *
	 * @param int $retention_days Days before permanent deletion (default: 30)
	 *
	 * @return int Number of CTAs permanently deleted
	 */
	public function empty_expired_trash( int $retention_days = 30 ): int {
		return $this->get_cta_repo()->empty_expired_trash( $retention_days );
	}

	/**
	 * Check if a CTA is a demo CTA
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool
	 */
	public function is_demo_cta( int $id ): bool {
		return $this->get_cta_repo()->is_demo_cta( $id );
	}

	/**
	 * Create backup of current settings
	 *
	 * Creates a full backup including all tables.
	 *
	 * @return bool
	 */
	public function create_backup(): bool {
		$backup              = $this->export_all();
		$backup['timestamp'] = current_time( 'mysql' );
		$backup['is_backup'] = true;

		return $this->get_settings_repo()->set(
			'backup',
			$backup,
			CTA_Settings_Repository::GROUP_BACKUP,
			false
		);
	}

	/**
	 * Get the last backup
	 *
	 * @return array|null
	 */
	public function get_backup(): ?array {
		$backup = $this->get_settings_repo()->get( 'backup', null );

		return ( $backup !== null && is_array( $backup ) ) ? $backup : null;
	}

	/**
	 * Remove empty values from array recursively
	 *
	 * Removes null, empty strings, and empty arrays.
	 * Preserves: false, 0, '0' (legitimate values)
	 *
	 * @param array $data Data to clean
	 *
	 * @return array Cleaned data
	 */
	private function remove_empty_values( array $data ): array {
		$result = [];

		foreach ( $data as $key => $value ) {
			// Skip truly empty values (but keep 0, false, '0')
			if ( $value === null || $value === '' || $value === [] ) {
				continue;
			}

			// Recursively clean arrays
			if ( is_array( $value ) ) {
				$cleaned = $this->remove_empty_values( $value );
				// Only include if the cleaned array is not empty
				if ( ! empty( $cleaned ) ) {
					$result[ $key ] = $cleaned;
				}
			} else {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Sanitize settings for export
	 *
	 * Removes internal tracking fields that shouldn't be exported.
	 *
	 * @param array $settings Settings array to sanitize.
	 *
	 * @return array Sanitized settings.
	 */
	private function sanitize_settings_for_export( array $settings ): array {
		// Remove internal retention tracking fields (Pro-only internal state)
		$internal_fields = [
			'retention_custom_days',
			'retention_previous_days',
			'retention_grace_until',
			'retention_changed_at',
		];

		if ( isset( $settings['analytics'] ) && is_array( $settings['analytics'] ) ) {
			foreach ( $internal_fields as $field ) {
				unset( $settings['analytics'][ $field ] );
			}
		}

		/**
		 * Filter settings before export.
		 *
		 * Allows extensions to add back Pro fields if needed.
		 *
		 * @param array $settings Sanitized settings.
		 */
		return apply_filters( 'cta_sanitize_settings_for_export', $settings );
	}

	/**
	 * Sanitize settings for import
	 *
	 * Enforces free version limits to prevent Pro feature manipulation via JSON.
	 * Pro version can bypass via filter.
	 *
	 * @param array $settings Settings array from import data.
	 *
	 * @return array Sanitized settings safe for import.
	 */
	private function sanitize_settings_for_import( array $settings ): array {
		/**
		 * Filter to allow Pro to bypass import sanitization.
		 *
		 * @param bool $is_pro Whether Pro is active.
		 */
		$is_pro = apply_filters( 'cta_is_pro_active', false );

		if ( $is_pro ) {
			return $settings;
		}

		// Free version: enforce allowed values only
		$sanitized = [];

		// Analytics - only allow free version options
		if ( isset( $settings['analytics'] ) && is_array( $settings['analytics'] ) ) {
			$sanitized['analytics'] = [];

			// enabled: can be true or false
			if ( isset( $settings['analytics']['enabled'] ) ) {
				$sanitized['analytics']['enabled'] = (bool) $settings['analytics']['enabled'];
			}

			// retention: can only be "7" or "1"
			if ( isset( $settings['analytics']['retention'] ) ) {
				$retention = $settings['analytics']['retention'];
				if ( in_array( $retention, [ '1', '7', 1, 7 ], true ) ) {
					$sanitized['analytics']['retention'] = (string) $retention;
				} else {
					$sanitized['analytics']['retention'] = '7';
				}
			}
		}

		// Performance - force load_scripts_footer to false
		$sanitized['performance'] = [
			'load_scripts_footer' => false,
		];

		// Data management - force delete_on_uninstall to true
		$sanitized['data_management'] = [
			'delete_on_uninstall' => true,
		];

		// Debug - allow the value if set
		if ( isset( $settings['debug'] ) && is_array( $settings['debug'] ) ) {
			$sanitized['debug'] = [
				'enabled' => ! empty( $settings['debug']['enabled'] ),
			];
		}

		return $sanitized;
	}

	/**
	 * Sanitize a single CTA for import
	 *
	 * Enforces free version limits to prevent Pro feature manipulation via JSON.
	 * Pro version can bypass via filter.
	 *
	 * @param array $cta CTA data from import.
	 *
	 * @return array Sanitized CTA safe for import.
	 */
	private function sanitize_cta_for_import( array $cta ): array {
		$is_pro = apply_filters( 'cta_is_pro_active', false );

		if ( $is_pro ) {
			return $cta;
		}

		// Free version: enforce allowed values only

		// type: can only be "phone", "link", or "email"
		$allowed_types = [ 'phone', 'link', 'email' ];
		if ( isset( $cta['type'] ) && ! in_array( $cta['type'], $allowed_types, true ) ) {
			$cta['type'] = 'phone';
		}

		// layout: can only be "button" in free version
		if ( isset( $cta['layout'] ) && $cta['layout'] !== 'button' ) {
			$cta['layout'] = 'button';
		}

		// Remove Pro-only fields entirely
		unset( $cta['slide_in_settings'] );

		return $cta;
	}

	/**
	 * Sanitize a single CTA for export
	 *
	 * Removes Pro-only fields when Pro is not active.
	 *
	 * @param array $cta CTA data.
	 *
	 * @return array Sanitized CTA for export.
	 */
	private function sanitize_cta_for_export( array $cta ): array {
		$is_pro = apply_filters( 'cta_is_pro_active', false );

		if ( $is_pro ) {
			return $cta;
		}

		// Free version: remove Pro-only fields from export
		unset( $cta['slide_in_settings'] );

		return $cta;
	}

	/**
	 * Sanitize all CTAs for export
	 *
	 * @param array $ctas Array of CTAs.
	 *
	 * @return array Sanitized CTAs.
	 */
	private function sanitize_ctas_for_export( array $ctas ): array {
		return array_map( [ $this, 'sanitize_cta_for_export' ], $ctas );
	}

	/**
	 * Export all data
	 *
	 * Exports all CTA Manager tables.
	 * Extensions can add additional data via the cta_export_data filter.
	 *
	 * @return array
	 */
	public function export_all(): array {
		$analytics = $this->get_analytics();

		// Include raw events for re-import capability (export all events)
		$events_repo = CTA_Events_Repository::get_instance();
		$raw_events  = [];
		$page        = 1;
		$per_page    = 5000;
		do {
			$events_result = $events_repo->get_events( [], $page, $per_page );
			$batch         = $events_result['events'] ?? [];
			if ( ! empty( $batch ) ) {
				$raw_events = array_merge( $raw_events, $batch );
			}
			$total = (int) ( $events_result['total'] ?? 0 );
			$page++;
		} while ( count( $raw_events ) < $total && ! empty( $batch ) );

		$analytics['events'] = array_map( function( $event ) {
			$context_json = [];
			if ( isset( $event['context_json'] ) ) {
				$context_json = is_array( $event['context_json'] )
					? $event['context_json']
					: CTA_Database::decode_json( (string) $event['context_json'] );
			}
			$meta_json = [];
			if ( isset( $event['meta_json'] ) ) {
				$meta_json = is_array( $event['meta_json'] )
					? $event['meta_json']
					: CTA_Database::decode_json( (string) $event['meta_json'] );
			}

			return [
				'event_type'     => $event['event_type'] ?? 'impression',
				'cta_id'         => (int) ( $event['cta_id'] ?? 0 ),
				'cta_title'      => $event['cta_title'] ?? '',
				'cta_uuid'       => $event['cta_uuid'] ?? '',
				'page_url'       => $event['page_url'] ?? '',
				'page_title'     => $event['page_title'] ?? '',
				'referrer'       => $event['referrer'] ?? '',
				'device'         => $event['device'] ?? 'desktop',
				'session_id'     => $event['session_id'] ?? '',
				'visitor_id'     => $event['visitor_id'] ?? '',
				'user_id'        => $event['user_id'] ?? '',
				'experiment_key' => $event['experiment_key'] ?? '',
				'variant'        => $event['variant'] ?? '',
				'value'          => $event['value'] ?? '',
				'currency'       => $event['currency'] ?? '',
				'ip_address'     => $event['ip_address'] ?? '',
				'user_agent'     => $event['user_agent'] ?? '',
				'context_json'   => $context_json,
				'meta_json'      => $meta_json,
				'occurred_at'    => $event['occurred_at'] ?? '',
			];
		}, $raw_events );

		// Get visitors
		$visitors = $this->export_visitors();

		// Get settings and remove internal tracking fields
		$settings = $this->get_settings();
		$settings = $this->sanitize_settings_for_export( $settings );

		// Get CTAs and remove Pro-only fields if Pro not active
		$ctas = $this->get_ctas();
		$ctas = $this->sanitize_ctas_for_export( $ctas );

		$export = [
			'plugin'       => 'cta-manager',
			'version'      => CTA_VERSION,
			'db_version'   => defined( 'CTA_DB_VERSION' ) ? CTA_DB_VERSION : '1.0.0',
			'exported'     => current_time( 'mysql' ),
			'site_url'     => get_site_url(),
			'settings'     => $settings,
			'ctas'         => $ctas,
			'visitors'     => $visitors,
			'analytics'    => $analytics,
			'custom_icons' => $this->get_custom_icons(),
		];

		// Export notifications
		$export['notifications'] = $this->export_notifications();

		/**
		 * Filter export data to allow extensions to add additional tables.
		 *
		 * @param array $export The export data array.
		 */
		$export = apply_filters( 'cta_export_data', $export );

		// Remove all empty keys from the export
		$export = $this->remove_empty_values( $export );

		// Ensure toggleable keys always exist (even if empty) for UI filtering
		$toggleable_keys = [ 'ctas', 'analytics', 'settings', 'notifications' ];
		foreach ( $toggleable_keys as $key ) {
			if ( ! isset( $export[ $key ] ) ) {
				$export[ $key ] = [];
			}
		}

		return $export;
	}

	/**
	 * Export notifications table
	 *
	 * @return array
	 */
	private function export_notifications(): array {
		if ( ! class_exists( 'CTA_Notifications' ) ) {
			return [];
		}

		$table = CTA_Notifications::get_table_name();
		if ( ! CTA_Database::table_exists( $table ) ) {
			return [];
		}

		$rows = CTA_Database::query( "SELECT * FROM {$table}" );
		if ( empty( $rows ) ) {
			return [];
		}

		return array_map( function( $row ) {
			$row['actions'] = ! empty( $row['actions'] ) ? json_decode( $row['actions'], true ) : [];
			return $row;
		}, $rows );
	}

	/**
	 * Export visitors from the visitors table
	 *
	 * @since 1.3.0
	 *
	 * @return array Array of visitor records
	 */
	private function export_visitors(): array {
		$table = CTA_Database::table( CTA_Database::TABLE_VISITORS );
		if ( ! CTA_Database::table_exists( $table ) ) {
			return [];
		}

		$rows = CTA_Database::query( "SELECT * FROM {$table}" );
		if ( empty( $rows ) ) {
			return [];
		}

		return array_map( function( $row ) {
			return [
				'id'          => (int) ( $row['id'] ?? 0 ),
				'wp_user_id'  => isset( $row['wp_user_id'] ) ? (int) $row['wp_user_id'] : null,
				'ip_address'  => $row['ip_address'] ?? null,
				'user_agent'  => $row['user_agent'] ?? null,
				'country'     => $row['country'] ?? null,
				'region'      => $row['region'] ?? null,
				'city'        => $row['city'] ?? null,
				'first_seen'  => $row['first_seen'] ?? '',
				'last_seen'   => $row['last_seen'] ?? '',
				'visit_count' => (int) ( $row['visits'] ?? 1 ),
				'meta_json'   => ! empty( $row['meta_json'] ) ? json_decode( $row['meta_json'], true ) : null,
			];
		}, $rows );
	}


	/**
	 * Get onboarding data
	 *
	 * @return array
	 */
	public function get_onboarding(): array {
		$defaults = [
			'completed'    => false,
			'dismissed'    => false,
			'current_step' => 1,
		];

		$onboarding = $this->get_settings_repo()->get( 'onboarding', null );

		if ( $onboarding !== null && is_array( $onboarding ) ) {
			return wp_parse_args( $onboarding, $defaults );
		}

		return $defaults;
	}

	/**
	 * Update onboarding data
	 *
	 * @param array $data Onboarding data to update
	 *
	 * @return bool
	 */
	public function update_onboarding( array $data ): bool {
		$merged = wp_parse_args( $data, $this->get_onboarding() );

		return $this->get_settings_repo()->set(
			'onboarding',
			$merged,
			CTA_Settings_Repository::GROUP_ONBOARDING,
			true
		);
	}

	/**
	 * Import data
	 *
	 * Imports all CTA Manager data.
	 * Extensions can handle additional data via the cta_import_data action.
	 *
	 * @param array $data          Data to import
	 * @param bool  $merge         Whether to merge with existing data
	 * @param bool  $create_backup Whether to create a backup before import
	 *
	 * @return bool
	 */
	public function import_all( array $data, bool $merge = false, bool $create_backup = true ): bool {
		if ( $create_backup ) {
			$this->create_backup();
		}

		$success = true;

		// Track ID mappings for related data
		$cta_id_map = [];

		// Import settings (sanitize to prevent Pro feature manipulation)
		if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
			$settings_to_import = $this->sanitize_settings_for_import( $data['settings'] );
			if ( $merge ) {
				$current = $this->get_settings();
				$merged  = wp_parse_args( $settings_to_import, $current );
				$success = $success && $this->update_settings( $merged );
			} else {
				$success = $success && $this->update_settings( $settings_to_import );
			}
		}

		// Import CTAs
		if ( isset( $data['ctas'] ) && is_array( $data['ctas'] ) ) {
			$cta_id_map = $this->import_ctas( $data['ctas'], $merge );
		}

		// Import visitors (must happen before analytics events since events reference visitors)
		if ( isset( $data['visitors'] ) && is_array( $data['visitors'] ) ) {
			$this->import_visitors( $data['visitors'], $merge );
		}

		// Import analytics events
		if ( isset( $data['analytics']['events'] ) && is_array( $data['analytics']['events'] ) ) {
			$this->import_analytics_events( $data['analytics']['events'], $cta_id_map, $merge );
		}

		// Import custom icons
		if ( isset( $data['custom_icons'] ) && is_array( $data['custom_icons'] ) ) {
			if ( $merge ) {
				$current_icons = $this->get_custom_icons();
				$merged_icons  = array_merge( $current_icons, $data['custom_icons'] );
				$unique_icons  = [];
				foreach ( $merged_icons as $icon ) {
					if ( isset( $icon['name'] ) ) {
						$unique_icons[ $icon['name'] ] = $icon;
					}
				}
				$success = $success && $this->save_custom_icons( array_values( $unique_icons ) );
			} else {
				$success = $success && $this->save_custom_icons( $data['custom_icons'] );
			}
		}

		// Import notifications
		if ( isset( $data['notifications'] ) && is_array( $data['notifications'] ) ) {
			$this->import_notifications( $data['notifications'], $merge );
		}

		/**
		 * Action to allow extensions to import additional data.
		 *
		 * @param array $data       The import data array.
		 * @param bool  $merge      Whether to merge with existing data.
		 * @param array $cta_id_map Mapping of old CTA IDs to new IDs.
		 */
		do_action( 'cta_import_data', $data, $merge, $cta_id_map );

		return $success;
	}

	/**
	 * Import CTAs and return ID mapping
	 *
	 * @param array $ctas  CTAs to import
	 * @param bool  $merge Whether to merge
	 *
	 * @return array ID mapping (old_id => new_id)
	 */
	private function import_ctas( array $ctas, bool $merge ): array {
		$repo       = $this->get_cta_repo();
		$cta_id_map = [];

		if ( ! $merge ) {
			$repo->truncate();
		}

		foreach ( $ctas as $cta ) {
			// Sanitize CTA to prevent Pro feature manipulation
			$cta    = $this->sanitize_cta_for_import( $cta );
			$old_id = isset( $cta['id'] ) ? (int) $cta['id'] : 0;

			if ( $merge && $old_id && $repo->get( $old_id ) !== null ) {
				$repo->update( $old_id, $cta );
				$cta_id_map[ $old_id ] = $old_id;
			} else {
				unset( $cta['id'] );
				$new_id = $repo->create( $cta );
				if ( $new_id && $old_id ) {
					$cta_id_map[ $old_id ] = $new_id;
				}
			}
		}

		return $cta_id_map;
	}

	/**
	 * Import visitors
	 *
	 * @param array $visitors Visitors to import
	 * @param bool  $merge    Whether to merge
	 */
	private function import_visitors( array $visitors, bool $merge ): void {
		$table = CTA_Database::table( CTA_Database::TABLE_VISITORS );
		if ( ! CTA_Database::table_exists( $table ) ) {
			return;
		}

		if ( ! $merge ) {
			CTA_Database::truncate( $table );
		}

		global $wpdb;
		foreach ( $visitors as $visitor ) {
			$first_seen = isset( $visitor['first_seen'] ) ? $this->parse_relative_datetime( $visitor['first_seen'] ) : current_time( 'mysql' );
			$last_seen  = isset( $visitor['last_seen'] ) ? $this->parse_relative_datetime( $visitor['last_seen'] ) : current_time( 'mysql' );

			$visitor_data = [
				'wp_user_id'  => isset( $visitor['wp_user_id'] ) ? absint( $visitor['wp_user_id'] ) : null,
				'ip_address'  => isset( $visitor['ip_address'] ) ? sanitize_text_field( $visitor['ip_address'] ) : null,
				'user_agent'  => isset( $visitor['user_agent'] ) ? sanitize_text_field( substr( $visitor['user_agent'], 0, 512 ) ) : null,
				'country'     => isset( $visitor['country'] ) ? sanitize_text_field( $visitor['country'] ) : null,
				'region'      => isset( $visitor['region'] ) ? sanitize_text_field( $visitor['region'] ) : null,
				'city'        => isset( $visitor['city'] ) ? sanitize_text_field( $visitor['city'] ) : null,
				'first_seen'  => $first_seen,
				'last_seen'   => $last_seen,
				'visits'      => isset( $visitor['visit_count'] ) ? absint( $visitor['visit_count'] ) : 1,
				'meta_json'   => isset( $visitor['meta_json'] ) && is_array( $visitor['meta_json'] ) ? wp_json_encode( $visitor['meta_json'] ) : null,
			];

			// In merge mode, check if visitor with same id exists and update
			// In replace mode, just insert all visitors
			if ( $merge && isset( $visitor['id'] ) ) {
				$visitor_id = absint( $visitor['id'] );
				$existing   = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d", $visitor_id ) );

				if ( $existing ) {
					$wpdb->update( $table, $visitor_data, [ 'id' => $visitor_id ] );
					continue;
				} else {
					$visitor_data['id'] = $visitor_id;
				}
			} elseif ( isset( $visitor['id'] ) ) {
				$visitor_data['id'] = absint( $visitor['id'] );
			}

			$wpdb->insert( $table, $visitor_data );
		}
	}

	/**
	 * Import analytics events with CTA ID mapping
	 *
	 * @param array $events     Events to import
	 * @param array $cta_id_map CTA ID mapping
	 * @param bool  $merge      Whether to merge
	 */
	private function import_analytics_events( array $events, array $cta_id_map, bool $merge ): void {
		$events_repo = CTA_Events_Repository::get_instance();

		// In replace mode, truncate all events since CTAs were also truncated
		// In merge mode, we keep existing events and just add new ones
		if ( ! $merge ) {
			$events_repo->truncate();
		}

		$events_to_import = [];
		foreach ( $events as $event ) {
			$old_cta_id = isset( $event['cta_id'] ) ? (int) $event['cta_id'] : 0;
			$new_cta_id = $cta_id_map[ $old_cta_id ] ?? $old_cta_id;

			if ( ! $new_cta_id ) {
				continue;
			}

			$occurred_at = $this->parse_relative_datetime( $event['occurred_at'] ?? '' );
			$page_url    = $event['page_url'] ?? '';
			if ( $page_url && strpos( $page_url, 'http' ) !== 0 ) {
				$page_url = home_url( $page_url );
			}

			$events_to_import[] = [
				'event_type'     => sanitize_text_field( $event['event_type'] ?? 'impression' ),
				'cta_id'         => $new_cta_id,
				'cta_title'      => sanitize_text_field( $event['cta_title'] ?? '' ),
				'cta_uuid'       => isset( $event['cta_uuid'] ) ? sanitize_text_field( $event['cta_uuid'] ) : null,
				'page_url'       => esc_url_raw( $page_url ),
				'page_title'     => sanitize_text_field( $event['page_title'] ?? '' ),
				'referrer'       => esc_url_raw( $event['referrer'] ?? '' ),
				'device'         => sanitize_text_field( $event['device'] ?? 'desktop' ),
				'session_id'     => isset( $event['session_id'] ) ? sanitize_text_field( $event['session_id'] ) : null,
				'visitor_id'     => isset( $event['visitor_id'] ) ? absint( $event['visitor_id'] ) : null,
				'user_id'        => isset( $event['user_id'] ) ? absint( $event['user_id'] ) : null,
				'experiment_key' => isset( $event['experiment_key'] ) ? sanitize_text_field( $event['experiment_key'] ) : null,
				'variant'        => isset( $event['variant'] ) ? sanitize_text_field( $event['variant'] ) : null,
				'value'          => isset( $event['value'] ) ? (float) $event['value'] : null,
				'currency'       => isset( $event['currency'] ) ? sanitize_text_field( $event['currency'] ) : null,
				'ip_address'     => isset( $event['ip_address'] ) ? sanitize_text_field( $event['ip_address'] ) : null,
				'user_agent'     => isset( $event['user_agent'] ) ? sanitize_text_field( substr( $event['user_agent'], 0, 512 ) ) : null,
				'context_json'   => isset( $event['context_json'] ) && is_array( $event['context_json'] ) ? $event['context_json'] : null,
				'meta_json'      => isset( $event['meta_json'] ) && is_array( $event['meta_json'] ) ? $event['meta_json'] : null,
				'occurred_at'    => $occurred_at,
			];
		}

		if ( ! empty( $events_to_import ) ) {
			$events_repo->bulk_insert( $events_to_import );
		}
	}

	/**
	 * Import notifications
	 *
	 * @param array $notifications Notifications to import
	 * @param bool  $merge         Whether to merge
	 */
	private function import_notifications( array $notifications, bool $merge ): void {
		if ( ! class_exists( 'CTA_Notifications' ) ) {
			return;
		}

		$table = CTA_Notifications::get_table_name();
		if ( ! CTA_Database::table_exists( $table ) ) {
			return;
		}

		if ( ! $merge ) {
			CTA_Database::truncate( $table );
		}

		foreach ( $notifications as $notification ) {
			$old_id = isset( $notification['id'] ) ? (int) $notification['id'] : 0;
			unset( $notification['id'] );

			$notification['actions'] = isset( $notification['actions'] ) && is_array( $notification['actions'] )
				? wp_json_encode( $notification['actions'] )
				: '[]';

			CTA_Database::insert( $table, $notification );
		}
	}


	/**
	 * Parse relative datetime string to MySQL datetime format
	 *
	 * Supports formats like:
	 * - "-6 days 09:15:00" (relative to today)
	 * - "today 07:00:00" (today at specific time)
	 * - "2026-01-08 12:00:00" (absolute datetime)
	 *
	 * @param string $datetime_str Datetime string to parse
	 *
	 * @return string MySQL datetime format (Y-m-d H:i:s)
	 */
	private function parse_relative_datetime( string $datetime_str ): string {
		$datetime_str = trim( $datetime_str );

		if ( empty( $datetime_str ) ) {
			return current_time( 'mysql' );
		}

		// If it's already a valid datetime, return it
		if ( preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime_str ) ) {
			return $datetime_str;
		}

		// Parse relative formats
		if ( preg_match( '/^(-?\d+\s+days?)\s+(\d{2}:\d{2}:\d{2})$/i', $datetime_str, $matches ) ) {
			// Format: "-6 days 09:15:00"
			$date_part = gmdate( 'Y-m-d', strtotime( $matches[1] ) );
			return $date_part . ' ' . $matches[2];
		}

		if ( preg_match( '/^today\s+(\d{2}:\d{2}:\d{2})$/i', $datetime_str, $matches ) ) {
			// Format: "today 07:00:00"
			return gmdate( 'Y-m-d' ) . ' ' . $matches[1];
		}

		// Try to parse with strtotime as fallback
		$timestamp = strtotime( $datetime_str );
		if ( $timestamp !== false ) {
			return gmdate( 'Y-m-d H:i:s', $timestamp );
		}

		// Default to current time if parsing fails
		return current_time( 'mysql' );
	}

	/**
	 * Get all custom icons
	 *
	 * @return array
	 */
	public function get_custom_icons(): array {
		$settings = $this->get_settings();
		return $settings['custom_icons']['icons'] ?? [];
	}

	/**
	 * Save custom icons to settings
	 *
	 * @param array $icons Icons data to save
	 * @return bool
	 */
	private function save_custom_icons( array $icons ): bool {
		return $this->update_settings( [
			'custom_icons' => [
				'icons' => $icons,
			],
		] );
	}

	/**
	 * Get a single custom icon by ID
	 *
	 * @param string $id Icon ID
	 *
	 * @return array|null
	 */
	public function get_custom_icon( string $id ): ?array {
		$icons = $this->get_custom_icons();

		foreach ( $icons as $icon ) {
			if ( isset( $icon['id'] ) && $icon['id'] === $id ) {
				return $icon;
			}
		}

		return null;
	}

	/**
	 * Validate and sanitize SVG content
	 *
	 * @param string $svg Raw SVG content
	 *
	 * @return string|false Sanitized SVG or false if invalid
	 */
	public function sanitize_svg( string $svg ): string|false {
		$svg = trim( $svg );

		// Must start with <svg and end with </svg>
		if ( ! preg_match( '/^<svg[\s>]/i', $svg ) || ! preg_match( '/<\/svg>\s*$/i', $svg ) ) {
			return false;
		}

		// Remove width and height attributes
		$svg = preg_replace( '/\s(width|height)=["\'][^"\']*["\']/i', '', $svg );

		// Remove potentially dangerous elements/attributes
		$dangerous_elements = [ 'script', 'iframe', 'object', 'embed', 'foreignObject', 'use' ];
		foreach ( $dangerous_elements as $element ) {
			$svg = preg_replace( '/<' . $element . '[^>]*>.*?<\/' . $element . '>/is', '', $svg );
			$svg = preg_replace( '/<' . $element . '[^>]*\/>/is', '', $svg );
		}

		// Remove event handlers (onclick, onload, etc.)
		$svg = preg_replace( '/\son\w+=["\'][^"\']*["\']/i', '', $svg );

		// Remove javascript: URLs
		$svg = preg_replace( '/href=["\']javascript:[^"\']*["\']/i', '', $svg );

		// Ensure viewBox exists for proper scaling
		if ( ! preg_match( '/viewBox=/i', $svg ) ) {
			// Try to extract from width/height before they were removed
			$svg = preg_replace( '/<svg/i', '<svg viewBox="0 0 24 24"', $svg, 1 );
		}

		return $svg;
	}

	/**
	 * Add a custom icon
	 *
	 * @param string $name Icon name
	 * @param string $svg SVG content
	 *
	 * @return array|false The new icon data on success, false on failure
	 */
	public function add_custom_icon( string $name, string $svg ): array|false {
		$name = sanitize_text_field( trim( $name ) );

		if ( empty( $name ) ) {
			return false;
		}

		// Sanitize and validate SVG
		$sanitized_svg = $this->sanitize_svg( $svg );
		if ( $sanitized_svg === false ) {
			return false;
		}

		$icons = $this->get_custom_icons();

		// Check for duplicate names
		foreach ( $icons as $icon ) {
			if ( strtolower( $icon['name'] ) === strtolower( $name ) ) {
				return false;
			}
		}

		// Generate unique ID
		$id = 'custom-' . wp_generate_uuid4();

		$new_icon = [
			'id'         => $id,
			'name'       => $name,
			'svg'        => $sanitized_svg,
			'created_at' => current_time( 'mysql' ),
		];

		$icons[] = $new_icon;

		// Sort alphabetically by name
		usort( $icons, function( $a, $b ) {
			return strcasecmp( $a['name'], $b['name'] );
		} );

		if ( $this->save_custom_icons( $icons ) ) {
			return $new_icon;
		}

		return false;
	}

	/**
	 * Update a custom icon
	 *
	 * @param string $id Icon ID
	 * @param array $data Icon data to update
	 *
	 * @return bool
	 */
	public function update_custom_icon( string $id, array $data ): bool {
		$icons = $this->get_custom_icons();
		$found = false;

		foreach ( $icons as $index => $icon ) {
			if ( isset( $icon['id'] ) && $icon['id'] === $id ) {
				// Only allow updating name and svg
				if ( isset( $data['name'] ) ) {
					$icons[ $index ]['name'] = sanitize_text_field( trim( $data['name'] ) );
				}
				if ( isset( $data['svg'] ) ) {
					$sanitized_svg = $this->sanitize_svg( $data['svg'] );
					if ( $sanitized_svg !== false ) {
						$icons[ $index ]['svg'] = $sanitized_svg;
					}
				}
				$icons[ $index ]['updated_at'] = current_time( 'mysql' );
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			return false;
		}

		// Re-sort alphabetically by name
		usort( $icons, function( $a, $b ) {
			return strcasecmp( $a['name'], $b['name'] );
		} );

		return $this->save_custom_icons( $icons );
	}

	/**
	 * Delete a custom icon
	 *
	 * @param string $id Icon ID
	 *
	 * @return bool
	 */
	public function delete_custom_icon( string $id ): bool {
		$icons = $this->get_custom_icons();
		$new_icons = [];

		foreach ( $icons as $icon ) {
			if ( ! isset( $icon['id'] ) || $icon['id'] !== $id ) {
				$new_icons[] = $icon;
			}
		}

		return $this->save_custom_icons( $new_icons );
	}

	/**
	 * Get dashboard statistics for stat cards
	 *
	 * @return array Dashboard stats for display
	 */
	public function get_dashboard_stats(): array {
		$events_repo = CTA_Events_Repository::get_instance();
		$cta_repo    = $this->get_cta_repo();

		// Date ranges
		$today          = gmdate( 'Y-m-d' );
		$window_7_days  = $this->get_reporting_window_days( 7 );
		$window_14_days = $this->get_reporting_window_days( 14 );
		$window_30_days = $this->get_reporting_window_days( 30 );

		$seven_days    = gmdate( 'Y-m-d', strtotime( "-{$window_7_days} days" ) );
		$fourteen_days = gmdate( 'Y-m-d', strtotime( "-{$window_14_days} days" ) );
		$thirty_days   = gmdate( 'Y-m-d', strtotime( "-{$window_30_days} days" ) );

		// Get 7-day snapshot for main stats
		$snapshot = $events_repo->get_snapshot( $seven_days, $today );

		// Count CTAs by status (published = active, regardless of enabled flag)
		$all_ctas       = $cta_repo->get_all();
		$active_ctas    = 0;
		$draft_ctas     = 0;
		$scheduled_ctas = 0;
		$now_timestamp  = current_time( 'timestamp' );

		foreach ( $all_ctas as $cta ) {
			$status         = $cta['status'] ?? 'draft';
			$schedule_start = $cta['schedule_start'] ?? null;

			if ( 'draft' === $status ) {
				++$draft_ctas;
			} elseif ( 'scheduled' === $status ) {
				++$scheduled_ctas;
			} elseif ( 'publish' === $status && ! empty( $schedule_start ) && strtotime( $schedule_start ) > $now_timestamp ) {
				++$scheduled_ctas;
			} elseif ( 'publish' === $status ) {
				++$active_ctas;
			} else {
				++$draft_ctas;
			}
		}

		// Calculate total impressions and clicks from snapshot
		$total_impressions   = 0;
		$total_clicks        = 0;
		$best_ctr_name       = '--';
		$best_ctr_id         = 0;
		$best_ctr_value      = 0;
		$most_seen_name      = '--';
		$most_seen_id        = 0;
		$most_seen_count     = 0;
		$most_clicked_name   = '--';
		$most_clicked_id     = 0;
		$most_clicked_count  = 0;

		// Build CTA name lookup from actual CTA data
		$cta_name_lookup = [];
		foreach ( $all_ctas as $cta ) {
			$cta_name_lookup[ (int) $cta['id'] ] = $cta['name'] ?? '';
		}

		foreach ( $snapshot['ctas'] as $cta_id => $cta_stats ) {
			$impressions = (int) ( $cta_stats['impressions'] ?? 0 );
			$clicks      = (int) ( $cta_stats['clicks'] ?? 0 );
			$cta_title   = $cta_name_lookup[ (int) $cta_id ] ?? $cta_stats['title'] ?: "CTA #{$cta_id}";

			$total_impressions += $impressions;
			$total_clicks      += $clicks;

			// Track most seen CTA
			if ( $impressions > $most_seen_count ) {
				$most_seen_count = $impressions;
				$most_seen_name  = $cta_title;
				$most_seen_id    = (int) $cta_id;
			}

			// Track most clicked CTA
			if ( $clicks > $most_clicked_count ) {
				$most_clicked_count = $clicks;
				$most_clicked_name  = $cta_title;
				$most_clicked_id    = (int) $cta_id;
			}

			// Track best CTR
			if ( $impressions > 0 ) {
				$ctr = ( $clicks / $impressions ) * 100;
				if ( $ctr > $best_ctr_value ) {
					$best_ctr_value = $ctr;
					$best_ctr_name  = $cta_title;
					$best_ctr_id    = (int) $cta_id;
				}
			}
		}

		// Get last seen date for most seen CTA
		$most_seen_last_date = '';
		if ( $most_seen_id > 0 ) {
			$last_event = $events_repo->get_events( [
				'cta_id'     => $most_seen_id,
				'event_type' => 'impression',
				'order_by'   => 'occurred_at',
				'order'      => 'DESC',
				'limit'      => 1,
			] );
			if ( ! empty( $last_event[0]['occurred_at'] ) ) {
				$most_seen_last_date = human_time_diff( strtotime( $last_event[0]['occurred_at'] ), current_time( 'timestamp' ) ) . ' ago';
			}
		}

		// Calculate average CTR
		$avg_ctr = $total_impressions > 0
			? round( ( $total_clicks / $total_impressions ) * 100, 1 )
			: 0;

		// Get top page (by clicks)
		$top_pages      = $events_repo->get_top_pages( $seven_days, $today, null, 'clicks', 1, 1 );
		$top_page_title = '--';
		$top_page_slug  = '--';
		$top_page_url   = '';
		if ( ! empty( $top_pages['pages'][0] ) ) {
			$page_data      = $top_pages['pages'][0];
			$top_page_url   = $page_data['page_url'] ?? '';
			$top_page_title = $page_data['page_title'] ?? '';
			if ( ! empty( $top_page_url ) ) {
				$parsed        = wp_parse_url( $top_page_url );
				$top_page_slug = $parsed['path'] ?? '/';
			}
			// Fallback title to slug if no title
			if ( empty( $top_page_title ) ) {
				$top_page_title = $top_page_slug;
			}
		}

		// Get unique visitors (count distinct visitor_id with impressions)
		$unique_visitors = $this->count_unique_visitors( $seven_days, $today );

		// Get today's clicks
		$today_clicks = $events_repo->count_events( [
			'start_date' => $today,
			'end_date'   => $today,
			'event_type' => 'click',
		] );

		// Get today's impressions
		$today_impressions = $events_repo->count_events( [
			'start_date' => $today,
			'end_date'   => $today,
			'event_type' => 'impression',
		] );

		$impressions_14d = $events_repo->count_events( [
			'start_date' => $fourteen_days,
			'end_date'   => $today,
			'event_type' => 'impression',
		] );

		$impressions_30d = $events_repo->count_events( [
			'start_date' => $thirty_days,
			'end_date'   => $today,
			'event_type' => 'impression',
		] );

		$clicks_14d = $events_repo->count_events( [
			'start_date' => $fourteen_days,
			'end_date'   => $today,
			'event_type' => 'click',
		] );

		$clicks_30d = $events_repo->count_events( [
			'start_date' => $thirty_days,
			'end_date'   => $today,
			'event_type' => 'click',
		] );

		// Get unique clicks (distinct visitor_id with clicks)
		$unique_clicks = $this->count_unique_clickers( $seven_days, $today );

		// Get pages with CTA activity
		$pages_with_ctas = $events_repo->count_distinct_pages( $seven_days, $today );

		// Get most active page (highest combined impressions + clicks)
		$most_active_page_title       = '--';
		$most_active_page_slug        = '--';
		$most_active_page_url         = '';
		$most_active_page_impressions = 0;
		$most_active_page_clicks      = 0;
		$all_pages                    = $events_repo->get_top_pages( $seven_days, $today, null, 'impressions', 1, 50 );
		if ( ! empty( $all_pages['pages'] ) ) {
			$highest_engagement = 0;
			foreach ( $all_pages['pages'] as $page_data ) {
				$page_impressions = (int) ( $page_data['impressions'] ?? 0 );
				$page_clicks      = (int) ( $page_data['clicks'] ?? 0 );
				$engagement       = $page_impressions + $page_clicks;
				if ( $engagement > $highest_engagement ) {
					$highest_engagement            = $engagement;
					$most_active_page_impressions  = $page_impressions;
					$most_active_page_clicks       = $page_clicks;
					$most_active_page_url          = $page_data['page_url'] ?? '';
					$most_active_page_title        = $page_data['page_title'] ?? '';
					if ( ! empty( $page_data['page_url'] ) ) {
						$parsed                 = wp_parse_url( $page_data['page_url'] );
						$most_active_page_slug  = $parsed['path'] ?? '/';
					}
					// Fallback title to slug if no title
					if ( empty( $most_active_page_title ) ) {
						$most_active_page_title = $most_active_page_slug;
					}
				}
			}
		}

		return [
			'active_ctas'                  => $active_ctas,
			'draft_ctas'                   => $draft_ctas,
			'scheduled_ctas'               => $scheduled_ctas,
			'total_ctas'                   => count( $all_ctas ),
			'total_impressions'            => $total_impressions,
			'total_clicks'                 => $total_clicks,
			'avg_ctr'                      => $avg_ctr,
			'top_page_title'               => $top_page_title,
			'top_page_slug'                => $top_page_slug,
			'top_page_url'                 => $top_page_url,
			'unique_visitors'              => $unique_visitors,
			'best_ctr_name'                => $best_ctr_name,
			'best_ctr_id'                  => $best_ctr_id,
			'today_clicks'                 => $today_clicks,
			'today_impressions'            => $today_impressions,
			'most_seen_name'               => $most_seen_name,
			'most_seen_id'                 => $most_seen_id,
			'most_seen_last_date'          => $most_seen_last_date,
			'most_clicked_name'            => $most_clicked_name,
			'most_clicked_id'              => $most_clicked_id,
			'impressions_7d'               => $total_impressions,
			'impressions_14d'              => $impressions_14d,
			'impressions_30d'              => $impressions_30d,
			'clicks_7d'                    => $total_clicks,
			'clicks_14d'                   => $clicks_14d,
			'clicks_30d'                   => $clicks_30d,
			'unique_clicks'                => $unique_clicks,
			'pages_with_ctas'              => $pages_with_ctas,
			'most_active_page_title'       => $most_active_page_title,
			'most_active_page_slug'        => $most_active_page_slug,
			'most_active_page_url'         => $most_active_page_url,
			'most_active_page_impressions' => $most_active_page_impressions,
			'most_active_page_clicks'      => $most_active_page_clicks,
			'window_days_7'                => $window_7_days,
			'window_days_14'               => $window_14_days,
			'window_days_30'               => $window_30_days,
		];
	}

	/**
	 * Count unique visitors with impressions
	 *
	 * @param string $start_date Start date (Y-m-d)
	 * @param string $end_date   End date (Y-m-d)
	 *
	 * @return int Unique visitor count
	 */
	private function count_unique_visitors( string $start_date, string $end_date ): int {
		$table = CTA_Events_Repository::get_table_name();

		if ( ! CTA_Database::table_exists( $table ) ) {
			return 0;
		}

		$sql = "SELECT COUNT(DISTINCT visitor_id) as unique_count
				FROM {$table}
				WHERE DATE(occurred_at) >= %s
				AND DATE(occurred_at) <= %s
				AND visitor_id IS NOT NULL
				AND event_type = 'impression'";

		$result = CTA_Database::query( $sql, [ $start_date, $end_date ] );

		return (int) ( $result[0]['unique_count'] ?? 0 );
	}

	/**
	 * Count unique visitors with clicks
	 *
	 * @param string $start_date Start date (Y-m-d)
	 * @param string $end_date   End date (Y-m-d)
	 *
	 * @return int Unique clicker count
	 */
	private function count_unique_clickers( string $start_date, string $end_date ): int {
		$table = CTA_Events_Repository::get_table_name();

		if ( ! CTA_Database::table_exists( $table ) ) {
			return 0;
		}

		$sql = "SELECT COUNT(DISTINCT visitor_id) as unique_count
				FROM {$table}
				WHERE DATE(occurred_at) >= %s
				AND DATE(occurred_at) <= %s
				AND visitor_id IS NOT NULL
				AND event_type = 'click'";

		$result = CTA_Database::query( $sql, [ $start_date, $end_date ] );

		return (int) ( $result[0]['unique_count'] ?? 0 );
	}

	/**
	 * Calculate retention days for cleanup based on settings.
	 *
	 * @param array $settings Full settings array
	 *
	 * @return int Number of days to retain, or 0 for unlimited
	 */
	private function get_cleanup_retention_days( array $settings ): int {
		$retention = $settings['analytics']['retention'] ?? '7';

		// Free version: limited to 1 or 7 days
		$free_retention_values = [ '1', '7' ];
		$days = in_array( $retention, $free_retention_values, true ) ? absint( $retention ) : 7;

		/**
		 * Filter the analytics cleanup retention days.
		 *
		 * Allows extensions to modify the retention period.
		 *
		 * @param int   $days     Number of days to retain.
		 * @param array $settings Full settings array.
		 */
		return apply_filters( 'cta_cleanup_retention_days', $days, $settings );
	}

	/**
	 * Get retention days for reporting (0 means unlimited).
	 *
	 * @return int
	 */
	public function get_reporting_retention_days(): int {
		$settings = $this->get_settings();
		return $this->get_cleanup_retention_days( $settings );
	}

	/**
	 * Get reporting start date based on retention.
	 *
	 * @param int|null $window_days Optional window override
	 *
	 * @return string|null
	 */
	public function get_reporting_start_date( ?int $window_days = null ): ?string {
		$retention_days = $this->get_reporting_retention_days();

		if ( $window_days !== null && $window_days > 0 ) {
			$days = $retention_days > 0 ? min( $window_days, $retention_days ) : $window_days;
			return gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );
		}

		if ( $retention_days > 0 ) {
			return gmdate( 'Y-m-d', strtotime( "-{$retention_days} days" ) );
		}

		return null;
	}

	/**
	 * Get reporting window length with retention clamp.
	 *
	 * @param int $default_days Default window length
	 *
	 * @return int
	 */
	private function get_reporting_window_days( int $default_days ): int {
		$retention_days = $this->get_reporting_retention_days();
		if ( $retention_days > 0 ) {
			return min( $default_days, $retention_days );
		}
		return $default_days;
	}

	/**
	 * Clamp a date range to the current retention window.
	 *
	 * @param string|null $start_date Start date (Y-m-d)
	 * @param string|null $end_date   End date (Y-m-d)
	 *
	 * @return array{0: string|null, 1: string}
	 */
	public function clamp_date_range_to_retention( ?string $start_date, ?string $end_date ): array {
		$end_date = $end_date ?: gmdate( 'Y-m-d' );
		$retention_start = $this->get_reporting_start_date();

		if ( $retention_start && ( empty( $start_date ) || $start_date < $retention_start ) ) {
			$start_date = $retention_start;
		}

		if ( ! empty( $start_date ) && $end_date < $start_date ) {
			$end_date = $start_date;
		}

		return [ $start_date, $end_date ];
	}

	/**
	 * Check if analytics data exists within the reporting window.
	 *
	 * @return bool
	 */
	public function has_analytics_data_for_reporting(): bool {
		$events_repo = CTA_Events_Repository::get_instance();

		if ( ! $events_repo->table_exists() ) {
			return false;
		}

		[ $start_date, $end_date ] = $this->clamp_date_range_to_retention( null, null );
		$filters = [];
		if ( $start_date ) {
			$filters['start_date'] = $start_date;
		}
		if ( $end_date ) {
			$filters['end_date'] = $end_date;
		}

		return $events_repo->count_events( $filters ) > 0;
	}


	/**
	 * Handle data reset request
	 *
	 * Clears data based on selected scope (all, ctas, analytics)
	 * Can optionally include or exclude demo data
	 *
	 * @return void
	 */
	public function handle_reset_data(): void {
		if ( ! current_user_can( CTA_Admin_Menu::REQUIRED_CAP ) ) {
			wp_die( esc_html__( 'Unauthorized', 'cta-manager' ) );
		}

		if ( ! check_admin_referer( 'cta_admin_nonce', 'nonce' ) ) {
			wp_safe_redirect( add_query_arg( 'message', 'invalid_nonce', CTA_Admin_Menu::get_admin_url( 'tools' ) ) );
			exit;
		}

		$reset = isset( $_POST['reset'] ) && is_array( $_POST['reset'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['reset'] ) )
			: [];

		// Check which toggles are enabled
		$reset_ctas          = isset( $reset['ctas'] ) && '1' === $reset['ctas'];
		$reset_settings      = isset( $reset['settings'] ) && '1' === $reset['settings'];
		$reset_analytics     = isset( $reset['analytics'] ) && '1' === $reset['analytics'];
		$reset_notifications = isset( $reset['notifications'] ) && '1' === $reset['notifications'];

		// Reset CTAs (excluding demo data - demo data is managed separately)
		if ( $reset_ctas && class_exists( 'CTA_Repository' ) ) {
			$repo = CTA_Repository::get_instance();
			if ( $repo->table_exists() ) {
				$all_ctas = $repo->get_all();
				foreach ( $all_ctas as $cta ) {
					// Skip demo CTAs - they are managed separately
					if ( ! empty( $cta['_demo'] ) ) {
						continue;
					}
					$repo->delete( (int) $cta['id'] );
				}
			}
		}

		// Reset Settings
		if ( $reset_settings ) {
			$settings_repo = $this->get_settings_repo();
			if ( $settings_repo ) {
				$settings_repo->truncate();
			}
		}

		// Reset Analytics
		if ( $reset_analytics ) {
			global $wpdb;
			$events_repo  = CTA_Events_Repository::get_instance();
			$events_table = CTA_Events_Repository::get_table_name();
			if ( CTA_Database::table_exists( $events_table ) ) {
				// Get demo CTA IDs to preserve their events
				$cta_repo     = CTA_Repository::get_instance();
				$demo_cta_ids = $cta_repo->get_demo_cta_ids();

				if ( empty( $demo_cta_ids ) ) {
					// No demo CTAs - truncate all events
					$events_repo->truncate();
				} else {
					// Delete events NOT associated with demo CTAs
					$placeholders = implode( ',', array_fill( 0, count( $demo_cta_ids ), '%d' ) );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wpdb->query(
						$wpdb->prepare(
							"DELETE FROM {$events_table} WHERE cta_id NOT IN ({$placeholders})",
							...$demo_cta_ids
						)
					);
				}
			}

			// Delete non-demo visitors (preserve demo visitors 1-12)
			$visitors_table = CTA_Database::table( CTA_Database::TABLE_VISITORS );
			if ( CTA_Database::table_exists( $visitors_table ) ) {
				// Delete all visitors except demo visitors (IDs 1-12)
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$visitors_table} WHERE id < %d OR id > %d",
						1,
						12
					)
				);
			}
		}

		// Reset Notifications
		if ( $reset_notifications && class_exists( 'CTA_Notifications' ) ) {
			$notifications_table = CTA_Notifications::get_table_name();
			if ( CTA_Database::table_exists( $notifications_table ) ) {
				CTA_Database::truncate( $notifications_table );
			}
		}

		wp_safe_redirect(
			add_query_arg(
				'message',
				'reset_success',
				CTA_Admin_Menu::get_admin_url( 'tools' )
			)
		);
		exit;
	}
}
