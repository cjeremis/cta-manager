<?php
/**
 * Admin Page Template - Tools
 *
 * Handles markup rendering for the tools admin page template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$current_page       = 'tools';
$header_title       = __( 'CTA Manager Tools', 'cta-manager' );
$header_description = __( 'Access utilities that help you maintain, troubleshoot, and manage your CTA setup efficiently.', 'cta-manager' );
$topbar_actions     = [];

include __DIR__ . '/partials/page-wrapper-start.php';
?>

	<?php
	// Show toast notification if there's a message parameter
	$message = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';
	if ( $message ) :
		$error_messages = [ 'import_failed', 'invalid_file', 'invalid_nonce', 'demo_file_missing', 'demo_file_invalid', 'delete_failed' ];
		$type           = in_array( $message, $error_messages, true ) ? 'error' : 'success';
		$copy           = [
			'imported'           => __( 'Settings imported successfully.', 'cta-manager' ),
			'import_failed'      => __( 'Failed to import settings. Please check the file format and try again.', 'cta-manager' ),
			'invalid_file'       => __( 'Invalid import file. The file must be a valid CTA Manager JSON export.', 'cta-manager' ),
			'invalid_nonce'      => __( 'Security check failed. Please refresh the page and try again.', 'cta-manager' ),
			'reset_success'      => __( 'Data reset completed.', 'cta-manager' ),
			'demo_imported'      => __( 'Demo data imported successfully.', 'cta-manager' ),
			'demo_deleted'       => __( 'Demo data deleted successfully.', 'cta-manager' ),
			'demo_file_missing'  => __( 'Demo data file not found. Please reinstall the plugin.', 'cta-manager' ),
			'demo_file_invalid'  => __( 'Demo data file is corrupted or invalid. Please reinstall the plugin.', 'cta-manager' ),
			'delete_failed'      => __( 'Failed to delete demo data. Please try again.', 'cta-manager' ),
			'debug_enabled'      => __( 'Debug mode enabled.', 'cta-manager' ),
			'debug_disabled'     => __( 'Debug mode disabled.', 'cta-manager' ),
		];
		$toast_message = $copy[ $message ] ?? __( 'Operation completed.', 'cta-manager' );
		?>
		<script>
		jQuery(document).ready(function($) {
			if (window.CTAToast) {
				CTAToast.<?php echo esc_js( $type ); ?>('<?php echo esc_js( $toast_message ); ?>');
			}
		});
		</script>
	<?php endif; ?>

	<div class="cta-export-import-container">
		<!-- Export Section -->
		<div class="cta-section">
			<h2 class="cta-section-title"><?php esc_html_e( 'Export Data', 'cta-manager' ); ?></h2>
			<div class="cta-card-content">

				<?php
				// Check if there is any data to export (CTAs or analytics)
				$data     = CTA_Data::get_instance();
				$all_ctas = $data->get_ctas();
				$has_ctas = ! empty( $all_ctas );

				$has_analytics = false;
				if ( class_exists( 'CTA_Events_Repository' ) ) {
					$events_repo   = CTA_Events_Repository::get_instance();
					$has_analytics = $events_repo->count_events() > 0;
				}

				$has_export_data = $has_ctas || $has_analytics;
				?>

				<?php if ( $has_export_data ) : ?>
					<?php
					$export_types = [
						[
							'id'    => 'cta-export-button',
							'text'  => __( 'Download JSON File', 'cta-manager' ),
							'icon'  => 'download',
							'class' => 'cta-button cta-button-primary',
						],
						[
							'id'    => 'cta-view-data',
							'text'  => __( 'View Data', 'cta-manager' ),
							'icon'  => 'visibility',
							'class' => 'cta-button cta-button-secondary',
						],
					];
					include __DIR__ . '/partials/export-actions.php';
					unset( $export_types );
					?>
				<?php else : ?>
					<?php
					$icon        = 'download';
					$title       = __( 'No Data to Export', 'cta-manager' );
					$description = __( 'There are no CTAs or analytics data available to export.', 'cta-manager' );
					$extra_class = '';
					include __DIR__ . '/partials/empty-state.php';
					unset( $icon, $title, $description, $extra_class );
					?>
				<?php endif; ?>
			</div>
		</div>

		<!-- Import Section -->
		<div class="cta-section">
			<?php
			$title             = __( 'Import Data', 'cta-manager' );
			$help_modal_target = '#cta-docs-modal';
			$help_icon_label   = __( 'View import data help', 'cta-manager' );
			$attrs             = 'data-docs-page="tools-import"';
			include __DIR__ . '/partials/section-header-with-actions.php';
			unset( $title, $help_modal_target, $help_icon_label, $actions_html, $attrs );
			?>
			<div class="cta-card-content">
				<form method="post" enctype="multipart/form-data" action="#" class="cta-import-form" id="cta-import-form">

					<div class="cta-import-settings-row">
						<!-- File Input Column -->
						<div class="cta-form-group cta-import-setting">
							<label for="cta-import-file">
								<?php esc_html_e( 'File', 'cta-manager' ); ?>
								<span class="cta-required">*</span>
							</label>
							<?php
							$input_id         = 'cta-import-file';
							$input_name       = 'file';
							$wrapper_id       = 'cta-file-input-wrapper';
							$selected_id      = 'cta-file-selected';
							$file_name_id     = 'cta-file-name';
							$file_size_id     = 'cta-file-size';
							$remove_button_id = 'cta-file-remove';
							$label_text       = __( 'Drop or Choose JSON File', 'cta-manager' );
							include CTA_PLUGIN_DIR . 'templates/admin/partials/file-input-wrapper.php';
							unset( $input_id, $input_name, $wrapper_id, $selected_id, $file_name_id, $file_size_id, $remove_button_id, $label_text );
							?>
						</div>

						<!-- Import Mode Column -->
						<?php $is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled(); ?>
						<div class="cta-form-group cta-import-setting">
							<label for="cta-import-mode">
								<?php esc_html_e( 'Mode', 'cta-manager' ); ?>
							</label>
							<input type="hidden" name="import[mode]" value="replace" />
							<select id="cta-import-mode" class="cta-select" disabled>
								<option value="replace" selected><?php esc_html_e( 'Replace all data', 'cta-manager' ); ?></option>
							</select>
						</div>

						<!-- Backup Column -->
						<div class="cta-form-group cta-import-setting">
							<label for="cta-import-backup" style="display: flex; align-items: center; gap: 8px;">
								<?php esc_html_e( 'Backup', 'cta-manager' ); ?>
								<?php if ( ! $is_pro ) : ?>
									<?php cta_pro_badge_inline(); ?>
								<?php endif; ?>
							</label>
							<?php
							$input_name  = 'import[backup]';
							$input_id    = 'cta-import-backup';
							$label       = '';
							$checked     = (bool) $is_pro;
							$extra_class = ! $is_pro ? 'cta-toggle-disabled' : '';
							$input_attrs = $is_pro ? '' : 'disabled="disabled"';
							include CTA_PLUGIN_DIR . 'templates/admin/partials/toggle-switch.php';
							unset( $input_name, $input_id, $label, $checked, $extra_class, $input_attrs );
							?>
						</div>
					</div>

				<?php if ( ! $is_pro ) : ?>
					<?php
					$icon        = 'update';
					$title       = __( 'Unlock Advanced Import Options', 'cta-manager' );
					$message     = __( 'Upgrade to Pro for merge imports (combine with existing data) and automatic backups before importing.', 'cta-manager' );
					$button_url  = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
					$button_text = __( 'Upgrade Now', 'cta-manager' );
					include CTA_PLUGIN_DIR . 'templates/admin/partials/upgrade-notice.php';
					unset( $icon, $title, $message, $button_url, $button_text );
					?>
				<?php endif; ?>

				<div class="cta-import-actions" id="cta-import-actions" style="display: none;">
					<button type="button" class="cta-button-primary" id="cta-import-button" disabled>
						<?php esc_html_e( 'Import Settings', 'cta-manager' ); ?>
					</button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=cta-manager' ) ); ?>" class="cta-button cta-button-secondary">
						<?php esc_html_e( 'Cancel', 'cta-manager' ); ?>
					</a>
				</div>
				</form>

				<!-- Import Preview (shown after file selection) -->
				<div id="cta-import-preview" class="cta-import-preview" style="display: none;">
					<h3><?php esc_html_e( 'Import Preview', 'cta-manager' ); ?></h3>
					<pre id="cta-preview-content" class="cta-json-preview"><code></code></pre>
				</div>
			</div>
		</div>

		<!-- Demo Data Management -->
		<div class="cta-section cta-demo-section">
			<?php
			$title             = __( 'Demo Data', 'cta-manager' );
			$help_modal_target = '#cta-docs-modal';
			$help_icon_label   = __( 'View demo data details', 'cta-manager' );
			$attrs             = 'data-docs-page="tools-demo"';
			include __DIR__ . '/partials/section-header-with-actions.php';
			unset( $title, $help_modal_target, $help_icon_label, $actions_html, $attrs );
			?>
			<div class="cta-card-content">
				<?php
				// Count demo CTAs
				$data        = CTA_Data::get_instance();
				$all_ctas    = $data->get_ctas();
				$demo_ctas   = array_filter( $all_ctas, function( $cta ) {
					return isset( $cta['_demo'] ) && $cta['_demo'] === true;
				} );
				$demo_count  = count( $demo_ctas );
				$has_demo    = $demo_count > 0;
				?>

				<div class="cta-demo-row">
					<div class="cta-demo-actions">
					<?php if ( ! $has_demo ) : ?>
						<button type="button" id="cta-open-import-demo-modal" class="cta-button cta-button-primary">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Import Demo Data', 'cta-manager' ); ?>
						</button>
					<?php else : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete all demo CTAs? This cannot be undone.', 'cta-manager' ) ); ?>');">
							<?php wp_nonce_field( 'cta_admin_nonce', 'nonce' ); ?>
							<input type="hidden" name="action" value="cta_delete_demo_data" />
							<?php
							$button_text = __( 'Delete Demo Data', 'cta-manager' );
							$icon = 'trash';
							$button_class = 'cta-button-danger';
							$button_type = 'submit';
							include __DIR__ . '/partials/button-with-icon.php';
							unset( $button_text, $icon, $button_class, $button_type );
							?>
						</form>
					<?php endif; ?>
				</div>
					<div class="cta-demo-status-bar">
						<?php if ( $has_demo ) : ?>
							<span class="cta-demo-status cta-status-active">
								<span class="dashicons dashicons-yes-alt"></span>
								<strong><?php echo esc_html( sprintf( _n( '%d demo CTA loaded', '%d demo CTAs loaded', $demo_count, 'cta-manager' ), $demo_count ) ); ?></strong>
							</span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Reset Data Section -->
		<div class="cta-section">
			<?php
			$title             = __( 'Reset Data', 'cta-manager' );
			$help_modal_target = '#cta-docs-modal';
			$help_icon_label   = __( 'View reset data help', 'cta-manager' );
			$attrs             = 'data-docs-page="tools-reset"';
			include __DIR__ . '/partials/section-header-with-actions.php';
			unset( $title, $help_modal_target, $help_icon_label, $actions_html, $attrs );
		?>
			<div class="cta-card-content">
				<?php
				// Check if there's any data to reset
				$data        = CTA_Data::get_instance();
				$all_ctas    = $data->get_ctas();
				$has_ctas    = ! empty( $all_ctas );

				$has_analytics = false;
				if ( class_exists( 'CTA_Events_Repository' ) ) {
					$events_repo   = CTA_Events_Repository::get_instance();
					$has_analytics = $events_repo->count_events() > 0;
				}

				$has_data = $has_ctas || $has_analytics;
				?>

			<div class="cta-demo-row">
				<div class="cta-demo-actions">
					<button type="button" id="cta-open-reset-modal" class="cta-button cta-button-danger"<?php echo ! $has_data ? ' disabled' : ''; ?>>
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Reset Data', 'cta-manager' ); ?>
					</button>
				</div>
			</div>
			</div>
		</div>

	</div>

	<!-- Debug Mode -->
	<div class="cta-section">
		<?php
		$title             = __( 'Debug Mode', 'cta-manager' );
		$help_modal_target = '#cta-docs-modal';
		$help_icon_label   = __( 'View debug mode help', 'cta-manager' );
		$attrs             = 'data-docs-page="tools-debug"';
		include __DIR__ . '/partials/section-header-with-actions.php';
		unset( $title, $help_modal_target, $help_icon_label, $actions_html, $attrs );
		?>
		<div class="cta-card-content">
			<?php
			$data = CTA_Data::get_instance();
			$settings = $data->get_settings();
			$debug_enabled = ! empty( $settings['debug']['enabled'] );
			?>
			<div class="cta-demo-row" id="cta-debug-toggle" data-nonce="<?php echo esc_attr( wp_create_nonce( 'cta_debug_toggle' ) ); ?>">
				<div class="cta-demo-actions">
					<?php
					$input_name  = 'cta_debug_enabled';
					$input_id    = 'cta-debug-enabled';
					$label       = __( 'Enable Debug Mode', 'cta-manager' );
					$checked     = $debug_enabled;
					$extra_class = '';
					$input_attrs = '';
					include CTA_PLUGIN_DIR . 'templates/admin/partials/toggle-switch.php';
					unset( $input_name, $input_id, $label, $checked, $extra_class, $input_attrs );
					?>
				</div>
			</div>
		</div>
	</div>

	<?php
	// Export Options Modal (shown when clicking Download JSON File button)
	// Structure mirrors import-demo-modal.php exactly
	ob_start();
	?>
	<div class="cta-import-demo-options">
		<p class="cta-import-intro">
			<?php esc_html_e( 'Select which data to include in your export file.', 'cta-manager' ); ?>
		</p>

		<div class="cta-import-toggle-group">
			<!-- Settings Toggle -->
			<div class="cta-import-toggle-row">
				<label class="cta-toggle cta-toggle--filter">
					<input type="checkbox" id="cta-export-settings" checked />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
				<div class="cta-toggle-content">
					<span class="cta-toggle-label"><?php esc_html_e( 'Settings', 'cta-manager' ); ?></span>
					<span class="cta-toggle-description">
						<?php esc_html_e( 'Include global plugin configuration', 'cta-manager' ); ?>
					</span>
				</div>
			</div>

			<!-- CTAs Toggle -->
			<div class="cta-import-toggle-row">
				<label class="cta-toggle cta-toggle--filter">
					<input type="checkbox" id="cta-export-ctas" checked />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
				<div class="cta-toggle-content">
					<span class="cta-toggle-label"><?php esc_html_e( 'CTAs', 'cta-manager' ); ?></span>
					<span class="cta-toggle-description">
						<?php esc_html_e( 'Include all CTA configurations and styling', 'cta-manager' ); ?>
					</span>
				</div>
			</div>

			<!-- Analytics Toggle -->
			<div class="cta-import-toggle-row">
				<label class="cta-toggle cta-toggle--filter">
					<input type="checkbox" id="cta-export-analytics" checked />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
				<div class="cta-toggle-content">
					<span class="cta-toggle-label"><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></span>
					<span class="cta-toggle-description">
						<?php esc_html_e( 'Include analytics events within retention window', 'cta-manager' ); ?>
					</span>
				</div>
			</div>

			<!-- Notifications Toggle -->
			<div class="cta-import-toggle-row">
				<label class="cta-toggle cta-toggle--filter">
					<input type="checkbox" id="cta-export-notifications" checked />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
				<div class="cta-toggle-content">
					<span class="cta-toggle-label"><?php esc_html_e( 'Notifications', 'cta-manager' ); ?></span>
					<span class="cta-toggle-description">
						<?php esc_html_e( 'Include notification history and dismissals', 'cta-manager' ); ?>
					</span>
				</div>
			</div>
		</div>

		<p class="cta-import-hint">
			<span class="dashicons dashicons-info"></span>
			<?php esc_html_e( 'Exports are JSON only and can be imported back into CTA Manager.', 'cta-manager' ); ?>
		</p>
	</div>
	<?php
	$export_options_body_html = ob_get_clean();
	ob_start();
	?>
	<div style="display: flex; justify-content: flex-end; gap: var(--cta-spacing-sm);">
		<button type="button" id="cta-export-download-btn" class="cta-button cta-button-primary">
			<span class="dashicons dashicons-download"></span>
			<?php esc_html_e( 'Download JSON', 'cta-manager' ); ?>
		</button>
	</div>
	<?php
	$export_options_footer_html = ob_get_clean();
	$modal                      = [
		'id'          => 'cta-export-options-modal',
		'title_html'  => '<span class="dashicons dashicons-upload"></span>' . esc_html__( 'Export Data', 'cta-manager' ),
		'body_html'   => $export_options_body_html,
		'footer_html' => $export_options_footer_html,
		'size_class'  => 'cta-modal-md',
		'display'     => 'none',
	];
	include CTA_PLUGIN_DIR . 'templates/admin/partials/modal.php';
	unset( $modal, $export_options_body_html, $export_options_footer_html );
	?>

	<?php
	// View Data Modal (with toggle switches in footer)
	ob_start();
	?>
	<textarea id="cta-export-json-preview" class="cta-json-textarea" readonly></textarea>
	<?php
	$export_body_html = ob_get_clean();
	ob_start();
	?>
	<div class="cta-view-data-footer">
		<div class="cta-view-data-toggles">
			<div class="cta-view-toggle-item">
				<label class="cta-toggle cta-toggle--filter cta-toggle--sm">
					<input type="checkbox" id="cta-view-toggle-ctas" class="cta-view-toggle" data-key="ctas" checked />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
				<span class="cta-view-toggle-label"><?php esc_html_e( 'CTAs', 'cta-manager' ); ?></span>
			</div>
			<div class="cta-view-toggle-item">
				<label class="cta-toggle cta-toggle--filter cta-toggle--sm">
					<input type="checkbox" id="cta-view-toggle-settings" class="cta-view-toggle" data-key="settings" checked />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
				<span class="cta-view-toggle-label"><?php esc_html_e( 'Settings', 'cta-manager' ); ?></span>
			</div>
			<div class="cta-view-toggle-item">
				<label class="cta-toggle cta-toggle--filter cta-toggle--sm">
					<input type="checkbox" id="cta-view-toggle-analytics" class="cta-view-toggle" data-key="analytics" checked />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
				<span class="cta-view-toggle-label"><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></span>
			</div>
			<div class="cta-view-toggle-item">
				<label class="cta-toggle cta-toggle--filter cta-toggle--sm">
					<input type="checkbox" id="cta-view-toggle-notifications" class="cta-view-toggle" data-key="notifications" checked />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
				<span class="cta-view-toggle-label"><?php esc_html_e( 'Notifications', 'cta-manager' ); ?></span>
			</div>
		</div>
		<div class="cta-view-data-actions">
			<?php cta_button_with_icon( __( 'Copy to Clipboard', 'cta-manager' ), 'clipboard', 'cta-button cta-button-secondary', 'cta-modal-copy-json' ); ?>
			<?php cta_button_with_icon( __( 'Download JSON', 'cta-manager' ), 'download', 'cta-button cta-button-primary', 'cta-modal-download-json' ); ?>
		</div>
	</div>
	<?php
	$export_footer_html = ob_get_clean();
	$modal              = [
		'id'          => 'cta-tools-export-data-modal',
		'title_html'  => '<span class="dashicons dashicons-visibility"></span>' . esc_html__( 'View Data', 'cta-manager' ),
		'body_html'   => $export_body_html,
		'footer_html' => $export_footer_html,
		'size_class'  => 'cta-modal-xl',
	];
	include CTA_PLUGIN_DIR . 'templates/admin/partials/modal.php';
	unset( $modal, $export_body_html, $export_footer_html );
	?>

	<?php
	// Reset Data Modal
	// Structure mirrors import-demo-modal.php exactly
	ob_start();
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="cta-reset-data-form">
		<?php wp_nonce_field( 'cta_admin_nonce', 'nonce' ); ?>
		<input type="hidden" name="action" value="cta_reset_data" />
	</form>
	<div class="cta-import-demo-options">
		<p class="cta-import-intro">
			<?php esc_html_e( 'Select which data to permanently delete.', 'cta-manager' ); ?>
		</p>

		<div class="cta-info-box cta-info-box--danger" style="margin-bottom: var(--cta-spacing-md);">
			<span class="cta-info-box__icon dashicons dashicons-warning"></span>
			<div>
				<p class="cta-info-box__title"><?php esc_html_e( 'Warning: This action cannot be undone', 'cta-manager' ); ?></p>
				<p class="cta-info-box__body">
					<?php esc_html_e( 'Reset removes data immediately. Export first if you may need it later.', 'cta-manager' ); ?>
				</p>
			</div>
		</div>

		<div class="cta-import-toggle-group cta-toggle-group--right">
			<!-- CTAs Toggle -->
			<div class="cta-import-toggle-row cta-toggle-row--right">
				<div class="cta-toggle-content">
					<span class="cta-toggle-label"><?php esc_html_e( 'CTAs', 'cta-manager' ); ?></span>
					<span class="cta-toggle-description">
						<?php esc_html_e( 'Delete all CTA configurations and styling', 'cta-manager' ); ?>
					</span>
				</div>
				<label class="cta-toggle cta-toggle--filter">
					<input type="checkbox" name="reset[ctas]" id="cta-reset-ctas" value="1" form="cta-reset-data-form" />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
			</div>

			<!-- Settings Toggle -->
			<div class="cta-import-toggle-row cta-toggle-row--right">
				<div class="cta-toggle-content">
					<span class="cta-toggle-label"><?php esc_html_e( 'Settings', 'cta-manager' ); ?></span>
					<span class="cta-toggle-description">
						<?php esc_html_e( 'Reset all plugin settings to defaults', 'cta-manager' ); ?>
					</span>
				</div>
				<label class="cta-toggle cta-toggle--filter">
					<input type="checkbox" name="reset[settings]" id="cta-reset-settings" value="1" form="cta-reset-data-form" />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
			</div>

			<!-- Analytics Toggle -->
			<div class="cta-import-toggle-row cta-toggle-row--right">
				<div class="cta-toggle-content">
					<span class="cta-toggle-label"><?php esc_html_e( 'Analytics', 'cta-manager' ); ?></span>
					<span class="cta-toggle-description">
						<?php esc_html_e( 'Delete all impression and click tracking data', 'cta-manager' ); ?>
					</span>
				</div>
				<label class="cta-toggle cta-toggle--filter">
					<input type="checkbox" name="reset[analytics]" id="cta-reset-analytics" value="1" form="cta-reset-data-form" />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
			</div>

			<!-- Notifications Toggle -->
			<div class="cta-import-toggle-row cta-toggle-row--right">
				<div class="cta-toggle-content">
					<span class="cta-toggle-label"><?php esc_html_e( 'Notifications', 'cta-manager' ); ?></span>
					<span class="cta-toggle-description">
						<?php esc_html_e( 'Clear notification history and dismissals', 'cta-manager' ); ?>
					</span>
				</div>
				<label class="cta-toggle cta-toggle--filter">
					<input type="checkbox" name="reset[notifications]" id="cta-reset-notifications" value="1" form="cta-reset-data-form" />
					<span class="cta-toggle-track">
						<span class="cta-toggle-thumb"></span>
					</span>
				</label>
			</div>
		</div>

		<p class="cta-import-hint">
			<span class="dashicons dashicons-info"></span>
			<?php esc_html_e( 'Demo data is managed separately from the Demo Data section.', 'cta-manager' ); ?>
		</p>
	</div>
	<?php
	$body_html = ob_get_clean();

	ob_start();
	?>
	<div style="display: flex; justify-content: space-between; align-items: center; width: 100%; gap: var(--cta-spacing-sm);">
		<button type="button" id="cta-reset-export-first" class="cta-button cta-button-secondary">
			<span class="dashicons dashicons-download"></span>
			<?php esc_html_e( 'Export First', 'cta-manager' ); ?>
		</button>
		<div style="display: flex; gap: var(--cta-spacing-sm);">
			<button type="submit" form="cta-reset-data-form" id="cta-reset-confirm-button" class="cta-button cta-button-danger">
				<span class="dashicons dashicons-trash"></span>
				<?php esc_html_e( 'Reset Data', 'cta-manager' ); ?>
			</button>
		</div>
	</div>
	<?php
	$footer_html = ob_get_clean();
	$modal = [
		'id'          => 'cta-reset-data-modal',
		'title_html'  => '<span class="dashicons dashicons-trash"></span>' . esc_html__( 'Reset Data', 'cta-manager' ),
		'body_html'   => $body_html,
		'footer_html' => $footer_html,
		'size_class'  => 'cta-modal-md',
		'display'     => 'none',
	];
	include __DIR__ . '/partials/modal.php';
	unset( $modal, $body_html, $footer_html );
	?>

	<?php
// Import Demo Data Modal is now rendered globally in page-wrapper-end.php
include __DIR__ . '/partials/page-wrapper-end.php';
?>
