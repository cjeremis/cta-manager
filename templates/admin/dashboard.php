<?php
/**
 * Admin Page Template - Dashboard
 *
 * Handles markup rendering for the dashboard admin page template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_ctas = ! empty( $ctas );
$is_pro_active = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
$window_7_days  = isset( $stats['window_days_7'] ) ? (int) $stats['window_days_7'] : 7;
$window_14_days = isset( $stats['window_days_14'] ) ? (int) $stats['window_days_14'] : 14;
$window_30_days = isset( $stats['window_days_30'] ) ? (int) $stats['window_days_30'] : 30;
$window_7_label = sprintf( _n( 'Past %d day', 'Past %d days', $window_7_days, 'cta-manager' ), $window_7_days );
$window_7_short_label = sprintf( _n( '%d Day', '%d Days', $window_7_days, 'cta-manager' ), $window_7_days );
$window_14_label = sprintf( _n( '%d Day', '%d Days', $window_14_days, 'cta-manager' ), $window_14_days );
$window_30_label = sprintf( _n( '%d Day', '%d Days', $window_30_days, 'cta-manager' ), $window_30_days );

// Page wrapper configuration
$current_page       = '';
$header_title       = __( 'CTA Manager Dashboard', 'cta-manager' );
$header_description = __( 'Create and manage essential CTAs in one place. Add CTAs to your site with controls and insights to start improving conversions.', 'cta-manager' );
$topbar_actions     = [];

include __DIR__ . '/partials/page-wrapper-start.php';
// After wrapper-start.php, we're in HTML mode
if ( ! $has_ctas ) : ?>
	<?php
	$icon         = 'megaphone';
	$title        = __( 'No CTAs Created Yet', 'cta-manager' );
	$description  = __( 'Get started by creating your first call-to-action button.', 'cta-manager' );
	$action_url   = '#';
	$action_text  = __( 'Create CTA', 'cta-manager' );
	$action_class = 'cta-global-modal-trigger';
	$action_icon  = 'plus-alt';
	$action_attrs = 'data-open-modal="#cta-global-form-modal"';
	include __DIR__ . '/partials/empty-state.php';
	unset( $icon, $title, $description, $action_url, $action_text, $action_class, $action_icon, $action_attrs );
	?>
<?php else : ?>
	<!-- Stats Overview -->
	<div class="cta-dashboard">
		<div class="cta-dashboard-section">
			<div class="cta-dashboard-section-header">
				<h3><?php esc_html_e( 'CTA Overview', 'cta-manager' ); ?></h3>
			</div>
			<div class="cta-dashboard-card-grid">
				<div class="cta-stat-card cta-stat-card--green">
					<span class="dashicons dashicons-megaphone cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Your CTAs', 'cta-manager' ); ?></h4>
						<div class="cta-stat-breakdown">
							<?php
							$active_count = $stats['active_ctas'] ?? 0;
							$draft_count = $stats['draft_ctas'] ?? 0;
							$scheduled_count = $stats['scheduled_ctas'] ?? 0;
							$active_url = $active_count > 0 ? add_query_arg( 'status', 'published', CTA_Admin_Menu::get_admin_url( 'cta' ) ) : '';
							$draft_url = $draft_count > 0 ? add_query_arg( 'status', 'draft', CTA_Admin_Menu::get_admin_url( 'cta' ) ) : '';
							$scheduled_url = $scheduled_count > 0 ? add_query_arg( 'status', 'scheduled', CTA_Admin_Menu::get_admin_url( 'cta' ) ) : '';
							?>
							<span class="cta-stat-breakdown-item">
								<?php if ( $active_url ) : ?>
									<a href="<?php echo esc_url( $active_url ); ?>" class="cta-stat-breakdown-value" title="<?php esc_attr_e( 'View active CTAs', 'cta-manager' ); ?>">
										<?php echo esc_html( number_format_i18n( $active_count ) ); ?>
									</a>
								<?php else : ?>
									<span class="cta-stat-breakdown-value"><?php echo esc_html( number_format_i18n( $active_count ) ); ?></span>
								<?php endif; ?>
								<span class="cta-stat-breakdown-label"><?php esc_html_e( 'Active', 'cta-manager' ); ?></span>
							</span>
							<span class="cta-stat-breakdown-item">
								<?php if ( $draft_url ) : ?>
									<a href="<?php echo esc_url( $draft_url ); ?>" class="cta-stat-breakdown-value" title="<?php esc_attr_e( 'View draft CTAs', 'cta-manager' ); ?>">
										<?php echo esc_html( number_format_i18n( $draft_count ) ); ?>
									</a>
								<?php else : ?>
									<span class="cta-stat-breakdown-value"><?php echo esc_html( number_format_i18n( $draft_count ) ); ?></span>
								<?php endif; ?>
								<span class="cta-stat-breakdown-label"><?php esc_html_e( 'Draft', 'cta-manager' ); ?></span>
							</span>
							<span class="cta-stat-breakdown-item">
								<?php if ( $scheduled_url ) : ?>
									<a href="<?php echo esc_url( $scheduled_url ); ?>" class="cta-stat-breakdown-value" title="<?php esc_attr_e( 'View scheduled CTAs', 'cta-manager' ); ?>">
										<?php echo esc_html( number_format_i18n( $scheduled_count ) ); ?>
									</a>
								<?php else : ?>
									<span class="cta-stat-breakdown-value"><?php echo esc_html( number_format_i18n( $scheduled_count ) ); ?></span>
								<?php endif; ?>
								<span class="cta-stat-breakdown-label"><?php esc_html_e( 'Scheduled', 'cta-manager' ); ?></span>
							</span>
						</div>
						<span class="cta-stat-meta"><?php echo esc_html( sprintf( __( '%d total CTAs', 'cta-manager' ), $stats['total_ctas'] ?? 0 ) ); ?></span>
					</div>
				</div>

				<div class="cta-stat-card cta-stat-card--blue">
					<span class="dashicons dashicons-visibility cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Total Impressions', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_impressions'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php echo esc_html( $window_7_label ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'impressions', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>

				<div class="cta-stat-card cta-stat-card--purple">
					<span class="dashicons dashicons-admin-links cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Total Clicks', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_clicks'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php echo esc_html( $window_7_label ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'clicks', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>

				<div class="cta-stat-card cta-stat-card--orange">
					<span class="dashicons dashicons-chart-line cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Click-Through Rate', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( ( $stats['avg_ctr'] ?? 0 ) . '%' ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'Average CTR', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'ctr', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="cta-dashboard-section">
			<div class="cta-dashboard-section-header">
				<h3><?php esc_html_e( 'Performance Highlights', 'cta-manager' ); ?></h3>
			</div>
			<div class="cta-dashboard-card-grid">
				<?php
				$top_page_title = $stats['top_page_title'] ?? '--';
				$top_page_slug  = $stats['top_page_slug'] ?? '--';
				$top_page_url   = $stats['top_page_url'] ?? '';
				$top_page_id    = $top_page_url ? url_to_postid( $top_page_url ) : 0;
				?>
				<div class="cta-stat-card cta-stat-card--teal">
					<span class="dashicons dashicons-admin-page cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Top Page', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value cta-stat-value--text"><?php echo esc_html( $top_page_title ); ?></span>
						<?php if ( $top_page_url && '--' !== $top_page_title ) : ?>
							<div class="cta-stat-slug-row">
								<span class="cta-stat-slug"><?php echo esc_html( $top_page_slug ); ?></span>
								<span class="cta-stat-actions">
									<?php if ( $top_page_id ) : ?>
										<a href="<?php echo esc_url( get_edit_post_link( $top_page_id ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'Edit page', 'cta-manager' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</a>
									<?php endif; ?>
									<a href="<?php echo esc_url( $top_page_url ); ?>" class="cta-stat-action" target="_blank" title="<?php esc_attr_e( 'View page', 'cta-manager' ); ?>">
										<span class="dashicons dashicons-external"></span>
									</a>
								</span>
							</div>
						<?php else : ?>
							<span class="cta-stat-meta"><?php esc_html_e( 'Most CTA activity', 'cta-manager' ); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="cta-stat-card cta-stat-card--pink">
					<span class="dashicons dashicons-groups cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Unique Visitors', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['unique_visitors'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php echo esc_html( $window_7_label ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'unique_visitors', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>

				<?php
				$best_ctr_name = $stats['best_ctr_name'] ?? '--';
				$best_ctr_id   = $stats['best_ctr_id'] ?? 0;
				$best_ctr_analytics_url = ( $best_ctr_id && $is_pro_active ) ? add_query_arg( 'cta_id', $best_ctr_id, CTA_Admin_Menu::get_admin_url( 'analytics' ) ) : '';
				$best_ctr_edit_url = $best_ctr_id ? add_query_arg( [ 'action' => 'edit', 'id' => $best_ctr_id ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) : '';
				?>
				<div class="cta-stat-card cta-stat-card--yellow">
					<span class="dashicons dashicons-awards cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Best CTA', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value cta-stat-value--text"><?php echo esc_html( $best_ctr_name ); ?></span>
						<?php if ( $best_ctr_id && '--' !== $best_ctr_name ) : ?>
							<div class="cta-stat-slug-row">
								<span class="cta-stat-slug"><?php esc_html_e( 'Top performer', 'cta-manager' ); ?></span>
								<span class="cta-stat-actions">
									<?php if ( $is_pro_active ) : ?>
										<a href="<?php echo esc_url( $best_ctr_analytics_url ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics for this CTA', 'cta-manager' ); ?>">
											<span class="dashicons dashicons-chart-line"></span>
										</a>
									<?php endif; ?>
									<a href="<?php echo esc_url( $best_ctr_edit_url ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'Edit this CTA', 'cta-manager' ); ?>">
										<span class="dashicons dashicons-edit"></span>
									</a>
								</span>
							</div>
						<?php else : ?>
							<span class="cta-stat-meta"><?php esc_html_e( 'Top performer', 'cta-manager' ); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="cta-stat-card cta-stat-card--red">
					<span class="dashicons dashicons-chart-bar cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Clicks Per CTA', 'cta-manager' ); ?></h4>
						<?php
						$clicks_per_cta = ( $stats['active_ctas'] ?? 0 ) > 0
							? round( ( $stats['total_clicks'] ?? 0 ) / $stats['active_ctas'] )
							: 0;
						?>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $clicks_per_cta ) ); ?></span>
						<span class="cta-stat-meta">
							<?php echo esc_html( $window_7_label ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'clicks', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Detailed Analytics Sections -->
	<div class="cta-dashboard">
		<div class="cta-dashboard-section">
			<div class="cta-dashboard-section-header">
				<h3><?php esc_html_e( 'Impressions Over Time', 'cta-manager' ); ?></h3>
			</div>
			<div class="cta-dashboard-card-grid">
				<?php
				$most_seen_name = $stats['most_seen_name'] ?? '--';
				$most_seen_id   = $stats['most_seen_id'] ?? 0;
				$most_seen_last_date = $stats['most_seen_last_date'] ?? '';
				$most_seen_analytics_url  = ( $most_seen_id && $is_pro_active ) ? add_query_arg( 'cta_id', $most_seen_id, CTA_Admin_Menu::get_admin_url( 'analytics' ) ) : '';
				$most_seen_edit_url = $most_seen_id ? add_query_arg( [ 'action' => 'edit', 'id' => $most_seen_id ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) : '';
				?>
				<div class="cta-stat-card cta-stat-card--blue">
					<span class="dashicons dashicons-star-filled cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Most Seen CTA', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value cta-stat-value--text"><?php echo esc_html( $most_seen_name ); ?></span>
						<?php if ( $most_seen_id && '--' !== $most_seen_name ) : ?>
							<div class="cta-stat-slug-row">
								<span class="cta-stat-slug">
									<?php
									if ( $most_seen_last_date ) {
										echo esc_html( sprintf( __( 'Last Seen %s', 'cta-manager' ), $most_seen_last_date ) );
									} else {
										esc_html_e( 'Highest impressions', 'cta-manager' );
									}
									?>
								</span>
								<span class="cta-stat-actions">
									<?php if ( $is_pro_active ) : ?>
										<a href="<?php echo esc_url( $most_seen_analytics_url ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics for this CTA', 'cta-manager' ); ?>">
											<span class="dashicons dashicons-chart-line"></span>
										</a>
									<?php endif; ?>
									<a href="<?php echo esc_url( $most_seen_edit_url ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'Edit this CTA', 'cta-manager' ); ?>">
										<span class="dashicons dashicons-edit"></span>
									</a>
								</span>
							</div>
						<?php else : ?>
							<span class="cta-stat-meta"><?php esc_html_e( 'Highest impressions', 'cta-manager' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--teal">
					<span class="dashicons dashicons-clock cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Today\'s Impressions', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['today_impressions'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'Views today', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'impressions', 'period' => '24h' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--purple">
					<span class="dashicons dashicons-chart-bar cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Period Views', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['impressions_7d'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php echo esc_html( $window_7_label ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'impressions', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--orange">
					<span class="dashicons dashicons-chart-area cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Avg Daily Views', 'cta-manager' ); ?></h4>
						<?php
						$avg_daily_impressions = $window_7_days > 0 ? ( $stats['impressions_7d'] ?? 0 ) / $window_7_days : 0;
						?>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $avg_daily_impressions, 2 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'Daily impressions', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'impressions', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<?php if ( ! $is_pro_active ) : ?>
			<?php
			$icon        = 'chart-bar';
			$title       = __( 'Unlock Pro Analytics Features', 'cta-manager' );
			$description = __( 'Upgrade to Pro to extend data retention up to 90 days, export analytics to CSV/JSON, and access advanced reporting features.', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
			unset( $icon, $title, $description );
			?>
		<?php endif; ?>

		<div class="cta-dashboard-section">
			<div class="cta-dashboard-section-header">
				<h3><?php esc_html_e( 'Clicks Over Time', 'cta-manager' ); ?></h3>
			</div>
			<div class="cta-dashboard-card-grid">
				<?php
				$most_clicked_name = $stats['most_clicked_name'] ?? '--';
				$most_clicked_id   = $stats['most_clicked_id'] ?? 0;
				$most_clicked_analytics_url = ( $most_clicked_id && $is_pro_active ) ? add_query_arg( 'cta_id', $most_clicked_id, CTA_Admin_Menu::get_admin_url( 'analytics' ) ) : '';
				$most_clicked_edit_url = $most_clicked_id ? add_query_arg( [ 'action' => 'edit', 'id' => $most_clicked_id ], CTA_Admin_Menu::get_admin_url( 'cta' ) ) : '';
				?>
				<div class="cta-stat-card cta-stat-card--pink">
					<span class="dashicons dashicons-thumbs-up cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Most Clicked CTA', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value cta-stat-value--text"><?php echo esc_html( $most_clicked_name ); ?></span>
						<?php if ( $most_clicked_id && '--' !== $most_clicked_name ) : ?>
							<div class="cta-stat-slug-row">
								<span class="cta-stat-slug"><?php esc_html_e( 'Top performer', 'cta-manager' ); ?></span>
								<span class="cta-stat-actions">
									<?php if ( $is_pro_active ) : ?>
										<a href="<?php echo esc_url( $most_clicked_analytics_url ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics for this CTA', 'cta-manager' ); ?>">
											<span class="dashicons dashicons-chart-line"></span>
										</a>
									<?php endif; ?>
									<a href="<?php echo esc_url( $most_clicked_edit_url ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'Edit this CTA', 'cta-manager' ); ?>">
										<span class="dashicons dashicons-edit"></span>
									</a>
								</span>
							</div>
						<?php else : ?>
							<span class="cta-stat-meta"><?php esc_html_e( 'Top performer', 'cta-manager' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--green">
					<span class="dashicons dashicons-clock cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Today\'s Clicks', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['today_clicks'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'Clicks today', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'clicks', 'period' => '24h' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--blue">
					<span class="dashicons dashicons-chart-bar cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Period Clicks', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_clicks'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php echo esc_html( $window_7_label ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'clicks', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--yellow">
					<span class="dashicons dashicons-chart-area cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Avg Daily Clicks', 'cta-manager' ); ?></h4>
						<?php
						$avg_daily_clicks = $window_7_days > 0 ? ( $stats['total_clicks'] ?? 0 ) / $window_7_days : 0;
						?>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $avg_daily_clicks, 2 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'Clicks per day', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'clicks', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="cta-dashboard-section">
			<div class="cta-dashboard-section-header">
				<h3><?php esc_html_e( 'Page Activity', 'cta-manager' ); ?></h3>
			</div>
			<div class="cta-dashboard-card-grid">
				<?php
				$active_page_title = $stats['most_active_page_title'] ?? '--';
				$active_page_slug  = $stats['most_active_page_slug'] ?? '--';
				$active_page_url   = $stats['most_active_page_url'] ?? '';
				$active_page_id    = $active_page_url ? url_to_postid( $active_page_url ) : 0;
				?>
				<div class="cta-stat-card cta-stat-card--teal">
					<span class="dashicons dashicons-admin-home cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Most Active Page', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value cta-stat-value--text"><?php echo esc_html( $active_page_title ); ?></span>
						<?php if ( $active_page_url && '--' !== $active_page_title ) : ?>
							<div class="cta-stat-slug-row">
								<span class="cta-stat-slug"><?php echo esc_html( $active_page_slug ); ?></span>
								<span class="cta-stat-actions">
									<?php if ( $active_page_id ) : ?>
										<a href="<?php echo esc_url( get_edit_post_link( $active_page_id ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'Edit page', 'cta-manager' ); ?>">
											<span class="dashicons dashicons-edit"></span>
										</a>
									<?php endif; ?>
									<a href="<?php echo esc_url( $active_page_url ); ?>" class="cta-stat-action" target="_blank" title="<?php esc_attr_e( 'View page', 'cta-manager' ); ?>">
										<span class="dashicons dashicons-external"></span>
									</a>
								</span>
							</div>
						<?php else : ?>
							<span class="cta-stat-meta"><?php esc_html_e( 'Highest engagement', 'cta-manager' ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--blue">
					<span class="dashicons dashicons-visibility cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Page Impressions', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['most_active_page_impressions'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'For Top CTA', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'impressions' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--purple">
					<span class="dashicons dashicons-admin-links cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Page Clicks', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['most_active_page_clicks'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'For Top CTA', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'clicks' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--orange">
					<span class="dashicons dashicons-admin-page cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Active Pages', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['pages_with_ctas'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'Pages with CTA activity', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( CTA_Admin_Menu::get_admin_url( 'analytics' ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="cta-dashboard-section">
			<div class="cta-dashboard-section-header">
				<h3><?php esc_html_e( 'Visitor Engagement', 'cta-manager' ); ?></h3>
			</div>
			<div class="cta-dashboard-card-grid">
				<div class="cta-stat-card cta-stat-card--pink">
					<span class="dashicons dashicons-chart-line cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Views Per Visitor', 'cta-manager' ); ?></h4>
						<?php
						$views_per_visitor = ( $stats['unique_visitors'] ?? 0 ) > 0
							? round( ( $stats['total_impressions'] ?? 0 ) / $stats['unique_visitors'], 1 )
							: 0;
						?>
						<span class="cta-stat-value"><?php echo esc_html( $views_per_visitor ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'Avg impressions', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'impressions', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--green">
					<span class="dashicons dashicons-businessman cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Unique Clickers', 'cta-manager' ); ?></h4>
						<span class="cta-stat-value"><?php echo esc_html( number_format_i18n( $stats['unique_clicks'] ?? 0 ) ); ?></span>
						<span class="cta-stat-meta">
							<?php echo esc_html( $window_7_label ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'clicks', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--blue">
					<span class="dashicons dashicons-chart-pie cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Visitor Click Rate', 'cta-manager' ); ?></h4>
						<?php
						$click_rate = ( $stats['unique_visitors'] ?? 0 ) > 0
							? round( ( ( $stats['unique_clicks'] ?? 0 ) / $stats['unique_visitors'] ) * 100, 1 )
							: 0;
						?>
						<span class="cta-stat-value"><?php echo esc_html( $click_rate . '%' ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'Of unique visitors', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'ctr', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<div class="cta-stat-card cta-stat-card--purple">
					<span class="dashicons dashicons-performance cta-stat-icon"></span>
					<div class="cta-stat-content">
						<h4><?php esc_html_e( 'Clicks Per Clicker', 'cta-manager' ); ?></h4>
						<?php
						$clicks_per_clicker = ( $stats['unique_clicks'] ?? 0 ) > 0
							? round( ( $stats['total_clicks'] ?? 0 ) / $stats['unique_clicks'], 1 )
							: 0;
						?>
						<span class="cta-stat-value"><?php echo esc_html( $clicks_per_clicker ); ?></span>
						<span class="cta-stat-meta">
							<?php esc_html_e( 'Avg visitor clicks', 'cta-manager' ); ?>
							<?php if ( $is_pro_active ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'metric' => 'clicks', 'period' => '7d' ], CTA_Admin_Menu::get_admin_url( 'analytics' ) ) ); ?>" class="cta-stat-action" title="<?php esc_attr_e( 'View analytics', 'cta-manager' ); ?>">
									<span class="dashicons dashicons-chart-line"></span>
								</a>
							<?php endif; ?>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<!-- Pro Upsell Footer -->

<?php include __DIR__ . '/partials/page-wrapper-end.php'; ?>
