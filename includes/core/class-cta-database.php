<?php
/**
 * Centralized Database Manager (Free)
 *
 * Handles CTA Manager table definitions, creation, and CRUD operations for the CTA Manager plugin.
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! trait_exists( 'CTA_Singleton' ) ) {
	require_once CTA_PLUGIN_DIR . 'includes/traits/trait-cta-singleton.php';
}

class CTA_Database {

	use CTA_Singleton;

	/**
	 * Table name constants
	 *
	 * SINGLE SOURCE OF TRUTH - All table references should use these constants.
	 * Use CTA_Database::TABLE_* throughout the codebase, not hardcoded strings.
	 *
	 * Note: Support tickets are NOT stored locally. They are managed via the
	 * TopDevAmerica API and stored on TopDevAmerica's servers.
	 */
	const TABLE_EVENTS        = 'cta_manager_events';
	const TABLE_NOTIFICATIONS = 'cta_manager_notifications';
	const TABLE_SETTINGS      = 'cta_manager_settings';
	const TABLE_CTA_MANAGER   = 'cta_manager';

	
	/**
	 * Get full table name with prefix
	 *
	 * @param string $table Table constant name.
	 *
	 * @return string
	 */
	public static function table( string $table ): string {
		global $wpdb;
		return $wpdb->prefix . $table;
	}

	/**
	 * Get all free tables
	 *
	 * @return array
	 */
	public static function get_tables(): array {
		return array(
			'events'        => self::table( self::TABLE_EVENTS ),
			'notifications' => self::table( self::TABLE_NOTIFICATIONS ),
			'settings'      => self::table( self::TABLE_SETTINGS ),
			'cta_manager'   => self::table( self::TABLE_CTA_MANAGER ),
		);
	}

	// =========================================================================
	// Table Creation
	// =========================================================================

	/**
	 * Create all CTA Manager tables
	 *
	 * @return void
	 */
	public static function create_tables(): void {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		self::create_notifications_table();
		self::create_settings_table();
		self::create_cta_table();
		self::create_events_table();
	}

	/**
	 * Check if all free tables exist
	 *
	 * @return bool
	 */
	public static function tables_exist(): bool {
		foreach ( self::get_tables() as $table ) {
			if ( ! self::table_exists( $table ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if a specific table exists
	 *
	 * @param string $table Full table name with prefix.
	 *
	 * @return bool
	 */
	public static function table_exists( string $table ): bool {
		global $wpdb;
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		return $exists === $table;
	}

	/**
	 * Ensure tables exist
	 *
	 * @return void
	 */
	public static function maybe_create_tables(): void {
		if ( self::tables_exist() ) {
			return;
		}
		self::create_tables();
	}

	/**
	 * Create notifications table
	 *
	 * @return void
	 */
	private static function create_notifications_table(): void {
		global $wpdb;

		$table           = self::table( self::TABLE_NOTIFICATIONS );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			type varchar(100) NOT NULL,
			title varchar(255) NOT NULL,
			message longtext NOT NULL,
			icon varchar(50) DEFAULT 'info',
			actions longtext,
			dismissed tinyint(1) DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY type (type),
			KEY dismissed (dismissed),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Create settings table
	 *
	 * @return void
	 */
	private static function create_settings_table(): void {
		global $wpdb;

		$table           = self::table( self::TABLE_SETTINGS );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			setting_key varchar(191) NOT NULL,
			setting_group varchar(100) DEFAULT NULL,
			value_json longtext NOT NULL,
			scope varchar(50) NOT NULL DEFAULT 'site',
			autoload tinyint(1) NOT NULL DEFAULT 1,
			updated_by bigint(20) unsigned DEFAULT NULL,
			meta_json longtext DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY setting_key_scope (setting_key, scope),
			KEY setting_group (setting_group),
			KEY autoload (autoload)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Create CTA manager table
	 *
	 * @return void
	 */
	private static function create_cta_table(): void {
		global $wpdb;

		$table           = self::table( self::TABLE_CTA_MANAGER );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			uuid char(36) NOT NULL,
			slug varchar(191) NOT NULL,
			title varchar(255) NOT NULL DEFAULT '',
			name varchar(255) DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'draft',
			type varchar(50) NOT NULL DEFAULT 'phone',
			layout varchar(50) NOT NULL DEFAULT 'button',
			is_enabled tinyint(1) NOT NULL DEFAULT 1,
			visibility varchar(50) NOT NULL DEFAULT 'all_devices',
			schedule_start datetime DEFAULT NULL,
			schedule_end datetime DEFAULT NULL,
			priority int NOT NULL DEFAULT 0,
			color varchar(20) DEFAULT NULL,
			icon varchar(100) DEFAULT 'none',
			author_id bigint(20) unsigned DEFAULT NULL,
			created_by bigint(20) unsigned DEFAULT NULL,
			updated_by bigint(20) unsigned DEFAULT NULL,
			content_json longtext DEFAULT NULL,
			style_json longtext DEFAULT NULL,
			behavior_json longtext DEFAULT NULL,
			targeting_json longtext DEFAULT NULL,
			assets_json longtext DEFAULT NULL,
			integrations_json longtext DEFAULT NULL,
			settings_json longtext DEFAULT NULL,
			metrics_snapshot_json longtext DEFAULT NULL,
			meta_json longtext DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			published_at datetime DEFAULT NULL,
			deleted_at datetime DEFAULT NULL,
			trashed_at datetime DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY uuid (uuid),
			UNIQUE KEY slug (slug),
			KEY status_enabled (status, is_enabled),
			KEY type (type),
			KEY schedule (schedule_start, schedule_end),
			KEY trashed_at (trashed_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Create events table
	 *
	 * @return void
	 */
	private static function create_events_table(): void {
		global $wpdb;

		$table           = self::table( self::TABLE_EVENTS );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			cta_id bigint(20) unsigned NOT NULL,
			cta_uuid char(36) DEFAULT NULL,
			event_type varchar(50) NOT NULL DEFAULT 'impression',
			occurred_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			page_url varchar(2048) DEFAULT NULL,
			page_url_hash varchar(32) DEFAULT NULL,
			page_title varchar(255) DEFAULT NULL,
			referrer varchar(2048) DEFAULT NULL,
			device varchar(20) DEFAULT NULL,
			session_id varchar(64) DEFAULT NULL,
			visitor_id varchar(64) DEFAULT NULL,
			user_id bigint(20) unsigned DEFAULT NULL,
			experiment_key varchar(100) DEFAULT NULL,
			variant varchar(50) DEFAULT NULL,
			value decimal(18,4) DEFAULT NULL,
			currency char(3) DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			user_agent varchar(512) DEFAULT NULL,
			cta_title varchar(255) DEFAULT '',
			context_json longtext DEFAULT NULL,
			meta_json longtext DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY idx_cta_occurred (cta_id, occurred_at),
			KEY idx_type_occurred (event_type, occurred_at),
			KEY idx_cta_type_occurred (cta_id, event_type, occurred_at),
			KEY idx_page_hash_type (page_url_hash, event_type),
			KEY idx_visitor_occurred (visitor_id, occurred_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	// =========================================================================
	// INSERT Operations
	// =========================================================================

	public static function insert( string $table, array $data, ?array $format = null ) {
		global $wpdb;

		$result = $wpdb->insert( $table, $data, $format );
		return $result ? $wpdb->insert_id : false;
	}

	public static function bulk_insert( string $table, array $rows ): int {
		global $wpdb;

		if ( empty( $rows ) ) {
			return 0;
		}

		$columns      = array_keys( $rows[0] );
		$placeholders = array();
		$values       = array();

		foreach ( $rows as $row ) {
			$row_placeholders = array();
			foreach ( $columns as $col ) {
				$value = $row[ $col ] ?? null;
				if ( is_int( $value ) ) {
					$row_placeholders[] = '%d';
				} elseif ( is_float( $value ) ) {
					$row_placeholders[] = '%f';
				} else {
					$row_placeholders[] = '%s';
				}
				$values[] = $value;
			}
			$placeholders[] = '(' . implode( ', ', $row_placeholders ) . ')';
		}

		$columns_sql = '`' . implode( '`, `', $columns ) . '`';
		$sql         = "INSERT INTO {$table} ({$columns_sql}) VALUES " . implode( ', ', $placeholders );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $wpdb->prepare( $sql, $values ) );

		return $result !== false ? $result : 0;
	}

	public static function upsert( string $table, array $data, array $unique_keys ) {
		global $wpdb;

		$columns      = array_keys( $data );
		$values       = array_values( $data );
		$placeholders = array_map(
			function ( $val ) {
				return is_int( $val ) ? '%d' : ( is_float( $val ) ? '%f' : '%s' );
			},
			$values
		);

		$columns_sql      = '`' . implode( '`, `', $columns ) . '`';
		$placeholders_sql = implode( ', ', $placeholders );

		$update_parts = array();
		foreach ( $columns as $col ) {
			if ( ! in_array( $col, $unique_keys, true ) ) {
				$update_parts[] = "`{$col}` = VALUES(`{$col}`)";
			}
		}
		$update_sql = implode( ', ', $update_parts );

		$sql = "INSERT INTO {$table} ({$columns_sql}) VALUES ({$placeholders_sql}) ON DUPLICATE KEY UPDATE {$update_sql}";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $wpdb->prepare( $sql, $values ) );

		if ( $result === false ) {
			return false;
		}

		return $wpdb->insert_id ?: $result;
	}

	// =========================================================================
	// SELECT Operations
	// =========================================================================

	public static function get_results( string $table, array $where = [], ?string $orderby = null, ?int $limit = null, int $offset = 0 ): array {
		global $wpdb;

		$sql    = "SELECT * FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		if ( $orderby ) {
			$sql .= " ORDER BY {$orderby}";
		}

		if ( $limit !== null ) {
			$sql     .= ' LIMIT %d OFFSET %d';
			$values[] = $limit;
			$values[] = $offset;
		}

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql, ARRAY_A ) ?: array();
	}

	public static function get_row( string $table, array $where ): ?array {
		global $wpdb;

		$sql    = "SELECT * FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		$sql .= ' LIMIT 1';

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$row = $wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}

	public static function get_by_id( string $table, int $id ): ?array {
		return self::get_row( $table, array( 'id' => $id ) );
	}

	public static function get_var( string $table, string $column, array $where = [] ) {
		global $wpdb;

		$sql    = "SELECT `{$column}` FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		$sql .= ' LIMIT 1';

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $sql );
	}

	public static function query( string $sql, array $values = [] ): array {
		global $wpdb;

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql, ARRAY_A ) ?: array();
	}

	// =========================================================================
	// UPDATE Operations
	// =========================================================================

	public static function update( string $table, array $data, array $where, ?array $format = null, ?array $where_format = null ) {
		global $wpdb;
		$result = $wpdb->update( $table, $data, $where, $format, $where_format );
		return $result !== false ? $result : false;
	}

	public static function update_by_id( string $table, int $id, array $data, ?array $format = null ): bool {
		$result = self::update( $table, $data, array( 'id' => $id ), $format, array( '%d' ) );
		return $result !== false;
	}

	public static function increment( string $table, string $column, array $where, int $amount = 1 ): bool {
		global $wpdb;

		$values     = array( $amount );
		$conditions = self::build_where_clause( $where, $values );

		$sql = "UPDATE {$table} SET `{$column}` = `{$column}` + %d WHERE {$conditions}";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $wpdb->prepare( $sql, $values ) );

		return $result !== false;
	}

	public static function decrement( string $table, string $column, array $where, int $amount = 1 ): bool {
		return self::increment( $table, $column, $where, -$amount );
	}

	// =========================================================================
	// DELETE Operations
	// =========================================================================

	public static function delete( string $table, array $where, ?array $where_format = null ) {
		global $wpdb;
		$result = $wpdb->delete( $table, $where, $where_format );
		return $result !== false ? $result : false;
	}

	public static function delete_by_id( string $table, int $id ): bool {
		$result = self::delete( $table, array( 'id' => $id ), array( '%d' ) );
		return $result !== false && $result > 0;
	}

	public static function delete_before_date( string $table, string $date_column, string $before_date ): int {
		global $wpdb;

		$sql = "DELETE FROM {$table} WHERE `{$date_column}` < %s";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $wpdb->prepare( $sql, $before_date ) );

		return $result !== false ? $result : 0;
	}

	// =========================================================================
	// Aggregation Operations
	// =========================================================================

	public static function count( string $table, array $where = [] ): int {
		global $wpdb;

		$sql    = "SELECT COUNT(*) FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	public static function sum( string $table, string $column, array $where = [] ): float {
		global $wpdb;

		$sql    = "SELECT SUM(`{$column}`) FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (float) ( $wpdb->get_var( $sql ) ?? 0 );
	}

	public static function max( string $table, string $column, array $where = [] ) {
		global $wpdb;

		$sql    = "SELECT MAX(`{$column}`) FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $sql );
	}

	public static function min( string $table, string $column, array $where = [] ) {
		global $wpdb;

		$sql    = "SELECT MIN(`{$column}`) FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $sql );
	}

	// =========================================================================
	// Table Management Operations
	// =========================================================================

	public static function truncate( string $table ): bool {
		global $wpdb;

		if ( ! self::table_exists( $table ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query( "TRUNCATE TABLE {$table}" );
		return $result !== false;
	}

	public static function drop( string $table ): bool {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		return $result !== false;
	}

	public static function drop_all_tables(): bool {
		$success = true;
		foreach ( self::get_tables() as $table ) {
			if ( ! self::drop( $table ) ) {
				$success = false;
			}
		}
		return $success;
	}

	public static function truncate_all_tables(): bool {
		$success = true;
		foreach ( self::get_tables() as $table ) {
			if ( ! self::truncate( $table ) ) {
				$success = false;
			}
		}
		return $success;
	}

	public static function get_table_stats( string $table ): array {
		global $wpdb;

		$db_name = DB_NAME;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT TABLE_ROWS as rows, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
				$db_name,
				$table
			),
			ARRAY_A
		);

		return array(
			'rows'    => (int) ( $stats['rows'] ?? 0 ),
			'size_mb' => (float) ( $stats['size_mb'] ?? 0 ),
		);
	}

	public static function optimize( string $table ): bool {
		global $wpdb;

		if ( ! self::table_exists( $table ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query( "OPTIMIZE TABLE {$table}" );
		return $result !== false;
	}

	// =========================================================================
	// Helper Methods
	// =========================================================================

	private static function build_where_clause( array $where, array &$values ): string {
		$conditions = array();

		foreach ( $where as $column => $value ) {
			if ( is_null( $value ) ) {
				$conditions[] = "`{$column}` IS NULL";
			} elseif ( is_array( $value ) ) {
				$placeholders = array_fill( 0, count( $value ), '%s' );
				$conditions[] = "`{$column}` IN (" . implode( ', ', $placeholders ) . ')';
				$values       = array_merge( $values, $value );
			} else {
				$placeholder  = is_int( $value ) ? '%d' : ( is_float( $value ) ? '%f' : '%s' );
				$conditions[] = "`{$column}` = {$placeholder}";
				$values[]     = $value;
			}
		}

		return implode( ' AND ', $conditions );
	}

	public static function last_insert_id(): int {
		global $wpdb;
		return (int) $wpdb->insert_id;
	}

	public static function last_error(): string {
		global $wpdb;
		return $wpdb->last_error;
	}

	public static function begin_transaction(): bool {
		global $wpdb;
		return $wpdb->query( 'START TRANSACTION' ) !== false;
	}

	public static function commit(): bool {
		global $wpdb;
		return $wpdb->query( 'COMMIT' ) !== false;
	}

	public static function rollback(): bool {
		global $wpdb;
		return $wpdb->query( 'ROLLBACK' ) !== false;
	}

	// =========================================================================
	// JSON Column Helpers
	// =========================================================================

	public static function encode_json( $data ): string {
		if ( is_string( $data ) ) {
			return $data;
		}
		return wp_json_encode( $data ) ?: '{}';
	}

	public static function decode_json( ?string $json ): array {
		if ( empty( $json ) ) {
			return array();
		}
		$decoded = json_decode( $json, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	public static function insert_with_json( string $table, array $data, array $json_columns = [] ) {
		foreach ( $json_columns as $col ) {
			if ( isset( $data[ $col ] ) && ! is_string( $data[ $col ] ) ) {
				$data[ $col ] = self::encode_json( $data[ $col ] );
			}
		}

		$data = apply_filters( 'cta_db_before_insert', $data, $table );

		$result = self::insert( $table, $data );

		do_action( 'cta_db_after_insert', $result, $table, $data );

		return $result;
	}

	public static function update_with_json( string $table, array $data, array $where, array $json_columns = [] ) {
		foreach ( $json_columns as $col ) {
			if ( isset( $data[ $col ] ) && ! is_string( $data[ $col ] ) ) {
				$data[ $col ] = self::encode_json( $data[ $col ] );
			}
		}

		$data = apply_filters( 'cta_db_before_update', $data, $table, $where );

		$result = self::update( $table, $data, $where );

		do_action( 'cta_db_after_update', $result, $table, $data, $where );

		return $result;
	}

	// =========================================================================
	// Soft Delete Operations
	// =========================================================================

	public static function soft_delete( string $table, array $where, string $deleted_column = 'deleted_at' ) {
		return self::update(
			$table,
			array( $deleted_column => current_time( 'mysql' ) ),
			$where
		);
	}

	public static function soft_delete_by_id( string $table, int $id, string $deleted_column = 'deleted_at' ): bool {
		$result = self::soft_delete( $table, array( 'id' => $id ), $deleted_column );
		return $result !== false && $result > 0;
	}

	public static function restore( string $table, array $where, string $deleted_column = 'deleted_at' ) {
		return self::update(
			$table,
			array( $deleted_column => null ),
			$where
		);
	}

	public static function restore_by_id( string $table, int $id, string $deleted_column = 'deleted_at' ): bool {
		$result = self::restore( $table, array( 'id' => $id ), $deleted_column );
		return $result !== false && $result > 0;
	}

	public static function get_deleted( string $table, string $deleted_column = 'deleted_at', ?string $orderby = null, ?int $limit = null ): array {
		global $wpdb;

		$sql    = "SELECT * FROM {$table} WHERE `{$deleted_column}` IS NOT NULL";
		$values = array();

		if ( $orderby ) {
			$sql .= " ORDER BY {$orderby}";
		}

		if ( $limit !== null ) {
			$sql     .= ' LIMIT %d';
			$values[] = $limit;
		}

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql, ARRAY_A ) ?: array();
	}

	public static function purge_deleted( string $table, string $deleted_column = 'deleted_at' ): int {
		global $wpdb;

		$sql = "DELETE FROM {$table} WHERE `{$deleted_column}` IS NOT NULL";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $sql );

		return $result !== false ? $result : 0;
	}

	// =========================================================================
	// Search & LIKE Operations
	// =========================================================================

	public static function search( string $table, string $search_term, array $columns, array $where = [], ?string $orderby = null, ?int $limit = null ): array {
		global $wpdb;

		if ( empty( $columns ) || empty( $search_term ) ) {
			return array();
		}

		$sql    = "SELECT * FROM {$table} WHERE ";
		$values = array();

		$like_conditions = array();
		$like_value      = '%' . $wpdb->esc_like( $search_term ) . '%';
		foreach ( $columns as $col ) {
			$like_conditions[] = "`{$col}` LIKE %s";
			$values[]          = $like_value;
		}
		$sql .= '(' . implode( ' OR ', $like_conditions ) . ')';

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " AND {$conditions}";
		}

		if ( $orderby ) {
			$sql .= " ORDER BY {$orderby}";
		}

		if ( $limit !== null ) {
			$sql     .= ' LIMIT %d';
			$values[] = $limit;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $wpdb->prepare( $sql, $values ), ARRAY_A ) ?: array();
	}

	public static function like_exists( string $table, string $column, string $value ): bool {
		global $wpdb;

		$sql = "SELECT 1 FROM {$table} WHERE `{$column}` LIKE %s LIMIT 1";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_var( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( $value ) . '%' ) );

		return $result !== null;
	}

	// =========================================================================
	// Date Range Operations
	// =========================================================================

	public static function get_by_date_range( string $table, string $date_column, string $start_date, string $end_date, array $where = [], ?string $orderby = null, ?int $limit = null ): array {
		global $wpdb;

		$sql    = "SELECT * FROM {$table} WHERE `{$date_column}` BETWEEN %s AND %s";
		$values = array( $start_date, $end_date );

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " AND {$conditions}";
		}

		if ( $orderby ) {
			$sql .= " ORDER BY {$orderby}";
		}

		if ( $limit !== null ) {
			$sql     .= ' LIMIT %d';
			$values[] = $limit;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $wpdb->prepare( $sql, $values ), ARRAY_A ) ?: array();
	}

	public static function count_by_date_range( string $table, string $date_column, string $start_date, string $end_date, array $where = [] ): int {
		global $wpdb;

		$sql    = "SELECT COUNT(*) FROM {$table} WHERE `{$date_column}` BETWEEN %s AND %s";
		$values = array( $start_date, $end_date );

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " AND {$conditions}";
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $values ) );
	}

	public static function delete_by_date_range( string $table, string $date_column, string $start_date, string $end_date ): int {
		global $wpdb;

		$sql = "DELETE FROM {$table} WHERE `{$date_column}` BETWEEN %s AND %s";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $wpdb->prepare( $sql, $start_date, $end_date ) );

		return $result !== false ? $result : 0;
	}

	// =========================================================================
	// Export/Import Operations
	// =========================================================================

	public static function export_table( string $table, array $where = [], ?string $orderby = null ): array {
		return self::get_results( $table, $where, $orderby );
	}

	public static function import_rows( string $table, array $rows, array $required_cols = [], bool $skip_on_error = true ): array {
		$result = array(
			'imported' => 0,
			'skipped'  => 0,
			'errors'   => array(),
		);

		foreach ( $rows as $index => $row ) {
			foreach ( $required_cols as $col ) {
				if ( ! isset( $row[ $col ] ) || $row[ $col ] === '' ) {
					$result['errors'][] = sprintf( 'Row %d: Missing required column "%s"', $index, $col );
					if ( $skip_on_error ) {
						$result['skipped']++;
						continue 2;
					}
					return $result;
				}
			}

			unset( $row['id'] );

			$insert_result = self::insert( $table, $row );
			if ( $insert_result === false ) {
				$result['errors'][] = sprintf( 'Row %d: %s', $index, self::last_error() );
				if ( $skip_on_error ) {
					$result['skipped']++;
				} else {
					return $result;
				}
			} else {
				$result['imported']++;
			}
		}

		return $result;
	}

	// =========================================================================
	// Additional Aggregation & Utility Operations
	// =========================================================================

	public static function avg( string $table, string $column, array $where = [] ): float {
		global $wpdb;

		$sql    = "SELECT AVG(`{$column}`) FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (float) ( $wpdb->get_var( $sql ) ?? 0 );
	}

	public static function exists( string $table, array $where ): bool {
		global $wpdb;

		$sql    = "SELECT 1 FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		$sql .= ' LIMIT 1';

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( $sql ) !== null;
	}

	public static function get_distinct( string $table, string $column, array $where = [] ): array {
		global $wpdb;

		$sql    = "SELECT DISTINCT `{$column}` FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_col( $sql ) ?: array();
	}

	public static function get_grouped_count( string $table, string $group_column, array $where = [] ): array {
		global $wpdb;

		$sql    = "SELECT `{$group_column}`, COUNT(*) as count FROM {$table}";
		$values = array();

		if ( ! empty( $where ) ) {
			$conditions = self::build_where_clause( $where, $values );
			$sql       .= " WHERE {$conditions}";
		}

		$sql .= " GROUP BY `{$group_column}`";

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $sql, ARRAY_A ) ?: array();

		$counts = array();
		foreach ( $results as $row ) {
			$counts[ $row[ $group_column ] ] = (int) $row['count'];
		}

		return $counts;
	}

	public static function paginate( string $table, int $page = 1, int $per_page = 20, array $where = [], ?string $orderby = null ): array {
		$page     = max( 1, $page );
		$per_page = max( 1, $per_page );
		$offset   = ( $page - 1 ) * $per_page;
		$total    = self::count( $table, $where );
		$pages    = (int) ceil( $total / $per_page );
		$data     = self::get_results( $table, $where, $orderby, $per_page, $offset );

		return array(
			'data'     => $data,
			'total'    => $total,
			'pages'    => $pages,
			'page'     => $page,
			'per_page' => $per_page,
		);
	}
}
