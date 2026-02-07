<?php
/**
 * CTA Repository - Database-backed CTA storage
 *
 * Provides CRUD operations for the wp_cta_manager table.
 * Handles JSON packing/unpacking for flexible feature groups.
 *
 * @package CTAManager
 * @since 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Repository {

	use CTA_Singleton;

	/**
	 * CTA status values
	 */
	const STATUS_DRAFT     = 'draft';
	const STATUS_PUBLISHED = 'publish';
	const STATUS_SCHEDULED = 'scheduled';
	const STATUS_TRASH     = 'trash';
	const STATUS_ARCHIVED  = 'archived';

	/**
	 * Trash retention period in days
	 */
	const TRASH_RETENTION_DAYS = 30;

	/**
	 * Get all valid status values
	 *
	 * @return array
	 */
	public static function get_valid_statuses(): array {
		return [
			self::STATUS_DRAFT,
			self::STATUS_PUBLISHED,
			self::STATUS_SCHEDULED,
			self::STATUS_ARCHIVED,
			self::STATUS_TRASH,
		];
	}

	/**
	 * Check if a status value is valid
	 *
	 * @param string $status Status to validate
	 *
	 * @return bool
	 */
	public static function is_valid_status( string $status ): bool {
		return in_array( $status, self::get_valid_statuses(), true );
	}

	/**
	 * CTA types
	 */
	const TYPE_PHONE    = 'phone';
	const TYPE_LINK     = 'link';
	const TYPE_EMAIL    = 'email';
	const TYPE_POPUP    = 'popup';
	const TYPE_SLIDE_IN = 'slide-in';

	/**
	 * Layout types
	 */
	const LAYOUT_BUTTON       = 'button';
	const LAYOUT_CARD_TOP     = 'card-top';
	const LAYOUT_CARD_LEFT    = 'card-left';
	const LAYOUT_CARD_RIGHT   = 'card-right';
	const LAYOUT_CARD_BOTTOM  = 'card-bottom';

	/**
	 * Cache for loaded CTAs
	 *
	 * @var array
	 */
	private array $cache = [];

	/**
	 * Get the full table name with prefix
	 *
	 * @return string
	 */
	public static function get_table_name(): string {
		return CTA_Database::table( CTA_Database::TABLE_CTA_MANAGER );
	}

	/**
	 * Check if the CTA table exists
	 *
	 * @return bool
	 */
	public function table_exists(): bool {
		return CTA_Database::table_exists( self::get_table_name() );
	}

	/**
	 * Generate a UUID v4
	 *
	 * @return string
	 */
	public function generate_uuid(): string {
		return wp_generate_uuid4();
	}

	/**
	 * Generate a unique slug from title
	 *
	 * @param string $title     Title to slugify
	 * @param int    $exclude_id Optional ID to exclude from uniqueness check
	 *
	 * @return string
	 */
	public function generate_slug( string $title, int $exclude_id = 0 ): string {
		$base_slug = sanitize_title( $title );
		if ( empty( $base_slug ) ) {
			$base_slug = 'cta';
		}

		$slug = $base_slug;
		$counter = 1;

		while ( $this->slug_exists( $slug, $exclude_id ) ) {
			$slug = $base_slug . '-' . $counter;
			$counter++;
		}

		return $slug;
	}

	/**
	 * Check if a slug exists
	 *
	 * @param string $slug       Slug to check
	 * @param int    $exclude_id Optional ID to exclude
	 *
	 * @return bool
	 */
	public function slug_exists( string $slug, int $exclude_id = 0 ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		if ( $exclude_id > 0 ) {
			$sql = "SELECT 1 FROM {$table} WHERE slug = %s AND id != %d AND deleted_at IS NULL LIMIT 1";
			$result = CTA_Database::query( $sql, [ $slug, $exclude_id ] );
		} else {
			$sql = "SELECT 1 FROM {$table} WHERE slug = %s AND deleted_at IS NULL LIMIT 1";
			$result = CTA_Database::query( $sql, [ $slug ] );
		}

		return ! empty( $result );
	}

	/**
	 * Get a CTA by ID
	 *
	 * @param int  $id              CTA ID
	 * @param bool $include_trashed Whether to include trashed CTAs (default: true for fetching individual CTAs)
	 *
	 * @return array|null
	 */
	public function get( int $id, bool $include_trashed = true ): ?array {
		if ( isset( $this->cache[ $id ] ) ) {
			return $this->cache[ $id ];
		}

		if ( ! $this->table_exists() ) {
			return null;
		}

		$table = self::get_table_name();

		$sql = "SELECT * FROM {$table} WHERE id = %d AND deleted_at IS NULL LIMIT 1";
		$results = CTA_Database::query( $sql, [ $id ] );

		if ( empty( $results ) ) {
			return null;
		}

		$cta = $this->unpack_to_cta( $results[0] );
		$this->cache[ $id ] = $cta;

		return $cta;
	}

	/**
	 * Get a CTA by UUID
	 *
	 * @param string $uuid CTA UUID
	 *
	 * @return array|null
	 */
	public function get_by_uuid( string $uuid ): ?array {
		if ( ! $this->table_exists() ) {
			return null;
		}

		$table = self::get_table_name();

		$sql = "SELECT * FROM {$table} WHERE uuid = %s AND deleted_at IS NULL LIMIT 1";
		$results = CTA_Database::query( $sql, [ $uuid ] );

		if ( empty( $results ) ) {
			return null;
		}

		return $this->unpack_to_cta( $results[0] );
	}

	/**
	 * Get a CTA by slug
	 *
	 * @param string $slug CTA slug
	 *
	 * @return array|null
	 */
	public function get_by_slug( string $slug ): ?array {
		if ( ! $this->table_exists() ) {
			return null;
		}

		$table = self::get_table_name();

		$sql = "SELECT * FROM {$table} WHERE slug = %s AND deleted_at IS NULL LIMIT 1";
		$results = CTA_Database::query( $sql, [ $slug ] );

		if ( empty( $results ) ) {
			return null;
		}

		return $this->unpack_to_cta( $results[0] );
	}

	/**
	 * Get all CTAs with optional filtering
	 *
	 * @param array $args {
	 *     Optional arguments.
	 *     @type string|array $status      Filter by status (string or array of statuses).
	 *                                     Special values: 'active' = published+scheduled, 'all' = all statuses
	 *     @type string $type        Filter by type (phone/link/email/popup/slide-in)
	 *     @type bool   $is_enabled  Filter by enabled state
	 *     @type bool   $include_deleted Include soft-deleted CTAs
	 *     @type bool   $include_trashed Include trashed CTAs (default: false)
	 *     @type bool   $demo_only   Only return demo CTAs
	 *     @type bool   $exclude_demo Exclude demo CTAs
	 *     @type string $orderby     Order by column (default: id)
	 *     @type string $order       Order direction (ASC/DESC, default: ASC)
	 *     @type int    $limit       Max number of results
	 *     @type int    $offset      Offset for pagination
	 * }
	 *
	 * @return array
	 */
	public function get_all( array $args = [] ): array {
		if ( ! $this->table_exists() ) {
			return [];
		}

		$table = self::get_table_name();

		$defaults = [
			'status'          => null,
			'type'            => null,
			'is_enabled'      => null,
			'include_deleted' => false,
			'include_trashed' => false,
			'demo_only'       => false,
			'exclude_demo'    => false,
			'orderby'         => 'id',
			'order'           => 'ASC',
			'limit'           => 0,
			'offset'          => 0,
		];

		$args = wp_parse_args( $args, $defaults );

		$where = [];
		$params = [];

		// Soft delete filter
		if ( ! $args['include_deleted'] ) {
			$where[] = 'deleted_at IS NULL';
		}

		// Status filter - supports string, array, or special values
		if ( $args['status'] !== null ) {
			$status = $args['status'];

			// Handle special status values
			if ( $status === 'active' ) {
				// Active = Published + Scheduled
				$status = [ self::STATUS_PUBLISHED, self::STATUS_SCHEDULED ];
			} elseif ( $status === 'all' ) {
				// All statuses - no filter
				$status = null;
			}

			if ( $status !== null ) {
				if ( is_array( $status ) ) {
					// Multiple statuses
					$placeholders = implode( ', ', array_fill( 0, count( $status ), '%s' ) );
					$where[]      = "status IN ({$placeholders})";
					$params       = array_merge( $params, $status );
				} else {
					// Single status
					$where[]  = 'status = %s';
					$params[] = $status;
				}
			}
		}

		// Exclude trashed by default (unless specifically included or querying for trash)
		if ( ! $args['include_trashed'] && $args['status'] !== self::STATUS_TRASH ) {
			// Don't add trash exclusion if we're querying for specific status that includes trash
			$querying_for_trash = false;
			if ( is_array( $args['status'] ) ) {
				$querying_for_trash = in_array( self::STATUS_TRASH, $args['status'], true );
			} elseif ( $args['status'] === self::STATUS_TRASH ) {
				$querying_for_trash = true;
			}

			if ( ! $querying_for_trash && $args['status'] !== 'all' ) {
				$where[] = 'status != %s';
				$params[] = self::STATUS_TRASH;
			}
		}

		// Type filter
		if ( $args['type'] !== null ) {
			$where[] = 'type = %s';
			$params[] = $args['type'];
		}

		// Enabled filter
		if ( $args['is_enabled'] !== null ) {
			$where[] = 'is_enabled = %d';
			$params[] = $args['is_enabled'] ? 1 : 0;
		}

		// Demo filters handled after fetching (need to check meta_json)

		$where_clause = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		// Order by
		$allowed_orderby = [ 'id', 'title', 'created_at', 'updated_at', 'priority', 'type', 'status', 'trashed_at' ];
		$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'id';
		$order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		$sql = "SELECT * FROM {$table} {$where_clause} ORDER BY {$orderby} {$order}";

		// Limit and offset
		if ( $args['limit'] > 0 ) {
			$params[] = $args['limit'];
			$params[] = $args['offset'];
			$sql .= ' LIMIT %d OFFSET %d';
		}

		$rows = CTA_Database::query( $sql, $params );

		if ( ! $rows ) {
			return [];
		}

		$ctas = [];
		foreach ( $rows as $row ) {
			$cta = $this->unpack_to_cta( $row );

			// Demo filters
			$is_demo = ! empty( $cta['_demo'] );
			if ( $args['demo_only'] && ! $is_demo ) {
				continue;
			}
			if ( $args['exclude_demo'] && $is_demo ) {
				continue;
			}

			$ctas[] = $cta;
			$this->cache[ (int) $row['id'] ] = $cta;
		}

		return $ctas;
	}

	/**
	 * Count CTAs with optional filtering
	 *
	 * @param array $args Same as get_all() but limit/offset/orderby ignored
	 *
	 * @return int
	 */
	public function count( array $args = [] ): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		$table = self::get_table_name();

		$defaults = [
			'status'          => null,
			'type'            => null,
			'is_enabled'      => null,
			'include_deleted' => false,
			'include_trashed' => false,
			'demo_only'       => false,
			'exclude_demo'    => false,
		];

		$args = wp_parse_args( $args, $defaults );

		// For demo filters we need full scan - optimize later with indexed column
		if ( $args['demo_only'] || $args['exclude_demo'] ) {
			return count( $this->get_all( $args ) );
		}

		$where = [];
		$params = [];

		if ( ! $args['include_deleted'] ) {
			$where[] = 'deleted_at IS NULL';
		}

		// Status filter - supports string, array, or special values
		if ( $args['status'] !== null ) {
			$status = $args['status'];

			// Handle special status values
			if ( $status === 'active' ) {
				$status = [ self::STATUS_PUBLISHED, self::STATUS_SCHEDULED ];
			} elseif ( $status === 'all' ) {
				$status = null;
			}

			if ( $status !== null ) {
				if ( is_array( $status ) ) {
					$placeholders = implode( ', ', array_fill( 0, count( $status ), '%s' ) );
					$where[]      = "status IN ({$placeholders})";
					$params       = array_merge( $params, $status );
				} else {
					$where[]  = 'status = %s';
					$params[] = $status;
				}
			}
		}

		// Exclude trashed by default
		if ( ! $args['include_trashed'] && $args['status'] !== self::STATUS_TRASH ) {
			$querying_for_trash = false;
			if ( is_array( $args['status'] ) ) {
				$querying_for_trash = in_array( self::STATUS_TRASH, $args['status'], true );
			} elseif ( $args['status'] === self::STATUS_TRASH ) {
				$querying_for_trash = true;
			}

			if ( ! $querying_for_trash && $args['status'] !== 'all' ) {
				$where[] = 'status != %s';
				$params[] = self::STATUS_TRASH;
			}
		}

		if ( $args['type'] !== null ) {
			$where[] = 'type = %s';
			$params[] = $args['type'];
		}

		if ( $args['is_enabled'] !== null ) {
			$where[] = 'is_enabled = %d';
			$params[] = $args['is_enabled'] ? 1 : 0;
		}

		$where_clause = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		$sql = "SELECT COUNT(*) as total_count FROM {$table} {$where_clause}";

		$result = CTA_Database::query( $sql, $params );

		return (int) ( $result[0]['total_count'] ?? 0 );
	}

	/**
	 * Create a new CTA
	 *
	 * @param array $data CTA data (unpacked format)
	 *
	 * @return int|false New CTA ID or false on failure
	 */
	public function create( array $data ): int|false {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		$now = current_time( 'mysql' );
		$user_id = get_current_user_id() ?: null;

		// Generate uuid if not provided
		$uuid = ! empty( $data['uuid'] ) ? $data['uuid'] : $this->generate_uuid();

		// Generate slug from title/name
		$title = $data['title'] ?? $data['name'] ?? '';
		$slug = ! empty( $data['slug'] ) ? $data['slug'] : $this->generate_slug( $title );

		// Map status from data, default to draft
		$status = self::STATUS_DRAFT;
		if ( isset( $data['status'] ) ) {
			$status = $data['status'];
		}

		$is_enabled = isset( $data['enabled'] ) ? ( $data['enabled'] ? 1 : 0 ) : 1;

		// Resolve first active / published timestamps
		$first_active_at = $data['first_active_at'] ?? '';
		$published_at    = $data['published_at'] ?? null;
		if ( $status === self::STATUS_SCHEDULED && ! empty( $data['schedule_start'] ) && empty( $first_active_at ) ) {
			$first_active_at = CTA_Sanitizer::sanitize_schedule_date( $data['schedule_start'] ) . ' 00:00:00';
		}
		if ( $status === self::STATUS_PUBLISHED && $is_enabled ) {
			if ( empty( $first_active_at ) ) {
				$first_active_at = $now;
			}
			if ( empty( $published_at ) ) {
				$published_at = $now;
			}
		}

		$data['first_active_at'] = $first_active_at;

		// Pack JSON columns
		$content_json = $this->pack_content_json( $data );
		$style_json = $this->pack_style_json( $data );
		$behavior_json = $this->pack_behavior_json( $data );
		$targeting_json = $this->pack_targeting_json( $data );
		$meta_json = $this->pack_meta_json( $data );

		// Parse schedules
		$schedule_start = ! empty( $data['schedule_start'] )
			? CTA_Sanitizer::sanitize_schedule_date( $data['schedule_start'] )
			: null;
		$schedule_end = ! empty( $data['schedule_end'] )
			? CTA_Sanitizer::sanitize_schedule_date( $data['schedule_end'] )
			: null;

		// Convert empty strings to null for database
		$schedule_start = $schedule_start !== '' ? $schedule_start : null;
		$schedule_end = $schedule_end !== '' ? $schedule_end : null;

		$row_data = [
			'uuid'           => $uuid,
			'slug'           => $slug,
			'title'          => sanitize_text_field( $title ),
			'name'           => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : null,
			'status'         => $status,
			'type'           => $data['type'] ?? self::TYPE_PHONE,
			'layout'         => $data['layout'] ?? self::LAYOUT_BUTTON,
			'is_enabled'     => $is_enabled,
			'visibility'     => $data['visibility'] ?? 'all_devices',
			'schedule_start' => $schedule_start,
			'schedule_end'   => $schedule_end,
			'priority'       => isset( $data['priority'] ) ? (int) $data['priority'] : 0,
			'color'          => $data['color'] ?? null,
			'icon'           => $data['icon'] ?? 'none',
			'author_id'      => $user_id,
			'created_by'     => $user_id,
			'updated_by'     => $user_id,
			'content_json'   => $content_json,
			'style_json'     => $style_json,
			'behavior_json'  => $behavior_json,
			'targeting_json' => $targeting_json,
			'meta_json'      => $meta_json,
			'created_at'     => $data['created_at'] ?? $now,
			'updated_at'     => $now,
			'published_at'   => $published_at,
		];

		$formats = [
			'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s',
			'%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d',
			'%s', '%s', '%s', '%s', '%s', '%s', '%s',
			'%s',
		];

		$result = CTA_Database::insert( $table, $row_data, $formats );

		if ( $result === false ) {
			return false;
		}

		$new_id = CTA_Database::last_insert_id();

		// Clear cache
		$this->clear_cache();

		return $new_id;
	}

	/**
	 * Update an existing CTA
	 *
	 * @param int   $id   CTA ID
	 * @param array $data CTA data to update (unpacked format)
	 *
	 * @return bool
	 */
	public function update( int $id, array $data ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		// Get existing CTA to merge with
		$existing = $this->get( $id );
		if ( ! $existing ) {
			return false;
		}

		$table = self::get_table_name();

		$now = current_time( 'mysql' );
		$user_id = get_current_user_id() ?: null;

		// Merge with existing data
		$merged = array_merge( $existing, $data );

		// Regenerate slug if title changed
		$slug = $existing['slug'] ?? '';
		if ( isset( $data['title'] ) && $data['title'] !== $existing['title'] ) {
			$slug = $this->generate_slug( $data['title'], $id );
		} elseif ( isset( $data['name'] ) && $data['name'] !== ( $existing['name'] ?? '' ) && empty( $existing['title'] ) ) {
			$slug = $this->generate_slug( $data['name'], $id );
		}

		$is_enabled = isset( $merged['enabled'] ) ? ( $merged['enabled'] ? 1 : 0 ) : 1;

		// Resolve first active / published timestamps
		$first_active_at = $merged['first_active_at'] ?? '';
		$published_at    = $merged['published_at'] ?? null;

		if ( $new_status === self::STATUS_SCHEDULED && ! empty( $merged['schedule_start'] ) && empty( $first_active_at ) ) {
			$first_active_at = CTA_Sanitizer::sanitize_schedule_date( $merged['schedule_start'] ) . ' 00:00:00';
		}

		if ( $new_status === self::STATUS_PUBLISHED && $is_enabled ) {
			if ( empty( $first_active_at ) ) {
				$first_active_at = $now;
			}
			if ( empty( $published_at ) ) {
				$published_at = $now;
			}
		}

		$merged['first_active_at'] = $first_active_at;

		// Pack JSON columns with merged data
		$content_json = $this->pack_content_json( $merged );
		$style_json = $this->pack_style_json( $merged );
		$behavior_json = $this->pack_behavior_json( $merged );
		$targeting_json = $this->pack_targeting_json( $merged );
		$meta_json = $this->pack_meta_json( $merged );

		// Clear schedule fields when not using date_range scheduling or when status is non-scheduled
		$new_status = $merged['status'] ?? self::STATUS_PUBLISHED;
		$schedule_type = $merged['schedule_type'] ?? 'date_range';

		// Clear schedule dates if:
		// 1. Schedule type is not date_range (using business_hours or always_on)
		// 2. Status is not scheduled (published, draft, archived, trash)
		if ( $schedule_type !== 'date_range' || in_array( $new_status, [ self::STATUS_DRAFT, self::STATUS_ARCHIVED, self::STATUS_TRASH ], true ) ) {
			$schedule_start = null;
			$schedule_end = null;
		} else {
			$schedule_start = ! empty( $merged['schedule_start'] )
				? CTA_Sanitizer::sanitize_schedule_date( $merged['schedule_start'] )
				: null;
			$schedule_end = ! empty( $merged['schedule_end'] )
				? CTA_Sanitizer::sanitize_schedule_date( $merged['schedule_end'] )
				: null;

			// Convert empty strings to null for database
			$schedule_start = $schedule_start !== '' ? $schedule_start : null;
			$schedule_end = $schedule_end !== '' ? $schedule_end : null;
		}

		$row_data = [
			'slug'           => $slug,
			'title'          => sanitize_text_field( $merged['title'] ?? '' ),
			'name'           => isset( $merged['name'] ) ? sanitize_text_field( $merged['name'] ) : null,
			'status'         => $new_status,
			'type'           => $merged['type'] ?? self::TYPE_PHONE,
			'layout'         => $merged['layout'] ?? self::LAYOUT_BUTTON,
			'is_enabled'     => $is_enabled,
			'visibility'     => $merged['visibility'] ?? 'all_devices',
			'schedule_start' => $schedule_start,
			'schedule_end'   => $schedule_end,
			'priority'       => isset( $merged['priority'] ) ? (int) $merged['priority'] : 0,
			'color'          => $merged['color'] ?? null,
			'icon'           => $merged['icon'] ?? 'none',
			'updated_by'     => $user_id,
			'content_json'   => $content_json,
			'style_json'     => $style_json,
			'behavior_json'  => $behavior_json,
			'targeting_json' => $targeting_json,
			'meta_json'      => $meta_json,
			'updated_at'     => $now,
			'published_at'   => $published_at,
		];

		$formats = [
			'%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s',
			'%s', '%s', '%d', '%s', '%s', '%d',
			'%s', '%s', '%s', '%s', '%s', '%s',
			'%s',
		];

		$result = CTA_Database::update(
			$table,
			$row_data,
			[ 'id' => $id ],
			$formats,
			[ '%d' ]
		);

		// Clear cache
		unset( $this->cache[ $id ] );

		return $result !== false;
	}

	/**
	 * Delete a CTA (hard delete)
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool
	 */
	public function delete( int $id ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		$result = CTA_Database::delete(
			$table,
			[ 'id' => $id ],
			[ '%d' ]
		);

		unset( $this->cache[ $id ] );

		return $result !== false;
	}

	/**
	 * Soft delete a CTA (set deleted_at)
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool
	 */
	public function soft_delete( int $id ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		$result = CTA_Database::update(
			$table,
			[
				'deleted_at' => current_time( 'mysql' ),
				'updated_by' => get_current_user_id() ?: null,
			],
			[ 'id' => $id ],
			[ '%s', '%d' ],
			[ '%d' ]
		);

		unset( $this->cache[ $id ] );

		return $result !== false;
	}

	/**
	 * Restore a soft-deleted CTA
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool
	 */
	public function restore( int $id ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		$result = CTA_Database::update(
			$table,
			[
				'deleted_at' => null,
				'updated_at' => current_time( 'mysql' ),
				'updated_by' => get_current_user_id() ?: null,
			],
			[ 'id' => $id ],
			[ '%s', '%s', '%d' ],
			[ '%d' ]
		);

		return $result !== false;
	}

	/**
	 * Move a CTA to trash (soft-trash with retention tracking)
	 *
	 * Sets status to 'trash', records trashed_at timestamp, and clears schedule fields.
	 * Demo CTAs cannot be trashed - use delete() instead.
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool|string True on success, 'demo_cta' if demo CTA, false on failure
	 */
	public function trash( int $id ): bool|string {
		if ( ! $this->table_exists() ) {
			return false;
		}

		// Check if this is a demo CTA - demo CTAs bypass trash
		$cta = $this->get( $id );
		if ( ! $cta ) {
			return false;
		}

		if ( ! empty( $cta['_demo'] ) ) {
			return 'demo_cta';
		}

		$table = self::get_table_name();
		$now   = current_time( 'mysql' );

		$result = CTA_Database::update(
			$table,
			[
				'status'         => self::STATUS_TRASH,
				'trashed_at'     => $now,
				'schedule_start' => null,
				'schedule_end'   => null,
				'updated_at'     => $now,
				'updated_by'     => get_current_user_id() ?: null,
			],
			[ 'id' => $id ],
			[ '%s', '%s', '%s', '%s', '%s', '%d' ],
			[ '%d' ]
		);

		unset( $this->cache[ $id ] );

		return $result !== false;
	}

	/**
	 * Restore a CTA from trash
	 *
	 * Restores status to draft and clears trashed_at timestamp.
	 *
	 * @param int    $id              CTA ID
	 * @param string $restore_status  Status to restore to (default: draft)
	 *
	 * @return bool
	 */
	public function restore_from_trash( int $id, string $restore_status = self::STATUS_DRAFT ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		// Validate restore status (cannot restore to trash)
		$valid_statuses = [ self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_SCHEDULED, self::STATUS_ARCHIVED ];
		if ( ! in_array( $restore_status, $valid_statuses, true ) ) {
			$restore_status = self::STATUS_DRAFT;
		}

		$table = self::get_table_name();

		$result = CTA_Database::update(
			$table,
			[
				'status'     => $restore_status,
				'trashed_at' => null,
				'updated_at' => current_time( 'mysql' ),
				'updated_by' => get_current_user_id() ?: null,
			],
			[ 'id' => $id ],
			[ '%s', '%s', '%s', '%d' ],
			[ '%d' ]
		);

		unset( $this->cache[ $id ] );

		return $result !== false;
	}

	/**
	 * Archive a CTA
	 *
	 * Sets status to 'archived' and clears schedule fields.
	 * Demo CTAs cannot be archived - use delete() instead.
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool|string True on success, 'demo_cta' if demo CTA, false on failure
	 */
	public function archive( int $id ): bool|string {
		if ( ! $this->table_exists() ) {
			return false;
		}

		// Check if this is a demo CTA - demo CTAs bypass archive
		$cta = $this->get( $id );
		if ( ! $cta ) {
			return false;
		}

		if ( ! empty( $cta['_demo'] ) ) {
			return 'demo_cta';
		}

		$table = self::get_table_name();
		$now   = current_time( 'mysql' );

		$result = CTA_Database::update(
			$table,
			[
				'status'         => self::STATUS_ARCHIVED,
				'schedule_start' => null,
				'schedule_end'   => null,
				'updated_at'     => $now,
				'updated_by'     => get_current_user_id() ?: null,
			],
			[ 'id' => $id ],
			[ '%s', '%s', '%s', '%s', '%d' ],
			[ '%d' ]
		);

		unset( $this->cache[ $id ] );

		return $result !== false;
	}

	/**
	 * Get all trashed CTAs
	 *
	 * @return array
	 */
	public function get_trashed(): array {
		return $this->get_all( [ 'status' => self::STATUS_TRASH ] );
	}

	/**
	 * Get trashed CTAs that have exceeded the retention period
	 *
	 * @param int $retention_days Days before permanent deletion (default: 30)
	 *
	 * @return array
	 */
	public function get_expired_trash( int $retention_days = self::TRASH_RETENTION_DAYS ): array {
		if ( ! $this->table_exists() ) {
			return [];
		}

		$table       = self::get_table_name();
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

		$sql = "SELECT * FROM {$table} WHERE status = %s AND trashed_at IS NOT NULL AND trashed_at < %s AND deleted_at IS NULL";
		$rows = CTA_Database::query( $sql, [ self::STATUS_TRASH, $cutoff_date ] );

		if ( ! $rows ) {
			return [];
		}

		$ctas = [];
		foreach ( $rows as $row ) {
			$ctas[] = $this->unpack_to_cta( $row );
		}

		return $ctas;
	}

	/**
	 * Empty all trashed CTAs (permanent delete)
	 *
	 * @return int Number of CTAs permanently deleted
	 */
	public function empty_trash(): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		$table = self::get_table_name();

		$sql = "DELETE FROM {$table} WHERE status = %s";
		$result = CTA_Database::query( $sql, [ self::STATUS_TRASH ] );

		$this->clear_cache();

		return $result !== false ? count( $result ) : 0;
	}

	/**
	 * Empty trashed CTAs that have exceeded retention period
	 *
	 * @param int $retention_days Days before permanent deletion (default: 30)
	 *
	 * @return int Number of CTAs permanently deleted
	 */
	public function empty_expired_trash( int $retention_days = self::TRASH_RETENTION_DAYS ): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		$table       = self::get_table_name();
		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

		// Count before deleting
		$count_sql = "SELECT COUNT(*) as count FROM {$table} WHERE status = %s AND trashed_at IS NOT NULL AND trashed_at < %s";
		$count_result = CTA_Database::query( $count_sql, [ self::STATUS_TRASH, $cutoff_date ] );
		$count = (int) ( $count_result[0]['count'] ?? 0 );

		if ( $count === 0 ) {
			return 0;
		}

		$sql = "DELETE FROM {$table} WHERE status = %s AND trashed_at IS NOT NULL AND trashed_at < %s";
		CTA_Database::query( $sql, [ self::STATUS_TRASH, $cutoff_date ] );

		$this->clear_cache();

		return $count;
	}

	/**
	 * Count trashed CTAs
	 *
	 * @return int
	 */
	public function count_trashed(): int {
		return $this->count( [ 'status' => self::STATUS_TRASH ] );
	}

	/**
	 * Check if a CTA is a demo CTA
	 *
	 * @param int $id CTA ID
	 *
	 * @return bool
	 */
	public function is_demo_cta( int $id ): bool {
		$cta = $this->get( $id );
		return $cta !== null && ! empty( $cta['_demo'] );
	}

	/**
	 * Delete all demo CTAs
	 *
	 * @return int Number of deleted rows
	 */
	public function delete_demo_ctas(): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		$table = self::get_table_name();

		// Find demo CTAs by checking meta_json for _demo flag
		$sql = "DELETE FROM {$table} WHERE meta_json LIKE '%\"_demo\":true%' OR meta_json LIKE '%\"_demo\": true%'";
		$result = CTA_Database::query( $sql, [] );

		$this->clear_cache();

		return $result !== false ? (int) $result : 0;
	}

	/**
	 * Get demo CTA IDs
	 *
	 * @return array
	 */
	public function get_demo_cta_ids(): array {
		$demo_ctas = $this->get_all( [ 'demo_only' => true ] );
		return array_column( $demo_ctas, 'id' );
	}

	/**
	 * Truncate the CTA table
	 *
	 * @return bool
	 */
	public function truncate(): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		$result = CTA_Database::truncate( $table );

		$this->clear_cache();

		return $result;
	}

	/**
	 * Clear the cache
	 *
	 * @return void
	 */
	public function clear_cache(): void {
		$this->cache = [];
	}

	// =========================================================================
	// JSON Packing Methods
	// =========================================================================

	/**
	 * Pack content-related fields into JSON
	 *
	 * @param array $cta CTA data
	 *
	 * @return string JSON string
	 */
	private function pack_content_json( array $cta ): string {
		$content = [
			'tagline'         => $cta['tagline'] ?? '',
			'body_text'       => $cta['body_text'] ?? '',
			'button_text'     => $cta['button_text'] ?? 'Call Now',
			'phone_number'    => $cta['phone_number'] ?? '',
			'email_to'        => $cta['email_to'] ?? '',
			'link_url'        => $cta['link_url'] ?? '',
			'link_target'     => $cta['link_target'] ?? '_self',
			'link_target_new_tab' => $cta['link_target_new_tab'] ?? false,
			'data_attributes' => $cta['data_attributes'] ?? [],
		];

		return wp_json_encode( $content );
	}

	/**
	 * Pack style-related fields into JSON
	 *
	 * @param array $cta CTA data
	 *
	 * @return string JSON string
	 */
	private function pack_style_json( array $cta ): string {
		$style = [
			// Title formatting
			'title_font_size'        => $cta['title_font_size'] ?? '24px',
			'title_font_family'      => $cta['title_font_family'] ?? 'inherit',
			'title_font_weight'      => $cta['title_font_weight'] ?? '700',
			'title_color'            => $cta['title_color'] ?? '#1e1e1e',
			'title_alignment'        => $cta['title_alignment'] ?? 'left',
			// Tagline formatting
			'tagline_font_size'      => $cta['tagline_font_size'] ?? '14px',
			'tagline_font_family'    => $cta['tagline_font_family'] ?? 'inherit',
			'tagline_font_weight'    => $cta['tagline_font_weight'] ?? '400',
			'tagline_color'          => $cta['tagline_color'] ?? '#757575',
			'tagline_alignment'      => $cta['tagline_alignment'] ?? 'left',
			// Body formatting
			'body_font_size'         => $cta['body_font_size'] ?? '14px',
			'body_font_family'       => $cta['body_font_family'] ?? 'inherit',
			'body_font_weight'       => $cta['body_font_weight'] ?? '400',
			'body_color'             => $cta['body_color'] ?? '#1e1e1e',
			'body_alignment'         => $cta['body_alignment'] ?? 'left',
			// Button text formatting
			'button_text_font_size'   => $cta['button_text_font_size'] ?? '16px',
			'button_text_font_family' => $cta['button_text_font_family'] ?? 'inherit',
			'button_text_font_weight' => $cta['button_text_font_weight'] ?? '600',
			'button_text_color'       => $cta['button_text_color'] ?? '#ffffff',
			'button_text_alignment'   => $cta['button_text_alignment'] ?? 'center',
			'button_alignment'        => $cta['button_alignment'] ?? 'center',
			'button_width'            => $cta['button_width'] ?? 'auto',
			// Card styling
			'card_background'        => $cta['card_background'] ?? '#ffffff',
			'card_background_type'   => $cta['card_background_type'] ?? 'solid',
			'card_gradient'          => $cta['card_gradient'] ?? '',
			'card_padding_top'       => $cta['card_padding_top'] ?? '24px',
			'card_padding_right'     => $cta['card_padding_right'] ?? '24px',
			'card_padding_bottom'    => $cta['card_padding_bottom'] ?? '24px',
			'card_padding_left'      => $cta['card_padding_left'] ?? '24px',
			'card_padding_linked'    => $cta['card_padding_linked'] ?? true,
			'card_margin_top'        => $cta['card_margin_top'] ?? '0px',
			'card_margin_right'      => $cta['card_margin_right'] ?? '0px',
			'card_margin_bottom'     => $cta['card_margin_bottom'] ?? '0px',
			'card_margin_left'       => $cta['card_margin_left'] ?? '0px',
			'card_margin_linked'     => $cta['card_margin_linked'] ?? true,
			'card_border_width'      => $cta['card_border_width'] ?? '1px',
			'card_border_color'      => $cta['card_border_color'] ?? '#dcdcde',
			'card_border_radius'     => $cta['card_border_radius'] ?? '12px',
			// Button background styling
			'background_type'        => $cta['background_type'] ?? 'solid',
			'button_background'      => $cta['button_background'] ?? '',
			'button_background_type' => $cta['button_background_type'] ?? 'solid',
			'button_gradient'        => $cta['button_gradient'] ?? '',
			'gradient_type'          => $cta['gradient_type'] ?? 'linear',
			'gradient_start'         => $cta['gradient_start'] ?? '#667eea',
			'gradient_end'           => $cta['gradient_end'] ?? '#764ba2',
			'gradient_angle'         => $cta['gradient_angle'] ?? '90',
			'gradient_start_position' => $cta['gradient_start_position'] ?? '0',
			'gradient_end_position'  => $cta['gradient_end_position'] ?? '100',
			// Button border styling
			'border_style'           => $cta['border_style'] ?? 'none',
			'border_color'           => $cta['border_color'] ?? '#667eea',
			'border_width_top'       => $cta['border_width_top'] ?? '2px',
			'border_width_right'     => $cta['border_width_right'] ?? '2px',
			'border_width_bottom'    => $cta['border_width_bottom'] ?? '2px',
			'border_width_left'      => $cta['border_width_left'] ?? '2px',
			'border_width_linked'    => $cta['border_width_linked'] ?? '1',
			// Button border radius
			'border_radius_top_left'     => $cta['border_radius_top_left'] ?? '8px',
			'border_radius_top_right'    => $cta['border_radius_top_right'] ?? '8px',
			'border_radius_bottom_right' => $cta['border_radius_bottom_right'] ?? '8px',
			'border_radius_bottom_left'  => $cta['border_radius_bottom_left'] ?? '8px',
			'border_radius_linked'       => $cta['border_radius_linked'] ?? '1',
			// Button padding
			'padding_top'            => $cta['padding_top'] ?? '12px',
			'padding_right'          => $cta['padding_right'] ?? '24px',
			'padding_bottom'         => $cta['padding_bottom'] ?? '12px',
			'padding_left'           => $cta['padding_left'] ?? '24px',
			'padding_linked'         => $cta['padding_linked'] ?? '0',
			// Animations
			'button_animation'       => $cta['button_animation'] ?? 'none',
			'icon_animation'         => $cta['icon_animation'] ?? 'none',
			// Show icon (icons are Pro feature, default to false)
			'show_icon'              => $cta['show_icon'] ?? false,
		];

		return wp_json_encode( $style );
	}

	/**
	 * Pack behavior-related fields into JSON
	 *
	 * @param array $cta CTA data
	 *
	 * @return string JSON string
	 */
	private function pack_behavior_json( array $cta ): string {
		$behavior = [
			'slide_in_settings' => $cta['slide_in_settings'] ?? [
				'trigger_type'       => 'time',
				'trigger_delay'      => 3,
				'trigger_scroll'     => 50,
				'position'           => 'bottom-right',
				'show_animation'     => 'slide-in',
				'hide_animation'     => 'slide-out',
				'auto_dismiss'       => true,
				'auto_dismiss_delay' => 10,
				'dismiss_behavior'   => 'session',
			],
			'blacklist_urls'    => $cta['blacklist_urls'] ?? [],
		];

		return wp_json_encode( $behavior );
	}

	/**
	 * Pack targeting-related fields into JSON
	 *
	 * @param array $cta CTA data
	 *
	 * @return string JSON string
	 */
	private function pack_targeting_json( array $cta ): string {
		$targeting = [
			// Device visibility is stored in column, but can add more rules here
			'device_rules' => [],
		];

		return wp_json_encode( $targeting );
	}

	/**
	 * Pack meta fields into JSON
	 *
	 * @param array $cta CTA data
	 *
	 * @return string JSON string
	 */
	private function pack_meta_json( array $cta ): string {
		$meta = [
			'_demo'           => $cta['_demo'] ?? false,
			'wrapper_id'      => $cta['wrapper_id'] ?? '',
			'wrapper_classes' => $cta['wrapper_classes'] ?? '',
			'cta_html_id'     => $cta['cta_html_id'] ?? '',
			'cta_classes'     => $cta['cta_classes'] ?? '',
			'schedule_type'   => $cta['schedule_type'] ?? 'date_range',
			'first_active_at' => $cta['first_active_at'] ?? '',
		];

		return wp_json_encode( $meta );
	}

	// =========================================================================
	// JSON Unpacking Methods
	// =========================================================================

	/**
	 * Unpack a table row to CTA array format
	 *
	 * @param array $row Database row
	 *
	 * @return array CTA data array
	 */
	public function unpack_to_cta( array $row ): array {
		// Decode JSON columns
		$content = ! empty( $row['content_json'] ) ? json_decode( $row['content_json'], true ) : [];
		$style = ! empty( $row['style_json'] ) ? json_decode( $row['style_json'], true ) : [];
		$behavior = ! empty( $row['behavior_json'] ) ? json_decode( $row['behavior_json'], true ) : [];
		$targeting = ! empty( $row['targeting_json'] ) ? json_decode( $row['targeting_json'], true ) : [];
		$meta = ! empty( $row['meta_json'] ) ? json_decode( $row['meta_json'], true ) : [];

		// Handle JSON decode failures
		if ( ! is_array( $content ) ) $content = [];
		if ( ! is_array( $style ) ) $style = [];
		if ( ! is_array( $behavior ) ) $behavior = [];
		if ( ! is_array( $targeting ) ) $targeting = [];
		if ( ! is_array( $meta ) ) $meta = [];

		// Build CTA array
		$cta = [
			// Core fields from columns
			'id'             => (int) $row['id'],
			'uuid'           => $row['uuid'],
			'slug'           => $row['slug'],
			'name'           => $row['name'] ?? $row['title'],
			'title'          => $row['title'],
			'type'           => $row['type'],
			'layout'         => $row['layout'],
			'visibility'     => $row['visibility'],
			'enabled'        => (bool) $row['is_enabled'],
			'status'         => $row['status'],
			'schedule_start' => self::normalize_schedule_date( $row['schedule_start'] ),
			'schedule_end'   => self::normalize_schedule_date( $row['schedule_end'] ),
			'trashed_at'     => $row['trashed_at'] ?? null,
			'priority'       => (int) $row['priority'],
			'color'          => $row['color'],
			'icon'           => $row['icon'],
			'created_at'     => $row['created_at'],
			'updated_at'     => $row['updated_at'],
			'published_at'   => $row['published_at'] ?? null,
			'author_id'      => isset( $row['author_id'] ) ? (int) $row['author_id'] : null,
			'created_by'     => isset( $row['created_by'] ) ? (int) $row['created_by'] : null,

			// Content fields
			'tagline'         => $content['tagline'] ?? '',
			'body_text'       => $content['body_text'] ?? '',
			'button_text'     => $content['button_text'] ?? 'Call Now',
			'phone_number'    => $content['phone_number'] ?? '',
			'email_to'        => $content['email_to'] ?? '',
			'link_url'        => $content['link_url'] ?? '',
			'link_target'     => $content['link_target'] ?? '_self',
			'link_target_new_tab' => $content['link_target_new_tab'] ?? false,
			'data_attributes' => $content['data_attributes'] ?? [],

			// Style fields
			'title_font_size'        => $style['title_font_size'] ?? '24px',
			'title_font_family'      => $style['title_font_family'] ?? 'inherit',
			'title_font_weight'      => $style['title_font_weight'] ?? '700',
			'title_color'            => $style['title_color'] ?? '#1e1e1e',
			'title_alignment'        => $style['title_alignment'] ?? 'left',
			'tagline_font_size'      => $style['tagline_font_size'] ?? '14px',
			'tagline_font_family'    => $style['tagline_font_family'] ?? 'inherit',
			'tagline_font_weight'    => $style['tagline_font_weight'] ?? '400',
			'tagline_color'          => $style['tagline_color'] ?? '#757575',
			'tagline_alignment'      => $style['tagline_alignment'] ?? 'left',
			'body_font_size'         => $style['body_font_size'] ?? '14px',
			'body_font_family'       => $style['body_font_family'] ?? 'inherit',
			'body_font_weight'       => $style['body_font_weight'] ?? '400',
			'body_color'             => $style['body_color'] ?? '#1e1e1e',
			'body_alignment'         => $style['body_alignment'] ?? 'left',
			'button_text_font_size'   => $style['button_text_font_size'] ?? '16px',
			'button_text_font_family' => $style['button_text_font_family'] ?? 'inherit',
			'button_text_font_weight' => $style['button_text_font_weight'] ?? '600',
			'button_text_color'       => $style['button_text_color'] ?? '#ffffff',
			'button_text_alignment'   => $style['button_text_alignment'] ?? 'center',
			'button_alignment'        => $style['button_alignment'] ?? 'center',
			'button_width'            => $style['button_width'] ?? 'auto',
			'card_background'        => $style['card_background'] ?? '#ffffff',
			'card_background_type'   => $style['card_background_type'] ?? 'solid',
			'card_gradient'          => $style['card_gradient'] ?? '',
			'card_padding_top'       => $style['card_padding_top'] ?? '24px',
			'card_padding_right'     => $style['card_padding_right'] ?? '24px',
			'card_padding_bottom'    => $style['card_padding_bottom'] ?? '24px',
			'card_padding_left'      => $style['card_padding_left'] ?? '24px',
			'card_padding_linked'    => $style['card_padding_linked'] ?? true,
			'card_margin_top'        => $style['card_margin_top'] ?? '0px',
			'card_margin_right'      => $style['card_margin_right'] ?? '0px',
			'card_margin_bottom'     => $style['card_margin_bottom'] ?? '0px',
			'card_margin_left'       => $style['card_margin_left'] ?? '0px',
			'card_margin_linked'     => $style['card_margin_linked'] ?? true,
			'card_border_width'      => $style['card_border_width'] ?? '1px',
			'card_border_color'      => $style['card_border_color'] ?? '#dcdcde',
			'card_border_radius'     => $style['card_border_radius'] ?? '12px',
			// Button background styling
			'background_type'        => $style['background_type'] ?? 'solid',
			'button_background'      => $style['button_background'] ?? '',
			'button_background_type' => $style['button_background_type'] ?? 'solid',
			'button_gradient'        => $style['button_gradient'] ?? '',
			'gradient_type'          => $style['gradient_type'] ?? 'linear',
			'gradient_start'         => $style['gradient_start'] ?? '#667eea',
			'gradient_end'           => $style['gradient_end'] ?? '#764ba2',
			'gradient_angle'         => $style['gradient_angle'] ?? '90',
			'gradient_start_position' => $style['gradient_start_position'] ?? '0',
			'gradient_end_position'  => $style['gradient_end_position'] ?? '100',
			// Button border styling
			'border_style'           => $style['border_style'] ?? 'none',
			'border_color'           => $style['border_color'] ?? '#667eea',
			'border_width_top'       => $style['border_width_top'] ?? '2px',
			'border_width_right'     => $style['border_width_right'] ?? '2px',
			'border_width_bottom'    => $style['border_width_bottom'] ?? '2px',
			'border_width_left'      => $style['border_width_left'] ?? '2px',
			'border_width_linked'    => $style['border_width_linked'] ?? '1',
			// Button border radius
			'border_radius_top_left'     => $style['border_radius_top_left'] ?? '8px',
			'border_radius_top_right'    => $style['border_radius_top_right'] ?? '8px',
			'border_radius_bottom_right' => $style['border_radius_bottom_right'] ?? '8px',
			'border_radius_bottom_left'  => $style['border_radius_bottom_left'] ?? '8px',
			'border_radius_linked'       => $style['border_radius_linked'] ?? '1',
			// Button padding
			'padding_top'            => $style['padding_top'] ?? '12px',
			'padding_right'          => $style['padding_right'] ?? '24px',
			'padding_bottom'         => $style['padding_bottom'] ?? '12px',
			'padding_left'           => $style['padding_left'] ?? '24px',
			'padding_linked'         => $style['padding_linked'] ?? '0',
			// Animations
			'button_animation'       => $style['button_animation'] ?? 'none',
			'icon_animation'         => $style['icon_animation'] ?? 'none',
			'show_icon'              => $style['show_icon'] ?? false,

			// Behavior fields
			'slide_in_settings' => $behavior['slide_in_settings'] ?? [
				'trigger_type'       => 'time',
				'trigger_delay'      => 3,
				'trigger_scroll'     => 50,
				'position'           => 'bottom-right',
				'show_animation'     => 'slide-in',
				'hide_animation'     => 'slide-out',
				'auto_dismiss'       => true,
				'auto_dismiss_delay' => 10,
				'dismiss_behavior'   => 'session',
			],
			'blacklist_urls'    => $behavior['blacklist_urls'] ?? [],

			// Meta fields
			'_demo'           => $meta['_demo'] ?? false,
			'wrapper_id'      => $meta['wrapper_id'] ?? '',
			'wrapper_classes' => $meta['wrapper_classes'] ?? '',
			'cta_html_id'     => $meta['cta_html_id'] ?? '',
			'cta_classes'     => $meta['cta_classes'] ?? '',
			'schedule_type'   => $meta['schedule_type'] ?? 'date_range',
			'first_active_at' => $meta['first_active_at'] ?? '',
		];

		return $cta;
	}

	/**
	 * Normalize schedule date to YYYY-MM-DD format
	 *
	 * Converts datetime values from database to date-only format for comparison.
	 *
	 * @param string|null $datetime Datetime value from database
	 * @return string Empty string or YYYY-MM-DD date
	 */
	private static function normalize_schedule_date( ?string $datetime ): string {
		if ( empty( $datetime ) ) {
			return '';
		}

		// Extract date part: "2024-01-15 00:00:00" -> "2024-01-15"
		// If already in date format, this just returns first 10 chars
		return substr( $datetime, 0, 10 );
	}
}
