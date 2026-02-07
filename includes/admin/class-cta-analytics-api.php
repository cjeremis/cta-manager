<?php
/**
 * Analytics REST API endpoints
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Analytics_API {

	use CTA_Singleton;

	/**
	 * Register REST API routes
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			'cta-analytics/v1',
			'/daily-stats',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_daily_stats' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);

		register_rest_route(
			'cta-analytics/v1',
			'/type-stats',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_type_stats' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);

		register_rest_route(
			'cta-analytics/v1',
			'/top-ctas',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_top_ctas' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);

		register_rest_route(
			'cta-analytics/v1',
			'/events',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_events' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);

		register_rest_route(
			'cta-analytics/v1',
			'/top-pages',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_top_pages' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);
	}

	/**
	 * Check if user has permission
	 *
	 * @return bool
	 */
	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Clamp a requested date range to the retention window.
	 *
	 * @param string|null $start_date Start date (Y-m-d)
	 * @param string|null $end_date   End date (Y-m-d)
	 *
	 * @return array{0: string|null, 1: string}
	 */
	private function clamp_dates_to_retention( ?string $start_date, ?string $end_date ): array {
		$data = CTA_Data::get_instance();
		return $data->clamp_date_range_to_retention( $start_date, $end_date );
	}

	/**
	 * Get daily statistics for impressions and clicks
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_REST_Response
	 */
	public function get_daily_stats( WP_REST_Request $request ): WP_REST_Response {
		$date_start = $request->get_param( 'start_date' );
		$date_end   = $request->get_param( 'end_date' );
		$cta_id     = $request->get_param( 'cta_id' );

		if ( ! $date_start || ! $date_end ) {
			return new WP_REST_Response( [ 'error' => 'Invalid dates' ], 400 );
		}

		[ $date_start, $date_end ] = $this->clamp_dates_to_retention( $date_start, $date_end );

		$repo    = CTA_Events_Repository::get_instance();
		$results = $repo->get_daily_stats(
			$date_start,
			$date_end,
			$cta_id && $cta_id !== 'all' ? (int) $cta_id : null
		);

		// Build response with CTA colors and titles
		$data   = CTA_Data::get_instance();
		$ctas   = $data->get_ctas();
		$output = [
			'dates' => [],
			'ctas'  => [],
			'data'  => [],
		];

		$cta_map = [];
		foreach ( $ctas as $cta ) {
			$cta_map[ $cta['id'] ] = [
				'name'    => $cta['title'] ?? '',
				'color'   => $cta['color'] ?? '#3b82f6',
				'type'    => $cta['type'] ?? 'phone',
				'is_demo' => ! empty( $cta['_demo'] ),
			];
		}

		// Organize data by date and cta_id
		$by_date = [];
		foreach ( (array) $results as $row ) {
			$date = $row['date'];
			if ( ! isset( $by_date[ $date ] ) ) {
				$by_date[ $date ]  = [];
				$output['dates'][] = $date;
			}
			$by_date[ $date ][] = $row;
		}

		// Build data structure
		$output['data'] = $by_date;
		$output['ctas'] = $cta_map;

		return new WP_REST_Response( $output, 200 );
	}

	/**
	 * Get statistics by CTA type
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_REST_Response
	 */
	public function get_type_stats( WP_REST_Request $request ): WP_REST_Response {
		$date_start = $request->get_param( 'start_date' );
		$date_end   = $request->get_param( 'end_date' );
		$cta_id     = $request->get_param( 'cta_id' );

		if ( ! $date_start || ! $date_end ) {
			return new WP_REST_Response( [ 'error' => 'Invalid dates' ], 400 );
		}

		[ $date_start, $date_end ] = $this->clamp_dates_to_retention( $date_start, $date_end );

		$data = CTA_Data::get_instance();
		$ctas = $data->get_ctas();

		// Map CTA types
		$cta_types = [];
		foreach ( $ctas as $cta ) {
			$cta_types[ $cta['id'] ] = $cta['type'] ?? 'phone';
		}

		$repo    = CTA_Events_Repository::get_instance();
		$results = $repo->get_type_stats(
			$date_start,
			$date_end,
			$cta_id && $cta_id !== 'all' ? (int) $cta_id : null
		);

		// Organize by type
		$by_type = [
			'phone' => [],
			'link'  => [],
			'email' => [],
			'popup' => [],
		];

		foreach ( (array) $results as $row ) {
			$type = $cta_types[ $row['cta_id'] ] ?? 'phone';
			if ( $row['event_type'] === 'impression' ) {
				if ( ! isset( $by_type[ $type ][ $row['date'] ] ) ) {
					$by_type[ $type ][ $row['date'] ] = 0;
				}
				$by_type[ $type ][ $row['date'] ] += $row['count'];
			}
		}

		return new WP_REST_Response(
			[
				'by_type' => $by_type,
				'dates'   => array_unique( array_column( (array) $results, 'date' ) ),
			],
			200
		);
	}

	/**
	 * Get top performing CTAs
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_REST_Response
	 */
	public function get_top_ctas( WP_REST_Request $request ): WP_REST_Response {
		$date_start = $request->get_param( 'start_date' );
		$date_end   = $request->get_param( 'end_date' );

		if ( ! $date_start || ! $date_end ) {
			return new WP_REST_Response( [ 'error' => 'Invalid dates' ], 400 );
		}

		[ $date_start, $date_end ] = $this->clamp_dates_to_retention( $date_start, $date_end );

		$repo    = CTA_Events_Repository::get_instance();
		$results = $repo->get_top_ctas( $date_start, $date_end );

		// Aggregate by CTA
		$data      = CTA_Data::get_instance();
		$ctas      = $data->get_ctas();
		$cta_stats = [];

		foreach ( $ctas as $cta ) {
			$cta_stats[ $cta['id'] ] = [
				'id'          => $cta['id'],
				'name'        => $cta['title'] ?? '',
				'color'       => $cta['color'] ?? '#3b82f6',
				'type'        => $cta['type'] ?? 'phone',
				'is_demo'     => ! empty( $cta['_demo'] ),
				'impressions' => 0,
				'clicks'      => 0,
			];
		}

		foreach ( (array) $results as $row ) {
			if ( isset( $cta_stats[ $row['cta_id'] ] ) ) {
				if ( $row['event_type'] === 'impression' ) {
					$cta_stats[ $row['cta_id'] ]['impressions'] = (int) $row['count'];
				} else {
					$cta_stats[ $row['cta_id'] ]['clicks'] = (int) $row['count'];
				}
			}
		}

		// Sort by clicks descending, then by impressions
		usort(
			$cta_stats,
			function ( $a, $b ) {
				if ( $b['clicks'] === $a['clicks'] ) {
					return $b['impressions'] <=> $a['impressions'];
				}
				return $b['clicks'] <=> $a['clicks'];
			}
		);

		return new WP_REST_Response( $cta_stats, 200 );
	}

	/**
	 * Get events with pagination
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_REST_Response
	 */
	public function get_events( WP_REST_Request $request ): WP_REST_Response {
		$date_start = $request->get_param( 'start_date' );
		$date_end   = $request->get_param( 'end_date' );
		$event_type = $request->get_param( 'event_type' );
		$cta_id     = $request->get_param( 'cta_id' );
		$search     = $request->get_param( 'search' );
		$sort_by    = $request->get_param( 'sort_by' ) ?: 'occurred_at';
		$sort_order = $request->get_param( 'sort_order' ) ?: 'desc';
		$page       = absint( $request->get_param( 'page' ) ) ?: 1;
		$per_page   = absint( $request->get_param( 'per_page' ) ) ?: 50;

		// Map legacy sort column name
		if ( $sort_by === 'created_at' ) {
			$sort_by = 'occurred_at';
		}

		[ $date_start, $date_end ] = $this->clamp_dates_to_retention( $date_start, $date_end );

		$filters = [
			'start_date' => $date_start,
			'end_date'   => $date_end,
			'event_type' => $event_type,
			'cta_id'     => $cta_id && $cta_id !== 'all' ? (int) $cta_id : null,
			'search'     => $search,
			'sort_by'    => $sort_by,
			'sort_order' => $sort_order,
		];

		$repo   = CTA_Events_Repository::get_instance();
		$result = $repo->get_events( $filters, $page, $per_page );

		$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

		// Filter columns based on Pro status
		$filtered_events = [];
		foreach ( (array) $result['events'] as $event ) {
			$item = [
				'id'         => $event['id'],
				'created_at' => $event['occurred_at'], // Map back to legacy name for API compatibility
				'event_type' => $event['event_type'],
				'cta_id'     => $event['cta_id'],
				'cta_title'  => $event['cta_title'],
				'page_url'   => $event['page_url'],
				'page_title' => $event['page_title'],
				'referrer'   => $event['referrer'],
				'device'     => $event['device'],
			];

			if ( $is_pro ) {
				$item['ip_address'] = $event['ip_address'];
				$item['user_agent'] = $event['user_agent'];
			}

			$filtered_events[] = $item;
		}

		return new WP_REST_Response(
			[
				'events'      => $filtered_events,
				'total'       => $result['total'],
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $result['total'] / $per_page ),
			],
			200
		);
	}

	/**
	 * Get top pages by clicks or impressions
	 *
	 * @param WP_REST_Request $request Request object
	 *
	 * @return WP_REST_Response
	 */
	public function get_top_pages( WP_REST_Request $request ): WP_REST_Response {
		$date_start = $request->get_param( 'start_date' );
		$date_end   = $request->get_param( 'end_date' );
		$cta_id     = $request->get_param( 'cta_id' );
		$sort_by    = $request->get_param( 'sort_by' ) ?: 'clicks';
		$page       = absint( $request->get_param( 'page' ) ) ?: 1;
		$per_page   = absint( $request->get_param( 'per_page' ) ) ?: 10;

		if ( ! $date_start || ! $date_end ) {
			return new WP_REST_Response( [ 'error' => 'Invalid dates' ], 400 );
		}

		[ $date_start, $date_end ] = $this->clamp_dates_to_retention( $date_start, $date_end );

		$repo   = CTA_Events_Repository::get_instance();
		$result = $repo->get_top_pages(
			$date_start,
			$date_end,
			$cta_id && $cta_id !== 'all' ? (int) $cta_id : null,
			$sort_by,
			$page,
			$per_page
		);

		// Calculate CTR for each page
		$pages = [];
		foreach ( (array) $result['pages'] as $row ) {
			$impressions = (int) $row['impressions'];
			$clicks      = (int) $row['clicks'];
			$ctr         = $impressions > 0 ? round( ( $clicks / $impressions ) * 100, 2 ) : 0;

			$pages[] = [
				'page_url'    => $row['page_url'],
				'page_title'  => $row['page_title'] ?: $row['page_url'],
				'clicks'      => $clicks,
				'impressions' => $impressions,
				'ctr'         => $ctr,
			];
		}

		return new WP_REST_Response(
			[
				'pages'       => $pages,
				'total'       => $result['total'],
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $result['total'] / $per_page ),
			],
			200
		);
	}
}
