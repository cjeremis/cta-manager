<?php
/**
 * Settings Repository Handler
 *
 * Handles settings storage and settings retrieval operations.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Settings_Repository {

	use CTA_Singleton;

	/**
	 * Setting groups
	 */
	const GROUP_GENERAL    = 'general';
	const GROUP_ANALYTICS  = 'analytics';
	const GROUP_ONBOARDING = 'onboarding';
	const GROUP_TOOLS      = 'tools';
	const GROUP_PORTAL     = 'portal';
	const GROUP_BACKUP     = 'backup';
	const GROUP_CUSTOM     = 'custom';

	/**
	 * Scope values
	 */
	const SCOPE_SITE    = 'site';
	const SCOPE_NETWORK = 'network';

	/**
	 * Cache for autoloaded settings
	 *
	 * @var array|null
	 */
	private ?array $autoload_cache = null;

	/**
	 * Get the full table name with prefix
	 *
	 * @return string
	 */
	public static function get_table_name(): string {
		return CTA_Database::table( CTA_Database::TABLE_SETTINGS );
	}

	/**
	 * Check if the settings table exists
	 *
	 * @return bool
	 */
	public function table_exists(): bool {
		return CTA_Database::table_exists( self::get_table_name() );
	}

	/**
	 * Get a setting by key
	 *
	 * @param string $key     Setting key
	 * @param mixed  $default Default value if not found
	 * @param string $scope   Scope (site/network)
	 *
	 * @return mixed
	 */
	public function get( string $key, mixed $default = null, string $scope = self::SCOPE_SITE ): mixed {
		// Check autoload cache first
		if ( $this->autoload_cache !== null && isset( $this->autoload_cache[ $key ] ) ) {
			return $this->autoload_cache[ $key ];
		}

		if ( ! $this->table_exists() ) {
			return $default;
		}

		$table = self::get_table_name();

		$sql = "SELECT value_json FROM {$table} WHERE setting_key = %s AND scope = %s LIMIT 1";
		$results = CTA_Database::query( $sql, [ $key, $scope ] );

		if ( empty( $results ) ) {
			return $default;
		}

		$value = json_decode( $results[0]['value_json'], true );
		return ( json_last_error() === JSON_ERROR_NONE ) ? $value : $default;
	}

	/**
	 * Get a setting with full metadata
	 *
	 * @param string $key   Setting key
	 * @param string $scope Scope (site/network)
	 *
	 * @return array|null Full row data or null if not found
	 */
	public function get_row( string $key, string $scope = self::SCOPE_SITE ): ?array {
		if ( ! $this->table_exists() ) {
			return null;
		}

		$table = self::get_table_name();

		$sql = "SELECT * FROM {$table} WHERE setting_key = %s AND scope = %s LIMIT 1";
		$results = CTA_Database::query( $sql, [ $key, $scope ] );

		if ( empty( $results ) ) {
			return null;
		}

		$row = $results[0];

		// Decode JSON fields
		$row['value'] = json_decode( $row['value_json'], true );
		if ( ! empty( $row['meta_json'] ) ) {
			$row['meta'] = json_decode( $row['meta_json'], true );
		}

		return $row;
	}

	/**
	 * Set/update a setting
	 *
	 * @param string      $key      Setting key
	 * @param mixed       $value    Value to store (will be JSON encoded)
	 * @param string|null $group    Optional setting group
	 * @param bool        $autoload Whether to autoload this setting
	 * @param string      $scope    Scope (site/network)
	 * @param array|null  $meta     Optional metadata
	 *
	 * @return bool Success
	 */
	public function set(
		string $key,
		mixed $value,
		?string $group = null,
		bool $autoload = true,
		string $scope = self::SCOPE_SITE,
		?array $meta = null
	): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		$value_json = wp_json_encode( $value );
		if ( $value_json === false ) {
			return false;
		}

		$updated_by = get_current_user_id() ?: null;
		$meta_json  = $meta ? wp_json_encode( $meta ) : null;

		// Check if exists
		$sql = "SELECT id FROM {$table} WHERE setting_key = %s AND scope = %s LIMIT 1";
		$exists_result = CTA_Database::query( $sql, [ $key, $scope ] );
		$exists = ! empty( $exists_result );

		if ( $exists ) {
			// Update existing
			$data = [
				'value_json' => $value_json,
				'autoload'   => $autoload ? 1 : 0,
				'updated_by' => $updated_by,
				'updated_at' => current_time( 'mysql' ),
			];
			$formats = [ '%s', '%d', '%d', '%s' ];

			if ( $group !== null ) {
				$data['setting_group'] = $group;
				$formats[]             = '%s';
			}

			if ( $meta_json !== null ) {
				$data['meta_json'] = $meta_json;
				$formats[]         = '%s';
			}

			$result = CTA_Database::update(
				$table,
				$data,
				[ 'setting_key' => $key, 'scope' => $scope ],
				$formats,
				[ '%s', '%s' ]
			);
		} else {
			// Insert new
			$data = [
				'setting_key'   => $key,
				'setting_group' => $group,
				'value_json'    => $value_json,
				'scope'         => $scope,
				'autoload'      => $autoload ? 1 : 0,
				'updated_by'    => $updated_by,
				'meta_json'     => $meta_json,
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' ),
			];

			$result = CTA_Database::insert(
				$table,
				$data,
				[ '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s' ]
			);
		}

		// Clear autoload cache on write
		$this->autoload_cache = null;

		return $result !== false;
	}

	/**
	 * Delete a setting
	 *
	 * @param string $key   Setting key
	 * @param string $scope Scope (site/network)
	 *
	 * @return bool Success
	 */
	public function delete( string $key, string $scope = self::SCOPE_SITE ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		$result = CTA_Database::delete(
			$table,
			[ 'setting_key' => $key, 'scope' => $scope ],
			[ '%s', '%s' ]
		);

		// Clear autoload cache
		$this->autoload_cache = null;

		return $result !== false;
	}

	/**
	 * Get all settings by group
	 *
	 * @param string $group Setting group
	 * @param string $scope Scope (site/network)
	 *
	 * @return array Array of key => value pairs
	 */
	public function get_by_group( string $group, string $scope = self::SCOPE_SITE ): array {
		if ( ! $this->table_exists() ) {
			return [];
		}

		$table = self::get_table_name();

		$sql = "SELECT setting_key, value_json FROM {$table} WHERE setting_group = %s AND scope = %s";
		$rows = CTA_Database::query( $sql, [ $group, $scope ] );

		$settings = [];
		foreach ( $rows as $row ) {
			$value = json_decode( $row['value_json'], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$settings[ $row['setting_key'] ] = $value;
			}
		}

		return $settings;
	}

	/**
	 * Delete all settings in a group
	 *
	 * @param string $group Setting group
	 * @param string $scope Scope (site/network)
	 *
	 * @return bool Success
	 */
	public function delete_group( string $group, string $scope = self::SCOPE_SITE ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		$result = CTA_Database::delete(
			$table,
			[ 'setting_group' => $group, 'scope' => $scope ],
			[ '%s', '%s' ]
		);

		$this->autoload_cache = null;

		return $result !== false;
	}

	/**
	 * Load all autoloadable settings into cache
	 *
	 * @param string $scope Scope (site/network)
	 *
	 * @return array Loaded settings
	 */
	public function load_autoload_settings( string $scope = self::SCOPE_SITE ): array {
		if ( $this->autoload_cache !== null ) {
			return $this->autoload_cache;
		}

		if ( ! $this->table_exists() ) {
			$this->autoload_cache = [];
			return $this->autoload_cache;
		}

		$table = self::get_table_name();

		$sql = "SELECT setting_key, value_json FROM {$table} WHERE autoload = 1 AND scope = %s";
		$rows = CTA_Database::query( $sql, [ $scope ] );

		$this->autoload_cache = [];
		foreach ( $rows as $row ) {
			$value = json_decode( $row['value_json'], true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				$this->autoload_cache[ $row['setting_key'] ] = $value;
			}
		}

		return $this->autoload_cache;
	}

	/**
	 * Get all settings (for export)
	 *
	 * @param string $scope Scope (site/network)
	 *
	 * @return array Array of all settings with metadata
	 */
	public function get_all( string $scope = self::SCOPE_SITE ): array {
		if ( ! $this->table_exists() ) {
			return [];
		}

		$table = self::get_table_name();

		$sql = "SELECT setting_key, setting_group, value_json, autoload, meta_json, created_at, updated_at
				 FROM {$table} WHERE scope = %s";
		$rows = CTA_Database::query( $sql, [ $scope ] );

		$settings = [];
		foreach ( $rows as $row ) {
			$value = json_decode( $row['value_json'], true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				continue;
			}

			$settings[ $row['setting_key'] ] = [
				'value'    => $value,
				'group'    => $row['setting_group'],
				'autoload' => (bool) $row['autoload'],
				'meta'     => $row['meta_json'] ? json_decode( $row['meta_json'], true ) : null,
			];
		}

		return $settings;
	}

	/**
	 * Import settings (for import/restore)
	 *
	 * @param array  $settings Array of settings from get_all() format
	 * @param bool   $merge    Whether to merge with existing (true) or replace (false)
	 * @param string $scope    Scope (site/network)
	 *
	 * @return bool Success
	 */
	public function import( array $settings, bool $merge = true, string $scope = self::SCOPE_SITE ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		// If not merging, clear existing settings first
		if ( ! $merge ) {
			$table = self::get_table_name();
			CTA_Database::delete( $table, [ 'scope' => $scope ], [ '%s' ] );
		}

		$success = true;
		foreach ( $settings as $key => $data ) {
			$value    = $data['value'] ?? $data;
			$group    = $data['group'] ?? null;
			$autoload = $data['autoload'] ?? true;
			$meta     = $data['meta'] ?? null;

			if ( ! $this->set( $key, $value, $group, $autoload, $scope, $meta ) ) {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Clear the autoload cache
	 *
	 * @return void
	 */
	public function clear_cache(): void {
		$this->autoload_cache = null;
	}

	/**
	 * Check if a setting key exists
	 *
	 * @param string $key   Setting key
	 * @param string $scope Scope (site/network)
	 *
	 * @return bool
	 */
	public function exists( string $key, string $scope = self::SCOPE_SITE ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		$sql = "SELECT 1 FROM {$table} WHERE setting_key = %s AND scope = %s LIMIT 1";
		$result = CTA_Database::query( $sql, [ $key, $scope ] );

		return ! empty( $result );
	}
}
