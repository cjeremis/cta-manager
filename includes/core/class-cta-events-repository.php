<?php
/**
 * Events Repository Handler
 *
 * Handles analytics event storage and retrieval operations.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CTA_Database' ) ) {
	require_once CTA_PLUGIN_DIR . 'includes/core/class-cta-database.php';
}

class CTA_Events_Repository {

	use CTA_Singleton;

	/**
	 * Get the full table name with prefix
	 *
	 * @return string
	 */
	public static function get_table_name(): string {
		return CTA_Database::table( CTA_Database::TABLE_EVENTS );
	}

	/**
	 * Check if the events table exists
	 *
	 * @return bool
	 */
	public function table_exists(): bool {
		return CTA_Database::table_exists( self::get_table_name() );
	}

	/**
	 * Compute page URL hash for faster grouping
	 *
	 * @param string $url Page URL
	 *
	 * @return string Binary MD5 hash (16 bytes)
	 */
	public static function compute_page_url_hash( string $url ): string {
		return md5( $url );
	}

	/**
	 * Insert a new event
	 *
	 * @param array $event_data Event data with keys:
	 *   - event_type (required): string - impression/click/close/conversion/hook_execution
	 *   - cta_id (required): int
	 *   - cta_title: string
	 *   - cta_uuid: string (36 chars)
	 *   - page_url: string
	 *   - page_title: string
	 *   - referrer: string
	 *   - device: string (desktop/tablet/mobile)
	 *   - session_id: string
	 *   - visitor_id: string
	 *   - user_id: int
	 *   - experiment_key: string
	 *   - variant: string
	 *   - value: float
	 *   - currency: string (3 chars)
	 *   - ip_address: string
	 *   - user_agent: string
	 *   - context_json: array (will be JSON encoded)
	 *   - meta_json: array (will be JSON encoded)
	 *   - occurred_at: string (datetime, defaults to current time)
	 *
	 * @return int|false Insert ID or false on failure
	 */
	public function insert( array $event_data ): int|false {
		if ( ! $this->table_exists() ) {
			return false;
		}

		$table = self::get_table_name();

		// Required fields
		if ( empty( $event_data['event_type'] ) || ! isset( $event_data['cta_id'] ) ) {
			return false;
		}

		// Compute page_url_hash if page_url provided
		$page_url_hash = null;
		if ( ! empty( $event_data['page_url'] ) ) {
			$page_url_hash = self::compute_page_url_hash( $event_data['page_url'] );
		}

		// Encode JSON fields
		$context_json = null;
		if ( ! empty( $event_data['context_json'] ) && is_array( $event_data['context_json'] ) ) {
			$context_json = wp_json_encode( $event_data['context_json'] );
		}

		$meta_json = null;
		if ( ! empty( $event_data['meta_json'] ) && is_array( $event_data['meta_json'] ) ) {
			$meta_json = wp_json_encode( $event_data['meta_json'] );
		}

		$data = [
			'event_type'     => sanitize_text_field( $event_data['event_type'] ),
			'cta_id'         => absint( $event_data['cta_id'] ),
			'cta_title'      => isset( $event_data['cta_title'] ) ? sanitize_text_field( $event_data['cta_title'] ) : '',
			'cta_uuid'       => isset( $event_data['cta_uuid'] ) ? sanitize_text_field( $event_data['cta_uuid'] ) : null,
			'page_url'       => isset( $event_data['page_url'] ) ? esc_url_raw( $event_data['page_url'] ) : null,
			'page_url_hash'  => $page_url_hash,
			'page_title'     => isset( $event_data['page_title'] ) ? sanitize_text_field( $event_data['page_title'] ) : null,
			'referrer'       => isset( $event_data['referrer'] ) ? esc_url_raw( $event_data['referrer'] ) : null,
			'device'         => isset( $event_data['device'] ) ? sanitize_text_field( $event_data['device'] ) : null,
			'session_id'     => isset( $event_data['session_id'] ) ? sanitize_text_field( $event_data['session_id'] ) : null,
			'visitor_id'     => isset( $event_data['visitor_id'] ) && $event_data['visitor_id'] ? absint( $event_data['visitor_id'] ) : null,
			'user_id'        => isset( $event_data['user_id'] ) ? absint( $event_data['user_id'] ) : null,
			'experiment_key' => isset( $event_data['experiment_key'] ) ? sanitize_text_field( $event_data['experiment_key'] ) : null,
			'variant'        => isset( $event_data['variant'] ) ? sanitize_text_field( $event_data['variant'] ) : null,
			'value'          => isset( $event_data['value'] ) ? (float) $event_data['value'] : null,
			'currency'       => isset( $event_data['currency'] ) ? strtoupper( substr( sanitize_text_field( $event_data['currency'] ), 0, 3 ) ) : null,
			'ip_address'     => isset( $event_data['ip_address'] ) ? sanitize_text_field( $event_data['ip_address'] ) : null,
			'user_agent'     => isset( $event_data['user_agent'] ) ? sanitize_text_field( substr( $event_data['user_agent'], 0, 512 ) ) : null,
			'context_json'   => $context_json,
			'meta_json'      => $meta_json,
			'occurred_at'    => isset( $event_data['occurred_at'] ) ? $event_data['occurred_at'] : current_time( 'mysql' ),
		];

		return CTA_Database::insert_with_json(
			self::get_table_name(),
			$data,
			[ 'context_json', 'meta_json' ]
		);
	}

	/**
	 * Get an event by ID
	 *
	 * @param int $id Event ID
	 *
	 * @return array|null Event data or null if not found
	 */
	public function get_by_id( int $id ): ?array {
		if ( ! $this->table_exists() ) {
			return null;
		}

		$row = CTA_Database::get_by_id( self::get_table_name(), $id );

		if ( ! $row ) {
			return null;
		}

		// Decode JSON fields
		$row['context'] = CTA_Database::decode_json( $row['context_json'] ?? null );
		$row['meta']    = CTA_Database::decode_json( $row['meta_json'] ?? null );

		return $row;
	}

	/**
	 * Delete an event by ID
	 *
	 * @param int $id Event ID
	 *
	 * @return bool Success
	 */
	public function delete( int $id ): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		return CTA_Database::delete_by_id( self::get_table_name(), $id );
	}

	/**
	 * Delete all events for a CTA
	 *
	 * @param int $cta_id CTA ID
	 *
	 * @return int Number of rows deleted
	 */
	public function delete_by_cta_id( int $cta_id ): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		$result = CTA_Database::delete(
			self::get_table_name(),
			[ 'cta_id' => $cta_id ],
			[ '%d' ]
		);

		return $result !== false ? $result : 0;
	}

	/**
	 * Get daily statistics
	 *
	 * @param string   $start_date Start date (Y-m-d)
	 * @param string   $end_date   End date (Y-m-d)
	 * @param int|null $cta_id     Optional CTA ID filter
	 *
	 * @return array Results grouped by date, cta_id, event_type
	 */
	public function get_daily_stats( string $start_date, string $end_date, ?int $cta_id = null ): array {
		if ( ! $this->table_exists() ) {
			return [];
		}

		$table  = self::get_table_name();
		$sql    = "SELECT DATE(occurred_at) as date, cta_id, event_type, COUNT(*) as count
			FROM {$table}
			WHERE DATE(occurred_at) >= %s AND DATE(occurred_at) <= %s";
		$values = [ $start_date, $end_date ];

		if ( $cta_id !== null ) {
			$sql     .= ' AND cta_id = %d';
			$values[] = $cta_id;
		}

		$sql .= ' GROUP BY DATE(occurred_at), cta_id, event_type ORDER BY occurred_at ASC';

		return CTA_Database::query( $sql, $values );
	}

	/**
	 * Get type statistics (aggregated by CTA type)
	 *
	 * @param string   $start_date Start date (Y-m-d)
	 * @param string   $end_date   End date (Y-m-d)
	 * @param int|null $cta_id     Optional CTA ID filter
	 *
	 * @return array Results grouped by date, cta_id, event_type
	 */
	public function get_type_stats( string $start_date, string $end_date, ?int $cta_id = null ): array {
		// Same as daily stats - type mapping happens in the API layer
		return $this->get_daily_stats( $start_date, $end_date, $cta_id );
	}

	/**
	 * Get top CTAs by clicks/impressions
	 *
	 * @param string $start_date Start date (Y-m-d)
	 * @param string $end_date   End date (Y-m-d)
	 *
	 * @return array Results with cta_id, cta_title, event_type, count
	 */
	public function get_top_ctas( string $start_date, string $end_date ): array {
		if ( ! $this->table_exists() ) {
			return [];
		}

		$table  = self::get_table_name();
		$sql    = "SELECT
				cta_id,
				cta_title,
				event_type,
				COUNT(*) as count
			FROM {$table}
			WHERE DATE(occurred_at) >= %s AND DATE(occurred_at) <= %s
			GROUP BY cta_id, event_type
			ORDER BY cta_id, event_type";

		return CTA_Database::query( $sql, [ $start_date, $end_date ] );
	}

	/**
	 * Get events with pagination and filtering
	 *
	 * @param array $filters Filter options:
	 *   - start_date: string (Y-m-d)
	 *   - end_date: string (Y-m-d)
	 *   - event_type: string
	 *   - cta_id: int
	 *   - search: string (searches page_url, cta_title, referrer)
	 *   - sort_by: string (occurred_at, event_type, cta_title, device, page_url)
	 *   - sort_order: string (ASC/DESC)
	 * @param int   $page     Page number (1-based)
	 * @param int   $per_page Items per page
	 *
	 * @return array ['events' => array, 'total' => int]
	 */
	public function get_events( array $filters, int $page = 1, int $per_page = 50 ): array {
		if ( ! $this->table_exists() ) {
			return [ 'events' => [], 'total' => 0 ];
		}

		$table      = self::get_table_name();
		$offset     = ( $page - 1 ) * $per_page;
		$where_sql  = '1=1';
		$where_vals = [];

		if ( ! empty( $filters['start_date'] ) ) {
			$where_sql  .= ' AND DATE(occurred_at) >= %s';
			$where_vals[] = $filters['start_date'];
		}
		if ( ! empty( $filters['end_date'] ) ) {
			$where_sql  .= ' AND DATE(occurred_at) <= %s';
			$where_vals[] = $filters['end_date'];
		}
		if ( ! empty( $filters['event_type'] ) && $filters['event_type'] !== 'all' ) {
			$where_sql  .= ' AND event_type = %s';
			$where_vals[] = $filters['event_type'];
		}
		if ( ! empty( $filters['cta_id'] ) ) {
			$where_sql  .= ' AND cta_id = %d';
			$where_vals[] = (int) $filters['cta_id'];
		}
		if ( ! empty( $filters['search'] ) ) {
			$search       = '%' . self::escape_like( $filters['search'] ) . '%';
			$where_sql   .= ' AND (page_url LIKE %s OR cta_title LIKE %s OR referrer LIKE %s)';
			$where_vals[] = $search;
			$where_vals[] = $search;
			$where_vals[] = $search;
		}

		// Validate sort parameters
		$allowed_sort = [ 'occurred_at', 'event_type', 'cta_title', 'device', 'page_url' ];
		$sort_by      = isset( $filters['sort_by'] ) && in_array( $filters['sort_by'], $allowed_sort, true )
			? $filters['sort_by']
			: 'occurred_at';
		$sort_order   = isset( $filters['sort_order'] ) && strtoupper( $filters['sort_order'] ) === 'ASC'
			? 'ASC'
			: 'DESC';

		$count_sql = "SELECT COUNT(*) as total_count FROM {$table} WHERE {$where_sql}";
		$count     = CTA_Database::query( $count_sql, $where_vals );
		$total     = (int) ( $count[0]['total_count'] ?? 0 );

		$list_sql  = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$sort_by} {$sort_order} LIMIT %d OFFSET %d";
		$list_vals = array_merge( $where_vals, [ $per_page, $offset ] );
		$events    = CTA_Database::query( $list_sql, $list_vals );

		return [
			'events' => $events ?: [],
			'total'  => $total,
		];
	}

	/**
	 * Get top pages by clicks/impressions
	 *
	 * @param string   $start_date Start date (Y-m-d)
	 * @param string   $end_date   End date (Y-m-d)
	 * @param int|null $cta_id     Optional CTA ID filter
	 * @param string   $sort_by    Sort by 'clicks' or 'impressions'
	 * @param int      $page       Page number (1-based)
	 * @param int      $per_page   Items per page
	 *
	 * @return array ['pages' => array, 'total' => int]
	 */
	public function get_top_pages(
		string $start_date,
		string $end_date,
		?int $cta_id = null,
		string $sort_by = 'clicks',
		int $page = 1,
		int $per_page = 10
	): array {
		if ( ! $this->table_exists() ) {
			return [ 'pages' => [], 'total' => 0 ];
		}

		$table  = self::get_table_name();
		$offset = ( $page - 1 ) * $per_page;

		$where_sql  = 'DATE(occurred_at) >= %s AND DATE(occurred_at) <= %s';
		$where_vals = [ $start_date, $end_date ];

		if ( $cta_id !== null ) {
			$where_sql  .= ' AND cta_id = %d';
			$where_vals[] = $cta_id;
		}

		$total_rows = CTA_Database::query(
			"SELECT COUNT(DISTINCT page_url_hash) as total_count FROM {$table} WHERE {$where_sql}",
			$where_vals
		);
		$total = (int) ( $total_rows[0]['total_count'] ?? 0 );

		$sort_column = $sort_by === 'impressions' ? 'impressions' : 'clicks';

		$page_sql  = "SELECT
					page_url,
					page_title,
					page_url_hash,
					SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks,
					SUM(CASE WHEN event_type = 'impression' THEN 1 ELSE 0 END) as impressions
				FROM {$table}
				WHERE {$where_sql}
				GROUP BY page_url_hash, page_url, page_title
				ORDER BY {$sort_column} DESC
				LIMIT %d OFFSET %d";
		$page_vals = array_merge( $where_vals, [ $per_page, $offset ] );
		$results   = CTA_Database::query( $page_sql, $page_vals );

		return [
			'pages' => $results ?: [],
			'total' => $total,
		];
	}

	/**
	 * Build an analytics snapshot aggregated from the events table.
	 *
	 * Returns structure:
	 * [
	 *   'total_clicks'   => int,
	 *   'last_click'     => string|null (Y-m-d H:i:s),
	 *   'clicks_by_date' => [ 'Y-m-d' => int ],
	 *   'ctas'           => [
	 *     cta_id => [
	 *       'title'       => string,
	 *       'clicks'      => int,
	 *       'impressions' => int,
	 *       'pages'       => [
	 *         page_key => [ 'url' => string, 'title' => string, 'clicks' => int, 'impressions' => int ]
	 *       ],
	 *     ],
	 *   ],
	 * ]
	 *
	 * @param string|null $start_date Optional start date (Y-m-d)
	 * @param string|null $end_date   Optional end date (Y-m-d)
	 *
	 * @return array
	 */
	public function get_snapshot( ?string $start_date = null, ?string $end_date = null ): array {
		if ( ! $this->table_exists() ) {
			return [
				'total_clicks'   => 0,
				'last_click'     => null,
				'clicks_by_date' => [],
				'ctas'           => [],
			];
		}

		$table      = self::get_table_name();
		$where_sql  = '1=1';
		$where_vals = [];
		if ( $start_date ) {
			$where_sql  .= ' AND DATE(occurred_at) >= %s';
			$where_vals[] = $start_date;
		}
		if ( $end_date ) {
			$where_sql  .= ' AND DATE(occurred_at) <= %s';
			$where_vals[] = $end_date;
		}

		$total_rows = CTA_Database::query(
			"SELECT COUNT(*) as total_count FROM {$table} WHERE {$where_sql} AND event_type = 'click'",
			$where_vals
		);
		$total_clicks = (int) ( $total_rows[0]['total_count'] ?? 0 );

		$last_click_rows = CTA_Database::query(
			"SELECT occurred_at FROM {$table} WHERE {$where_sql} AND event_type = 'click' ORDER BY occurred_at DESC LIMIT 1",
			$where_vals
		);
		$last_click = $last_click_rows[0]['occurred_at'] ?? null;

		$clicks_by_date_rows = CTA_Database::query(
			"SELECT DATE(occurred_at) as click_date, COUNT(*) as total
			 FROM {$table}
			 WHERE {$where_sql} AND event_type = 'click'
			 GROUP BY DATE(occurred_at)
			 ORDER BY DATE(occurred_at) ASC",
			$where_vals
		);

		$clicks_by_date = [];
		foreach ( (array) $clicks_by_date_rows as $row ) {
			$clicks_by_date[ $row['click_date'] ] = (int) $row['total'];
		}

		// CTA-level aggregates
		$cta_rows = CTA_Database::query(
			"SELECT cta_id, cta_title, event_type, COUNT(*) as count
			 FROM {$table}
			 WHERE {$where_sql}
			 GROUP BY cta_id, cta_title, event_type",
			$where_vals
		);

		$ctas = [];
		foreach ( (array) $cta_rows as $row ) {
			$id = (int) $row['cta_id'];
			if ( ! isset( $ctas[ $id ] ) ) {
				$ctas[ $id ] = [
					'title'       => $row['cta_title'] ?? '',
					'clicks'      => 0,
					'impressions' => 0,
					'pages'       => [],
				];
			}

			if ( $row['event_type'] === 'click' ) {
				$ctas[ $id ]['clicks'] = (int) $row['count'];
			} elseif ( $row['event_type'] === 'impression' ) {
				$ctas[ $id ]['impressions'] = (int) $row['count'];
			}
		}

		// Page-level aggregates (per CTA)
		$page_rows = CTA_Database::query(
			"SELECT cta_id, page_url, page_title, event_type, COUNT(*) as count
			 FROM {$table}
			 WHERE {$where_sql} AND page_url IS NOT NULL
			 GROUP BY cta_id, page_url, page_title, event_type",
			$where_vals
		);

		foreach ( (array) $page_rows as $row ) {
			$cta_id   = (int) $row['cta_id'];
			$page_key = md5( (string) $row['page_url'] );

			if ( ! isset( $ctas[ $cta_id ] ) ) {
				$ctas[ $cta_id ] = [
					'title'       => $row['cta_title'] ?? '',
					'clicks'      => 0,
					'impressions' => 0,
					'pages'       => [],
				];
			}

			if ( ! isset( $ctas[ $cta_id ]['pages'][ $page_key ] ) ) {
				$ctas[ $cta_id ]['pages'][ $page_key ] = [
					'url'         => $row['page_url'],
					'title'       => $row['page_title'],
					'clicks'      => 0,
					'impressions' => 0,
				];
			}

			if ( $row['event_type'] === 'click' ) {
				$ctas[ $cta_id ]['pages'][ $page_key ]['clicks'] = (int) $row['count'];
			} elseif ( $row['event_type'] === 'impression' ) {
				$ctas[ $cta_id ]['pages'][ $page_key ]['impressions'] = (int) $row['count'];
			}
		}

		return [
			'total_clicks'   => $total_clicks,
			'last_click'     => $last_click ?: null,
			'clicks_by_date' => $clicks_by_date,
			'ctas'           => $ctas,
		];
	}

	/**
	 * Cleanup old events based on retention period
	 *
	 * @param int $days Number of days to retain
	 *
	 * @return int Number of rows deleted
	 */
	public function cleanup_old_events( int $days ): int {
		if ( ! $this->table_exists() || $days < 1 ) {
			return 0;
		}

		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		return CTA_Database::delete_by_date_range(
			self::get_table_name(),
			'occurred_at',
			'0000-00-00 00:00:00',
			$cutoff
		);
	}

	/**
	 * Cleanup old events since a given timestamp based on retention period.
	 *
	 * Deletes events that occurred between $since and the retention cutoff.
	 *
	 * @param int    $days  Number of days to retain
	 * @param string $since Lower bound timestamp (Y-m-d H:i:s)
	 *
	 * @return int Number of rows deleted
	 */
	public function cleanup_old_events_since( int $days, string $since ): int {
		if ( ! $this->table_exists() || $days < 1 ) {
			return 0;
		}

		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		$since  = sanitize_text_field( $since );

		$cutoff_ts = strtotime( $cutoff );
		$since_ts  = strtotime( $since );
		if ( ! $cutoff_ts || ! $since_ts || $cutoff_ts <= $since_ts ) {
			return 0;
		}

		return CTA_Database::delete_by_date_range(
			self::get_table_name(),
			'occurred_at',
			$since,
			$cutoff
		);
	}

	/**
	 * Count events with optional filters
	 *
	 * @param array|null $filters Optional filters (same as get_events)
	 *
	 * @return int Event count
	 */
	public function count_events( ?array $filters = null ): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		$table      = self::get_table_name();
		$where_sql  = '1=1';
		$where_vals = [];

		if ( $filters !== null ) {
			if ( ! empty( $filters['start_date'] ) ) {
				$where_sql  .= ' AND DATE(occurred_at) >= %s';
				$where_vals[] = $filters['start_date'];
			}
			if ( ! empty( $filters['end_date'] ) ) {
				$where_sql  .= ' AND DATE(occurred_at) <= %s';
				$where_vals[] = $filters['end_date'];
			}
			if ( ! empty( $filters['event_type'] ) && $filters['event_type'] !== 'all' ) {
				$where_sql  .= ' AND event_type = %s';
				$where_vals[] = $filters['event_type'];
			}
			if ( ! empty( $filters['cta_id'] ) ) {
				$where_sql  .= ' AND cta_id = %d';
				$where_vals[] = (int) $filters['cta_id'];
			}
		}

		$count = CTA_Database::query(
			"SELECT COUNT(*) as total_count FROM {$table} WHERE {$where_sql}",
			$where_vals
		);

		return (int) ( $count[0]['total_count'] ?? 0 );
	}

	/**
	 * Count distinct pages with CTA events
	 *
	 * @param string $start_date Start date (Y-m-d)
	 * @param string $end_date   End date (Y-m-d)
	 *
	 * @return int Distinct page count
	 */
	public function count_distinct_pages( string $start_date, string $end_date ): int {
		if ( ! $this->table_exists() ) {
			return 0;
		}

		$table = self::get_table_name();
		$sql   = "SELECT COUNT(DISTINCT page_url_hash) as page_count
				FROM {$table}
				WHERE DATE(occurred_at) >= %s
				AND DATE(occurred_at) <= %s
				AND page_url_hash IS NOT NULL";

		$result = CTA_Database::query( $sql, [ $start_date, $end_date ] );

		return (int) ( $result[0]['page_count'] ?? 0 );
	}

	/**
	 * Truncate the events table
	 *
	 * @return bool Success
	 */
	public function truncate(): bool {
		if ( ! $this->table_exists() ) {
			return false;
		}

		return CTA_Database::truncate( self::get_table_name() );
	}

	/**
	 * Delete events by CTA IDs (for demo data cleanup)
	 *
	 * @param array $cta_ids Array of CTA IDs
	 *
	 * @return int Number of rows deleted
	 */
	public function delete_by_cta_ids( array $cta_ids ): int {
		if ( ! $this->table_exists() || empty( $cta_ids ) ) {
			return 0;
		}

		$deleted = 0;
		foreach ( $cta_ids as $cta_id ) {
			$result = CTA_Database::delete(
				self::get_table_name(),
				[ 'cta_id' => (int) $cta_id ],
				[ '%d' ]
			);
			if ( $result !== false ) {
				$deleted += (int) $result;
			}
		}

		return $deleted;
	}

	/**
	 * Bulk insert events (for demo data seeding)
	 *
	 * @param array $events Array of event data arrays
	 *
	 * @return int Number of events inserted
	 */
	public function bulk_insert( array $events ): int {
		if ( ! $this->table_exists() || empty( $events ) ) {
			return 0;
		}

		$table    = self::get_table_name();
		$inserted = 0;

		foreach ( array_chunk( $events, 100 ) as $batch ) {
			$rows = [];
			foreach ( $batch as $event ) {
				$rows[] = [
					'event_type'    => $event['event_type'] ?? 'impression',
					'cta_id'        => (int) ( $event['cta_id'] ?? 0 ),
					'cta_title'     => $event['cta_title'] ?? '',
					'page_url'      => $event['page_url'] ?? '',
					'page_url_hash' => ! empty( $event['page_url'] ) ? self::compute_page_url_hash( $event['page_url'] ) : null,
					'page_title'    => $event['page_title'] ?? '',
					'referrer'      => $event['referrer'] ?? '',
					'ip_address'    => $event['ip_address'] ?? '',
					'user_agent'    => $event['user_agent'] ?? '',
					'device'        => $event['device'] ?? 'desktop',
					'visitor_id'    => $event['visitor_id'] ?? null,
					'session_id'    => $event['session_id'] ?? null,
					'occurred_at'   => $event['occurred_at'] ?? current_time( 'mysql' ),
					'cta_uuid'      => $event['cta_uuid'] ?? null,
				];
			}

			if ( ! empty( $rows ) ) {
				$result = CTA_Database::bulk_insert( $table, $rows );
				if ( $result !== false ) {
					$inserted += (int) $result;
				}
			}
		}

		return $inserted;
	}

	/**
	 * Escape value for LIKE clauses without relying on $wpdb.
	 *
	 * @param string $value Raw search string.
	 * @return string Escaped string safe for LIKE.
	 */
	private static function escape_like( string $value ): string {
		return addcslashes( $value, '\\%_' );
	}
}
