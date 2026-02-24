<?php
/**
 * Admin Page Template - Manage Ctas
 *
 * Handles markup rendering for the manage ctas admin page template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variables available in this template:
 *
 * @var array $ctas Array of all CTAs
 * @var int $cta_count Total CTA count
 * @var array|null $editing_cta CTA being edited (if in edit mode)
 */

// Determine form visibility
$show_form = false;
if ( $editing_cta ) {
	$show_form = true;
} elseif ( isset( $_GET['show_form'] ) ) {
	$show_form = true;
}

// Page wrapper configuration
$current_page       = 'cta';
$header_title       = __( 'CTA Manager', 'cta-manager' );
$header_description = __( 'Create, edit, schedule and manage all CTAs, and keep your on-site offers and prompts organized across the entire site.', 'cta-manager' );
$topbar_actions     = [];

include __DIR__ . '/partials/page-wrapper-start.php';
?>

	<?php
	include __DIR__ . '/partials/messages.php';
	?>

	<!-- CTA List -->
	<?php if ( empty( $ctas ) ) : ?>
		<?php
		$icon         = 'megaphone';
		$title        = __( 'No CTAs Created Yet', 'cta-manager' );
		$description  = __( 'Get started by creating your first call-to-action button.', 'cta-manager' );
		$action_url   = '#cta-global-form-modal';
		$action_text  = __( 'Create CTA', 'cta-manager' );
		$action_class = 'cta-global-modal-trigger';
		$action_icon  = 'plus-alt';
		$action_attrs = 'data-open-modal="#cta-global-form-modal"';
		include __DIR__ . '/partials/empty-state.php';
		unset( $icon, $title, $description, $action_url, $action_text, $action_class, $action_icon, $action_attrs );
		?>
	<?php else : ?>
		<div class="cta-section">
			<?php
			// Count CTAs by status (matching data-cta-status values in cta-card-item.php)
			$published_count = 0;
			$draft_count     = 0;
			$scheduled_count = 0;
			$archived_count  = 0;
			$trash_count     = 0;
			$now_timestamp   = current_time( 'timestamp' );

			foreach ( $ctas as $cta ) {
				$status         = $cta['status'] ?? 'draft';
				$schedule_start = $cta['schedule_start'] ?? null;

				if ( 'trash' === $status ) {
					$trash_count++;
				} elseif ( 'archived' === $status ) {
					$archived_count++;
				} elseif ( 'draft' === $status ) {
					$draft_count++;
				} elseif ( 'scheduled' === $status ) {
					$scheduled_count++;
				} elseif ( 'publish' === $status && ! empty( $schedule_start ) && strtotime( $schedule_start ) > $now_timestamp ) {
					$scheduled_count++;
				} elseif ( 'publish' === $status ) {
					$published_count++;
				} else {
					$draft_count++;
				}
			}

			ob_start();
			?>
			<button type="button" class="cta-button cta-button-primary cta-global-modal-trigger" data-open-modal="#cta-global-form-modal">
				<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'New CTA', 'cta-manager' ); ?>
			</button>
			<?php
			$actions_html = ob_get_clean();

			// Build status filter HTML with clickable filters
			ob_start();
			?>
			<span class="cta-status-filters">
				<button type="button" class="cta-status-filter is-active" data-filter="all"><?php esc_html_e( 'All', 'cta-manager' ); ?></button>
				<span class="cta-status-separator">|</span>
				<button type="button" class="cta-status-filter" data-filter="published"><span class="cta-status-count"><?php echo esc_html( $published_count ); ?></span> <?php esc_html_e( 'Active', 'cta-manager' ); ?></button>
				<span class="cta-status-separator">|</span>
				<button type="button" class="cta-status-filter" data-filter="scheduled"><span class="cta-status-count"><?php echo esc_html( $scheduled_count ); ?></span> <?php esc_html_e( 'Scheduled', 'cta-manager' ); ?></button>
				<span class="cta-status-separator">|</span>
				<button type="button" class="cta-status-filter" data-filter="draft"><span class="cta-status-count"><?php echo esc_html( $draft_count ); ?></span> <?php esc_html_e( 'Draft', 'cta-manager' ); ?></button>
				<?php if ( $trash_count > 0 ) : ?>
					<span class="cta-status-separator">|</span>
					<button type="button" class="cta-status-filter" data-filter="trash"><span class="cta-status-count"><?php echo esc_html( $trash_count ); ?></span> <?php esc_html_e( 'Trash', 'cta-manager' ); ?></button>
				<?php endif; ?>
				<?php if ( $archived_count > 0 ) : ?>
					<span class="cta-status-separator">|</span>
					<button type="button" class="cta-status-filter" data-filter="archived"><span class="cta-status-count"><?php echo esc_html( $archived_count ); ?></span> <?php esc_html_e( 'Archive', 'cta-manager' ); ?></button>
				<?php endif; ?>
			</span>
			<?php
			$title_raw = ob_get_clean();
			include CTA_PLUGIN_DIR . 'templates/admin/partials/section-header-with-actions.php';
			unset( $actions_html, $title_raw );
			?>
			<div class="cta-manage-controls-row">
				<div class="cta-manage-controls-row__search">
					<div class="cta-search-wrapper">
						<input type="text" id="cta-search-input" class="cta-search-input" placeholder="<?php esc_attr_e( 'Search CTAs...', 'cta-manager' ); ?>" />
						<button type="button" id="cta-search-icon" class="cta-search-icon" aria-label="<?php esc_attr_e( 'Search', 'cta-manager' ); ?>">
							<span class="dashicons dashicons-search"></span>
						</button>
					</div>
				</div>
				<button type="button" class="cta-button cta-button-primary cta-modal-trigger" data-open-modal="#cta-filters-modal">
					<span class="dashicons dashicons-filter"></span>
					<?php esc_html_e( 'Filters', 'cta-manager' ); ?>
				</button>
			</div>
			<?php
			$demo_count_is_hidden = $demo_count <= 0;
			if ( ! $demo_count_is_hidden ) :
				?>
				<div class="cta-count-row">
					<span class="cta-count-badge-warning-dark">
						<?php echo esc_html( sprintf( _n( '%d Demo CTA', '%d Demo CTAs', $demo_count, 'cta-manager' ), $demo_count ) ); ?>
					</span>
				</div>
			<?php endif; ?>
			<div class="cta-cta-list">
				<?php foreach ( $ctas as $cta ) : ?>
					<?php
					include __DIR__ . '/partials/cta-card-item.php';
					?>
				<?php endforeach; ?>
			</div>
			<div class="cta-filter-empty-state" id="cta-filter-empty-state" style="display: none;">
				<div class="cta-filter-empty-state-content">
					<span class="dashicons dashicons-filter"></span>
					<p><?php esc_html_e( 'No CTAs match your current filter.', 'cta-manager' ); ?></p>
					<button type="button" class="cta-button cta-button-primary" id="cta-clear-filters-btn">
						<?php esc_html_e( 'Clear Filters', 'cta-manager' ); ?>
					</button>
				</div>
			</div>
		</div>
	<?php endif; ?>

