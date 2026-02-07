<?php
/**
 * Analytics Page template
 *
 * Fresh layout for CTA analytics using shared partials.
 *
 * @var array  $cta_list CTA list with id, name, type, color
 * @var array  $settings Plugin settings
 * @var bool   $is_pro   Whether Pro features are enabled
 * @var string $nonce    Nonce for AJAX requests
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Page wrapper configuration
$current_page       = 'analytics';
$header_title       = __( 'CTA Manager Analytics', 'cta-manager' );
$header_description = __( 'View essential performance metrics for your CTAs so you can understand what\'s driving engagement and prioritize improvements.', 'cta-manager' );
$topbar_actions     = [];

include __DIR__ . '/partials/page-wrapper-start.php';
?>

	<!-- Analytics Toolbar: Date Range + Filters -->
	<div class="cta-analytics-toolbar">
		<div class="cta-analytics-toolbar-left">
			<div class="cta-filter-info">
				<span class="cta-filter-label"><?php esc_html_e( 'Date Range:', 'cta-manager' ); ?></span>
				<span class="cta-filter-value" id="cta-filter-date-display">—</span>
			</div>
			<div class="cta-filter-badge-group">
				<span class="cta-filter-count-badge" id="cta-filter-count" style="display: none;">
					<span id="cta-active-filter-count">0</span> <?php esc_html_e( 'filters', 'cta-manager' ); ?>
				</span>
			</div>
		</div>
		<div class="cta-analytics-toolbar-right">
			<button type="button" id="cta-open-filters-modal" class="cta-button cta-button-primary">
				<span class="dashicons dashicons-filter"></span>
				<?php esc_html_e( 'Filters', 'cta-manager' ); ?>
			</button>
		</div>
	</div>

	<div class="cta-section">
		<?php
		$title        = __( 'Performance Snapshot', 'cta-manager' );
		$actions_html = '';
		include __DIR__ . '/partials/section-header-with-actions.php';
		unset( $title, $actions_html );
		?>

	<?php if ( $has_analytics_data ) : ?>
		<div class="cta-analytics-kpi">
			<?php
			$total_impressions = 0;
			$total_clicks      = 0;
			foreach ( (array) $cta_stats as $cta_stat ) {
				$total_impressions += (int) ( $cta_stat['impressions'] ?? 0 );
				$total_clicks      += (int) ( $cta_stat['clicks'] ?? 0 );
			}
			if ( isset( $analytics['total_clicks'] ) ) {
				$total_clicks = (int) $analytics['total_clicks'];
			}
			$ctr_value = $total_impressions > 0 ? round( ( $total_clicks / $total_impressions ) * 100, 2 ) : 0;
			?>
			<?php
			$label    = __( 'Total Impressions', 'cta-manager' );
			$value    = number_format_i18n( $total_impressions );
			$value_id = 'cta-kpi-impressions';
			include __DIR__ . '/partials/kpi-card.php';
			unset( $label, $value, $value_id );
			?>

			<?php
			$label    = __( 'Total Clicks', 'cta-manager' );
			$value    = number_format_i18n( $total_clicks );
			$value_id = 'cta-kpi-clicks';
			include __DIR__ . '/partials/kpi-card.php';
			unset( $label, $value, $value_id );
			?>

			<?php
			$label    = __( 'Click-Through Rate', 'cta-manager' );
			$value    = number_format_i18n( $ctr_value, 2 ) . '%';
			$value_id = 'cta-kpi-ctr';
			include __DIR__ . '/partials/kpi-card.php';
			unset( $label, $value, $value_id );
			?>
		</div>
	<?php else : ?>
			<?php
			$icon        = 'chart-area';
			$title       = __( 'No Performance Data Yet', 'cta-manager' );
			$description = __( 'Performance metrics will appear here once your CTAs receive traffic.', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/empty-state.php';
			unset( $icon, $title, $description );
			?>
		<?php endif; ?>
	</div>

	<!-- CTA Performance Table -->
	<div class="cta-section">
		<?php
		$title        = __( 'CTA Performance', 'cta-manager' );
		$actions_html = '';
		include __DIR__ . '/partials/section-header-with-actions.php';
		unset( $title, $actions_html );

		// Build CTA titles lookup from cta_list
		$cta_titles = [];
		foreach ( $cta_list as $cta ) {
			$cta_id = isset( $cta['id'] ) ? (string) $cta['id'] : null;
			if ( $cta_id ) {
				$cta_titles[ $cta_id ] = $cta['name'] ?? ( $cta['title'] ?? '' );
			}
		}

		// Get analytics data if available
		$cta_stats = $analytics['ctas'] ?? [];
		$rows = [];
		foreach ( $cta_stats as $cta_id => $cta_stat ) {
			$rows[] = array_merge(
				[ 'id' => (string) $cta_id ],
				$cta_stat
			);
		}
		usort(
			$rows,
			function ( $a, $b ) {
				return ( $b['clicks'] ?? 0 ) <=> ( $a['clicks'] ?? 0 );
			}
		);
		?>

		<?php if ( empty( $rows ) ) : ?>
			<?php
			$icon        = 'chart-bar';
			$title       = __( 'No Performance Data Yet', 'cta-manager' );
			$description = __( 'CTA performance metrics will appear here once your CTAs receive traffic.', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/empty-state.php';
			unset( $icon, $title, $description );
			?>
        <?php else : ?>
            <?php
            // Build simplified rows for client-side rendering
            $formatted_rows = [];
            foreach ( $rows as $cta_stat ) :
                $cta_id      = $cta_stat['id'] ?? '';
                $impressions = intval( $cta_stat['impressions'] ?? 0 );
                $clicks      = intval( $cta_stat['clicks'] ?? 0 );
                $ctr         = $impressions > 0 ? round( ( $clicks / $impressions ) * 100, 2 ) : 0;
                $pages       = $cta_stat['pages'] ?? [];
                $top_page    = null;
                if ( ! empty( $pages ) ) {
                    usort(
                        $pages,
                        function ( $a, $b ) {
                            return ( $b['clicks'] ?? 0 ) <=> ( $a['clicks'] ?? 0 );
                        }
                    );
                    $top_page = $pages[0];
                }
                $cta_title = $cta_titles[ (string) $cta_id ] ?? '';
                if ( '' === $cta_title && ! empty( $cta_stat['title'] ) ) {
                    $cta_title = $cta_stat['title'];
                }
                if ( '' === $cta_title && ! empty( $cta_stat['name'] ) ) {
                    $cta_title = $cta_stat['name'];
                }
                if ( '' === $cta_title ) {
                    $cta_title = sprintf( __( 'CTA #%d', 'cta-manager' ), $cta_id );
                }
                $top_page_text = $top_page ? ( $top_page['title'] ?: $top_page['url'] ) : __( '—', 'cta-manager' );

                $formatted_rows[] = [
                    'id'         => (string) $cta_id,
                    'cta_title'  => $cta_title,
                    'impressions'=> $impressions,
                    'clicks'     => $clicks,
                    'ctr'        => $ctr,
                    'top_page'   => $top_page_text,
                ];
            endforeach;
            $rows_json = wp_json_encode( $formatted_rows );
            ?>

            <div
                class="cta-performance-list"
                id="cta-performance-list"
                data-performance-rows="<?php echo esc_attr( $rows_json ); ?>"
                data-performance-label-impressions="<?php echo esc_attr__( 'Impressions', 'cta-manager' ); ?>"
                data-performance-label-clicks="<?php echo esc_attr__( 'Clicks', 'cta-manager' ); ?>"
                data-performance-label-ctr="<?php echo esc_attr__( 'CTR', 'cta-manager' ); ?>"
                data-performance-label-top-page="<?php echo esc_attr__( 'Top Page:', 'cta-manager' ); ?>"
            >
                <div class="cta-performance-list-header">
                    <span class="cta-performance-list-header__label"><?php esc_html_e( 'Sort by', 'cta-manager' ); ?></span>
                    <div class="cta-performance-list-header__controls">
                        <?php
                        $sort_keys = [
                            'cta'         => __( 'CTA', 'cta-manager' ),
                            'impressions' => __( 'Impressions', 'cta-manager' ),
                            'clicks'      => __( 'Clicks', 'cta-manager' ),
                            'ctr'         => __( 'CTR', 'cta-manager' ),
                        ];
                        foreach ( $sort_keys as $key => $label ) :
                            $default_dir = in_array( $key, [ 'impressions', 'clicks', 'ctr' ], true ) ? 'desc' : 'asc';
                            $is_active   = 'clicks' === $key;
                            ?>
                            <button
                                type="button"
                                class="cta-performance-sort-btn<?php echo $is_active ? ' is-active' : ''; ?>"
                                data-sort-key="<?php echo esc_attr( $key ); ?>"
                                data-default-dir="<?php echo esc_attr( $default_dir ); ?>"
                                aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
                            >
                                <span><?php echo esc_html( $label ); ?></span>
                                <span class="cta-performance-sort-icon"></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="cta-performance-list-body" id="cta-performance-list-body">
                    <div class="cta-performance-placeholder">
                        <em><?php esc_html_e( 'Rendering performance data…', 'cta-manager' ); ?></em>
                    </div>
                </div>
                <div class="cta-performance-footer">
                    <div class="cta-pagination" id="cta-performance-pagination"></div>
                    <div class="cta-table-limit">
                        <label for="cta-performance-per-page"><?php esc_html_e( 'Rows per page', 'cta-manager' ); ?></label>
                        <select id="cta-performance-per-page">
                            <option value="10"><?php esc_html_e( '10', 'cta-manager' ); ?></option>
                            <option value="25" selected><?php esc_html_e( '25', 'cta-manager' ); ?></option>
                            <option value="50"><?php esc_html_e( '50', 'cta-manager' ); ?></option>
                            <option value="100"><?php esc_html_e( '100', 'cta-manager' ); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

	<div class="cta-section cta-section--flat">
		<?php
		$charts = [
			[
				'title'             => __( 'Daily Impressions', 'cta-manager' ),
				'canvas_id'         => 'cta-chart-impressions',
				'legend_id'         => 'cta-legend-impressions',
				'empty_icon'        => 'visibility',
				'empty_title'       => __( 'No Impression Data', 'cta-manager' ),
				'empty_description' => __( 'Daily impressions will appear here once your CTAs receive traffic.', 'cta-manager' ),
			],
			[
				'title'             => __( 'Daily Clicks', 'cta-manager' ),
				'canvas_id'         => 'cta-chart-clicks',
				'legend_id'         => 'cta-legend-clicks',
				'empty_icon'        => 'chart-bar',
				'empty_title'       => __( 'No Click Data', 'cta-manager' ),
				'empty_description' => __( 'Daily clicks will appear here once your CTAs receive traffic.', 'cta-manager' ),
			],
			[
				'title'             => __( 'CTR Trend', 'cta-manager' ),
				'canvas_id'         => 'cta-chart-ctr-trend',
				'legend_id'         => 'cta-legend-ctr-trend',
				'full_width'        => true,
				'empty_icon'        => 'chart-line',
				'empty_title'       => __( 'No CTR Data', 'cta-manager' ),
				'empty_description' => __( 'Click-through rate trends will appear here once you have traffic.', 'cta-manager' ),
			],
			[
				'title'             => __( 'Impressions by CTA Type', 'cta-manager' ),
				'canvas_id'         => 'cta-chart-impressions-type',
				'legend_id'         => 'cta-legend-impressions-type',
				'empty_icon'        => 'chart-pie',
				'empty_title'       => __( 'No Type Data', 'cta-manager' ),
				'empty_description' => __( 'CTA type breakdown will appear here once you have traffic.', 'cta-manager' ),
			],
			[
				'title'             => __( 'Clicks by CTA Type', 'cta-manager' ),
				'canvas_id'         => 'cta-chart-clicks-type',
				'legend_id'         => 'cta-legend-clicks-type',
				'empty_icon'        => 'chart-pie',
				'empty_title'       => __( 'No Type Data', 'cta-manager' ),
				'empty_description' => __( 'CTA type breakdown will appear here once you have traffic.', 'cta-manager' ),
			],
			[
				'title'             => __( 'Top Performing CTAs', 'cta-manager' ),
				'canvas_id'         => 'cta-chart-top-ctas',
				'legend_id'         => 'cta-legend-top-ctas',
				'full_width'        => true,
				'empty_icon'        => 'awards',
				'empty_title'       => __( 'No Top CTAs Yet', 'cta-manager' ),
				'empty_description' => __( 'Top performing CTAs will appear here once you have traffic.', 'cta-manager' ),
			],
			[
				'title'             => __( 'Impression Share', 'cta-manager' ),
				'canvas_id'         => 'cta-chart-impressions-share',
				'legend_id'         => 'cta-legend-impressions-share',
				'is_donut'          => true,
				'card_id'           => 'cta-card-impressions-share',
				'canvas_height'     => '180',
				'empty_icon'        => 'visibility',
				'empty_title'       => __( 'No Impressions', 'cta-manager' ),
				'empty_description' => __( 'Impression share data will appear here.', 'cta-manager' ),
			],
			[
				'title'             => __( 'Click Share', 'cta-manager' ),
				'canvas_id'         => 'cta-chart-clicks-share',
				'legend_id'         => 'cta-legend-clicks-share',
				'is_donut'          => true,
				'card_id'           => 'cta-card-clicks-share',
				'canvas_height'     => '180',
				'empty_icon'        => 'chart-bar',
				'empty_title'       => __( 'No Clicks', 'cta-manager' ),
				'empty_description' => __( 'Click share data will appear here.', 'cta-manager' ),
			],
		];
		?>
		<div class="cta-charts-container" style="margin-top: var(--cta-spacing-md);">
			<?php foreach ( $charts as $chart ) : ?>
				<?php
				$title             = $chart['title'];
				$canvas_id         = $chart['canvas_id'];
				$legend_id         = $chart['legend_id'];
				$full_width        = ! empty( $chart['full_width'] );
				$is_donut          = ! empty( $chart['is_donut'] );
				$card_id           = $chart['card_id'] ?? '';
				$canvas_height     = $chart['canvas_height'] ?? '120';
				$show_empty_state  = ! $has_analytics_data;
				$empty_icon        = $chart['empty_icon'] ?? 'chart-bar';
				$empty_title       = $chart['empty_title'] ?? __( 'No Data Available', 'cta-manager' );
				$empty_description = $chart['empty_description'] ?? __( 'Data will appear here once analytics are collected.', 'cta-manager' );
				include __DIR__ . '/partials/chart-card.php';
				unset( $title, $canvas_id, $legend_id, $full_width, $is_donut, $card_id, $canvas_height, $show_empty_state, $empty_icon, $empty_title, $empty_description );
				?>
			<?php endforeach; ?>
			<?php unset( $charts ); ?>
		</div>
	</div>

	<div class="cta-section">
		<?php
		$title        = __( 'Top Performing Pages', 'cta-manager' );
		$actions_html = '';
		include __DIR__ . '/partials/section-header-with-actions.php';
		unset( $title, $actions_html );
		?>

		<div id="cta-top-pages-section" style="margin-top: var(--cta-spacing-md);">
			<!-- Section-level empty state (shown when both panels have no data) -->
			<div id="cta-top-pages-empty-state" style="<?php echo $has_analytics_data ? 'display: none;' : ''; ?>">
				<?php
				$icon        = 'admin-page';
				$title       = __( 'No Page Data Yet', 'cta-manager' );
				$description = __( 'Page performance data will appear here once your CTAs receive traffic on different pages.', 'cta-manager' );
				include CTA_PLUGIN_DIR . 'templates/admin/partials/empty-state.php';
				unset( $icon, $title, $description );
				?>
			</div>

			<div id="cta-top-pages-tables" class="cta-top-pages-panels" style="<?php echo $has_analytics_data ? '' : 'display: none;'; ?>">
				<div class="cta-top-pages-panel">
					<div class="cta-top-pages-panel__header">
						<h3><?php esc_html_e( 'Top Pages by Clicks', 'cta-manager' ); ?></h3>
						<p class="cta-top-pages-panel__description">
							<?php esc_html_e( 'Pages are ranked by the number of clicks generated by your CTAs.', 'cta-manager' ); ?>
						</p>
					</div>
					<div
						class="cta-top-pages-list"
						id="cta-top-pages-clicks-list"
						data-label-clicks="<?php echo esc_attr__( 'Clicks', 'cta-manager' ); ?>"
						data-label-impressions="<?php echo esc_attr__( 'Impressions', 'cta-manager' ); ?>"
						data-label-ctr="<?php echo esc_attr__( 'CTR', 'cta-manager' ); ?>"
					>
						<div class="cta-events-placeholder">
							<em><?php esc_html_e( 'Loading pages…', 'cta-manager' ); ?></em>
						</div>
					</div>
					<div class="cta-pagination" id="cta-pagination-pages-clicks"></div>
				</div>
				<div class="cta-top-pages-panel">
					<div class="cta-top-pages-panel__header">
						<h3><?php esc_html_e( 'Top Pages by Impressions', 'cta-manager' ); ?></h3>
						<p class="cta-top-pages-panel__description">
							<?php esc_html_e( 'Pages with the most CTA impressions appear first.', 'cta-manager' ); ?>
						</p>
					</div>
					<div
						class="cta-top-pages-list"
						id="cta-top-pages-impressions-list"
						data-label-clicks="<?php echo esc_attr__( 'Clicks', 'cta-manager' ); ?>"
						data-label-impressions="<?php echo esc_attr__( 'Impressions', 'cta-manager' ); ?>"
						data-label-ctr="<?php echo esc_attr__( 'CTR', 'cta-manager' ); ?>"
					>
						<div class="cta-events-placeholder">
							<em><?php esc_html_e( 'Loading pages…', 'cta-manager' ); ?></em>
						</div>
					</div>
					<div class="cta-pagination" id="cta-pagination-pages-impressions"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="cta-section cta-event-log-section">
		<?php
		$title_html = __( 'Event Log', 'cta-manager' );
		$title_raw  = esc_html( $title_html );
		if ( ! $is_pro ) {
			ob_start();
			cta_pro_badge_inline();
			$title_raw .= '<span style="margin-left: 8px;">' . ob_get_clean() . '</span>';
		}

		$actions_html = '';
		if ( $is_pro ) {
			ob_start();
			$export_types = [
				[
					'id'    => 'cta-export-csv',
					'text'  => __( 'Export CSV', 'cta-manager' ),
					'icon'  => 'download',
					'class' => 'cta-button-secondary',
				],
				[
					'id'    => 'cta-export-json',
					'text'  => __( 'Export JSON', 'cta-manager' ),
					'icon'  => 'download',
					'class' => 'cta-button-secondary',
				],
			];
			include __DIR__ . '/partials/export-actions.php';
			unset( $export_types );
			$actions_html = ob_get_clean();
		}

		$title = $title_html;
		include __DIR__ . '/partials/section-header-with-actions.php';
		unset( $title, $title_html, $title_raw );
		?>

		<?php
		$icon        = 'list-view';
		$title       = __( 'Unlock Advanced Event Logging', 'cta-manager' );
		$description = __( 'Upgrade to Pro to access detailed event logs, export data, and track user agent and IP information for comprehensive analytics.', 'cta-manager' );
		include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
		unset( $icon, $title, $description );
		?>

		<?php if ( $is_pro ) : ?>
		<div id="cta-events-empty-state" style="<?php echo $has_analytics_data ? 'display: none;' : ''; ?>">
			<?php
			$icon        = 'list-view';
			$title       = __( 'No Events Found', 'cta-manager' );
			$description = __( 'Events will appear here as visitors view and interact with your CTAs.', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/empty-state.php';
			unset( $icon, $title, $description );
			?>
		</div>
		<?php endif; ?>

		<?php if ( $is_pro ) : ?>
		<div class="cta-event-log-card cta-table-section" id="cta-events-card" style="margin-top: var(--cta-spacing-md); <?php echo $has_analytics_data ? '' : 'display: none;'; ?>">
			<div class="cta-table-filters" id="cta-events-filters">
				<input
					type="text"
					id="cta-table-search"
					class="cta-input"
					placeholder="<?php esc_attr_e( 'Search events...', 'cta-manager' ); ?>"
					style="flex: 1; min-width: 200px;"
				>
				<select id="cta-table-event-type" class="cta-select" style="min-width: 150px;">
					<option value="all"><?php esc_html_e( 'All Events', 'cta-manager' ); ?></option>
					<option value="impression"><?php esc_html_e( 'Impressions', 'cta-manager' ); ?></option>
					<option value="click"><?php esc_html_e( 'Clicks', 'cta-manager' ); ?></option>
				</select>
			</div>

			<div class="cta-events-list" id="cta-events-list">
				<div class="cta-events-list-inner" id="cta-events-list-inner">
					<?php if ( $has_analytics_data ) : ?>
					<div class="cta-events-placeholder">
						<em><?php esc_html_e( 'Loading events…', 'cta-manager' ); ?></em>
					</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="cta-pagination" id="cta-pagination"></div>
		</div>
		<?php endif; ?>
	</div>

	<!-- Filters Modal -->
	<?php
	ob_start();
	?>
	<div class="cta-analytics-filters-modal-content">
		<?php
		// Set default date range to retention window (fallback to last 7 days)
		$to_date      = gmdate( 'Y-m-d' );
		if ( ! empty( $retention_start_date ) ) {
			$from_date = $retention_start_date;
		} elseif ( ! empty( $retention_days ) ) {
			$from_date = gmdate( 'Y-m-d', strtotime( '-' . absint( $retention_days ) . ' days' ) );
		} else {
			$from_date = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		}
		$selected_cta = '';
		$min_date     = $retention_start_date ?? '';
		$max_date     = $to_date;
		$all_ctas     = array_map(
			function ( $cta ) {
				return (object) [
					'id'    => $cta['id'],
					'title' => $cta['name'],
				];
			},
			$cta_list
		);
		$event_types  = [ 'impression', 'click' ];

		include __DIR__ . '/partials/analytics-filters.php';
		unset( $from_date, $to_date, $min_date, $max_date, $selected_cta, $all_ctas, $event_types );
		?>
	</div>
	<?php
	$analytics_filters_body_html = ob_get_clean();

	ob_start();
	?>
	<div class="cta-modal-footer-buttons">
		<button type="button" id="cta-reset-filters" class="cta-button-secondary">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Reset', 'cta-manager' ); ?>
		</button>
		<button type="button" id="cta-apply-filters" class="cta-button-primary">
			<span class="dashicons dashicons-search"></span>
			<?php esc_html_e( 'Apply Filters', 'cta-manager' ); ?>
		</button>
	</div>
	<?php
	$analytics_filters_footer_html = ob_get_clean();

	$modal = [
		'id'          => 'cta-analytics-filters-modal',
		'title_html'  => '<span class="dashicons dashicons-filter"></span>' . esc_html__( 'Filters', 'cta-manager' ),
		'body_html'   => $analytics_filters_body_html,
		'footer_html' => $analytics_filters_footer_html,
		'size_class'  => 'cta-modal-md',
	];
	include __DIR__ . '/partials/modal.php';
	unset( $modal, $analytics_filters_body_html, $analytics_filters_footer_html );
	?>

	<?php
	$tooltip_id = 'cta-tooltip';
	include __DIR__ . '/partials/tooltip.php';
	unset( $tooltip_id );
	?>

<?php include __DIR__ . '/partials/page-wrapper-end.php'; ?>

<script>
	window.ctaAnalyticsData = {
		nonce: '<?php echo esc_js( $nonce ); ?>',
		ajaxUrl: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
		ctas: <?php echo wp_json_encode( $cta_list ); ?>,
		isPro: <?php echo $is_pro ? 'true' : 'false'; ?>,
		hasAnalyticsData: <?php echo $has_analytics_data ? 'true' : 'false'; ?>,
		retentionDays: <?php echo (int) ( $retention_days ?? 0 ); ?>,
		retentionStartDate: <?php echo ! empty( $retention_start_date ) ? "'" . esc_js( $retention_start_date ) . "'" : 'null'; ?>,
	};
</script>