<?php include __DIR__ . '/partials/page-wrapper-end.php'; ?>

<!-- CTA Manager Filters Modal -->
<?php
ob_start();
include CTA_PLUGIN_DIR . 'templates/admin/partials/cta-manager-filters.php';
$filters_body_html = ob_get_clean();

ob_start();
?>
<div class="cta-modal-footer-buttons">
	<button type="button" id="cta-reset-cta-filters" class="cta-button-secondary">
		<span class="dashicons dashicons-update"></span>
		<?php esc_html_e( 'Reset', 'cta-manager' ); ?>
	</button>
	<button type="button" id="cta-apply-cta-filters" class="cta-button-primary">
		<span class="dashicons dashicons-search"></span>
		<?php esc_html_e( 'Apply Filters', 'cta-manager' ); ?>
	</button>
</div>
<?php
$filters_footer_html = ob_get_clean();

$modal = [
	'id'          => 'cta-filters-modal',
	'title_html'  => '<span class="dashicons dashicons-filter"></span>' . esc_html__( 'Filter CTAs', 'cta-manager' ),
	'body_html'   => $filters_body_html,
	'footer_html' => $filters_footer_html,
	'size_class'  => 'cta-modal-md',
	'display'     => 'none',
];
include __DIR__ . '/partials/modal.php';
unset( $modal, $filters_body_html, $filters_footer_html );
?>

<?php
$modal = [
	'id'         => 'cta-preview-modal',
	'title_html' => esc_html__( 'CTA Preview', 'cta-manager' ),
	'template'   => CTA_PLUGIN_DIR . 'templates/admin/modals/cta-preview.php',
	'display'    => 'none',
];
include __DIR__ . '/partials/modal.php';
unset( $modal );
?>


<!-- Global CTA Form Modal - Used for both new and edit CTAs -->
<?php
include __DIR__ . '/partials/global-cta-form-modal.php';
?>

<?php if ( $is_pro ) : ?>
<?php
$modal = [
	'id'         => 'cta-add-icon-modal-cta',
	'title_html' => esc_html__( 'Add Custom Icon', 'cta-manager' ),
	'template'   => CTA_PLUGIN_DIR . 'templates/admin/modals/cta-add-icon-cta.php',
	'display'    => 'none',
];
include __DIR__ . '/partials/modal.php';
unset( $modal );
?>
<?php endif; ?>
