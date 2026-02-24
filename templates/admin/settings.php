<?php
/**
 * Admin Page Template - Settings
 *
 * Handles markup rendering for the settings admin page template.
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
 * @var array $settings Current plugin settings
 */

// Page wrapper configuration
$current_page       = 'settings';
$header_title       = __( 'CTA Manager Settings', 'cta-manager' );
$header_description = __( 'Configure global defaults and core plugin behavior to keep your CTAs consistent, stable, and easy to manage site-wide.', 'cta-manager' );
$topbar_actions     = [];

include __DIR__ . '/partials/page-wrapper-start.php';
?>

	<?php
	// Check Pro plugin status for the license section
	$pro_plugin_file = 'cta-manager-pro/cta-manager-pro.php';
	$pro_plugin_path = WP_PLUGIN_DIR . '/' . $pro_plugin_file;
	$is_pro_installed = file_exists( $pro_plugin_path );

	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$is_pro_active = is_plugin_active( $pro_plugin_file );
	$is_pro_class_available = class_exists( 'CTA_Pro_License' );

	// Check if Pro is enabled via filter (TDA development bypass or other override)
	$is_pro_enabled_via_filter = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
	$fake_license_key = 'DEV-MODE-LICENSE';

	// License key format: XXXX-XXXX-XXXX-XXXX (19 characters)
	$license_key_min_length = 19;

	// Development bypass mode - show fake license when Pro is enabled via filter but Pro class isn't available
	if ( $is_pro_enabled_via_filter && ! $is_pro_class_available ) :
		$license_status     = 'active';
		$license_key        = $fake_license_key;
		$is_license_active  = true;
		$has_saved_license  = true;
		$lock_license_input = true;
		$section_title      = __( 'Pro License (Development Mode)', 'cta-manager' );
		include CTA_PLUGIN_DIR . 'templates/admin/partials/license-manager.php';
		unset( $license_status, $license_key, $is_license_active, $has_saved_license, $lock_license_input, $section_title, $license_key_min_length );

	elseif ( ! $is_pro_installed ) :
		// Pro Not Installed - Use pro-upsell-footer partial
		$custom_state   = 'not-installed';
		$custom_title   = __( 'Pro License', 'cta-manager' );
		$custom_message = __( 'CTA Manager Pro is not installed. Purchase and install the Pro version to unlock advanced features and enter your license key.', 'cta-manager' );
		ob_start();
		?>
		<a href="#" class="cta-button-primary cta-upgrade-button">
			<span class="dashicons dashicons-star-filled cta-animate-fast"></span>
			<?php esc_html_e( 'Upgrade to Pro', 'cta-manager' ); ?>
		</a>
		<?php
		$custom_actions = ob_get_clean();
		include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upsell-footer.php';
		unset( $custom_state, $custom_title, $custom_message, $custom_actions );
		?>

	<?php elseif ( ! $is_pro_active ) :
		// Pro Inactive - Use pro-upsell-footer partial
		$custom_state   = 'inactive';
		$custom_title   = __( 'Pro License', 'cta-manager' );
		$custom_message = __( 'CTA Manager Pro is installed but not activated. Activate the plugin to enter your license key and unlock Pro features.', 'cta-manager' );
		ob_start();
		?>
		<?php
		$button_text  = __( 'Activate CTA Manager Pro', 'cta-manager' );
		$button_class = 'cta-button-primary';
		$button_id    = 'cta-activate-pro-plugin';
		$button_type  = 'button';
		$button_icon  = 'dashicons dashicons-yes-alt';
		$extra_attrs  = 'data-nonce="' . esc_attr( wp_create_nonce( 'cta_activate_pro_plugin' ) ) . '"';
		include CTA_PLUGIN_DIR . 'templates/admin/partials/button-with-spinner.php';
		?>
		<?php
		$custom_actions = ob_get_clean();
		include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upsell-footer.php';
		unset( $custom_state, $custom_title, $custom_message, $custom_actions );
		?>

	<?php elseif ( $is_pro_class_available ) : ?>
		<?php
		// Pro is installed and active - show license management
		$license_data      = CTA_Pro_License::get_instance()->get_license_data();
		$license_status    = $license_data['status'] ?? 'inactive';
		$license_key       = $license_data['key'] ?? '';
		$is_license_active = 'active' === $license_status;
		$has_saved_license = ! empty( $license_key ) && $is_license_active;

		include CTA_PLUGIN_DIR . 'templates/admin/partials/license-manager.php';
		unset( $license_status, $license_key, $is_license_active, $has_saved_license, $license_key_min_length );
		?>
	<?php endif; // end if ( ! $is_pro_installed ) ?>

	<?php
	// Settings form
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" id="cta-settings-form">
		<?php wp_nonce_field( 'cta_admin_nonce', 'nonce' ); ?>
		<input type="hidden" name="action" value="cta_save_settings" />

		<!-- Click Tracking -->
		<div class="cta-section">
			<?php
		$title             = __( 'Analytics', 'cta-manager' );
		$help_modal_target = '#cta-docs-modal';
		$help_icon_label   = __( 'View analytics settings help', 'cta-manager' );
		$attrs             = 'data-docs-page="settings-analytics"';
			include CTA_PLUGIN_DIR . 'templates/admin/partials/section-header-with-actions.php';
		unset( $title, $help_modal_target, $help_icon_label, $actions_html, $attrs );

			// Check Pro status
			$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
			$analytics_enabled = $settings['analytics']['enabled'] ?? true;
			$current_retention = $settings['analytics']['retention'] ?? '7';
			$current_custom_days = $settings['analytics']['retention_custom_days'] ?? 30;
			$retention_options = [
				[
					'value' => '1',
					'label' => __( '1 Day', 'cta-manager' ),
				],
				[
					'value' => '7',
					'label' => __( '1 Week', 'cta-manager' ),
				],
			];

			// Allow pro plugin to extend retention options
			$retention_options = apply_filters( 'cta_analytics_retention_options', $retention_options, $settings );
			?>

			<div class="cta-form-group cta-analytics-row">
				<?php
				cta_toggle_switch(
					'settings[analytics][enabled]',
					'',
					$analytics_enabled,
					'cta-enable-analytics'
				);
				?>

				<div id="cta-analytics-retention-wrapper" class="cta-analytics-retention-wrapper" style="<?php echo ! $analytics_enabled ? 'display: none;' : ''; ?>">
					<label for="analytics-retention" class="cta-retention-label">
						<?php esc_html_e( 'Data Retention:', 'cta-manager' ); ?>
					</label>
					<?php
					?>
					<select id="analytics-retention" name="settings[analytics][retention]" class="cta-select">
						<?php foreach ( $retention_options as $option ) : ?>
							<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $current_retention, $option['value'] ); ?>>
								<?php echo esc_html( $option['label'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<?php
					?>
				</div>
			</div>

			<!-- Custom Period Input -->
			<div
				id="cta-custom-retention-wrapper"
				class="cta-custom-retention-input"
				style="<?php echo ( $current_retention !== 'custom' || ! $analytics_enabled ) ? 'display: none;' : ''; ?>"
			>
				<div style="display: flex; gap: var(--cta-spacing-sm); align-items: center;">
					<input
						type="number"
						id="analytics-retention-custom-days"
						name="settings[analytics][retention_custom_days]"
						value="<?php echo esc_attr( $current_custom_days ); ?>"
						min="1"
						max="3650"
						class="cta-input"
						style="width: 120px;"
					/>
					<label for="analytics-retention-custom-days">
						<?php esc_html_e( 'days', 'cta-manager' ); ?>
					</label>
				</div>
			</div>

			<?php if ( ! $is_pro ) : ?>
				<?php
				$icon        = 'chart-bar';
				$title       = __( 'Unlock Pro Analytics Features', 'cta-manager' );
				$description = __( 'Upgrade to Pro to extend data retention up to 90 days, export analytics to CSV/JSON, and access advanced reporting features.', 'cta-manager' );
				include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
				unset( $icon, $title, $description );
				?>
			<?php endif; ?>
		</div>

		<!-- Custom CSS -->
		<div class="cta-section">
			<?php
			ob_start();
			?>
			<?php esc_html_e( 'Custom CSS', 'cta-manager' ); ?>
			<span style="margin-left: 8px;"><?php cta_pro_badge_inline(); ?></span>
			<?php
			$title_raw         = ob_get_clean();
			$help_modal_target = '#cta-docs-modal';
			$help_icon_label   = __( 'View custom CSS settings help', 'cta-manager' );
			$attrs             = 'data-docs-page="settings-custom-css"';
			include CTA_PLUGIN_DIR . 'templates/admin/partials/section-header-with-actions.php';
			unset( $title_raw, $help_modal_target, $help_icon_label, $actions_html, $attrs );
			?>

			<?php if ( $is_pro ) : ?>
				<div class="cta-form-group">
					<label for="custom-css">
						<?php esc_html_e( 'Additional CSS', 'cta-manager' ); ?>
					</label>
					<textarea
						id="custom-css"
						name="settings[custom_css][css]"
						rows="10"
						placeholder="<?php esc_attr_e( '/* Add your custom CSS here */', 'cta-manager' ); ?>"
						style="font-family: monospace; width: 100%;"
						data-manual-save="true"
					><?php echo esc_textarea( $settings['custom_css']['css'] ?? '' ); ?></textarea>
						<div style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
						<button type="button" class="cta-button cta-button-primary" id="cta-save-custom-css">
							<?php esc_html_e( 'Save Custom CSS', 'cta-manager' ); ?>
						</button>
					</div>
				</div>
			<?php else : ?>
				<?php
				$icon        = 'editor-code';
				$title       = __( 'Unlock Custom CSS Styling', 'cta-manager' );
				$description = __( 'Upgrade to Pro to add custom CSS and fully customize the appearance of your CTA buttons globally.', 'cta-manager' );
				include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
				unset( $icon, $title, $description );
				?>
			<?php endif; ?>
		</div>

		<!-- Performance -->
		<div class="cta-section">
			<?php
			$title             = __( 'Performance', 'cta-manager' );
			$help_modal_target = '#cta-docs-modal';
			$help_icon_label   = __( 'View performance settings help', 'cta-manager' );
			$attrs             = 'data-docs-page="settings-performance"';
			include CTA_PLUGIN_DIR . 'templates/admin/partials/section-header-with-actions.php';
			unset( $title, $help_modal_target, $help_icon_label, $actions_html, $attrs );
			?>

				<div class="cta-form-group">
					<?php
					$id                  = 'cta-load-scripts-footer';
					$name                = 'settings[performance][load_scripts_footer]';
					$checked             = (bool) ( $is_pro && ( $settings['performance']['load_scripts_footer'] ?? false ) );
					$label               = __( 'Load CTA Scripts in Footer', 'cta-manager' );
					$disabled            = ! $is_pro;
					$show_badge          = true;
					$extra_wrapper_class = ! $is_pro ? 'cta-toggle-disabled' : '';
					include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-toggle-row.php';
					unset( $id, $name, $checked, $label, $disabled, $show_badge, $extra_wrapper_class );
					?>

				<?php if ( ! $is_pro ) : ?>
					<?php
					$icon        = 'performance';
					$title       = __( 'Unlock Performance Optimization', 'cta-manager' );
					$description = __( 'Upgrade to Pro to load scripts in the footer, improve page load times, and optimize CTA delivery.', 'cta-manager' );
					include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
					unset( $icon, $title, $description );
					?>
				<?php endif; ?>
			</div>

		</div>

		<!-- Custom Icons -->
		<div class="cta-section" id="cta-custom-icons-section">
			<?php
			ob_start();
			?>
			<?php esc_html_e( 'Custom Icons', 'cta-manager' ); ?>
			<span style="margin-left: 8px;"><?php cta_pro_badge_inline(); ?></span>
			<?php
			$title_raw         = ob_get_clean();
			$help_modal_target = '#cta-docs-modal';
			$help_icon_label   = __( 'View custom icons settings help', 'cta-manager' );
			$attrs             = 'data-docs-page="settings-custom-icons"';
			include CTA_PLUGIN_DIR . 'templates/admin/partials/section-header-with-actions.php';
			unset( $title_raw, $help_modal_target, $help_icon_label, $actions_html, $attrs );
			?>

			<?php if ( $is_pro ) : ?>
				<?php
				$data = CTA_Data::get_instance();
				$custom_icons = $data->get_custom_icons();
				?>

					<div class="cta-form-group">
					<!-- Custom Icons Grid -->
					<div id="cta-custom-icons-grid" class="cta-custom-icons-grid" <?php echo empty( $custom_icons ) ? 'style="display: none;"' : ''; ?>>
						<?php foreach ( $custom_icons as $icon ) : ?>
							<?php
							$icon_id      = $icon['id'];
							$icon_name    = $icon['name'];
							$svg_content  = $icon['svg'];
							include CTA_PLUGIN_DIR . 'templates/admin/partials/custom-icon-item.php';
							unset( $icon_id, $icon_name, $svg_content );
							?>
						<?php endforeach; ?>
					</div>

					<!-- Empty State -->
					<div id="cta-custom-icons-empty" <?php echo ! empty( $custom_icons ) ? 'style="display: none;"' : ''; ?>>
						<?php
						$icon         = 'format-image';
						$title        = __( 'No custom icons yet.', 'cta-manager' );
						$description  = __( 'Add your first icon to get started.', 'cta-manager' );
						$action_url   = '#';
						$action_text  = __( 'Add Icon', 'cta-manager' );
						$action_class = '';
						$action_icon  = 'plus-alt2';
						$action_attrs = 'id="cta-add-custom-icon-btn"';
						include CTA_PLUGIN_DIR . 'templates/admin/partials/empty-state.php';
						unset( $icon, $title, $description, $action_url, $action_text, $action_class, $action_icon, $action_attrs );
						?>
					</div>

					<!-- Add Icon Button (only when icons exist) -->
					<?php if ( ! empty( $custom_icons ) ) : ?>
						<div style="margin-top: var(--cta-spacing-md);">
							<?php cta_button_with_icon( __( 'Add Icon', 'cta-manager' ), 'plus-alt2', 'cta-button-secondary', 'cta-add-custom-icon-btn' ); ?>
						</div>
					<?php endif; ?>
				</div>

			<?php else : ?>
				<?php
				$icon        = 'format-image';
				$title       = __( 'Unlock Custom Icons', 'cta-manager' );
				$description = __( 'Upgrade to Pro to upload and use your own custom SVG icons in CTA buttons.', 'cta-manager' );
				include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
				unset( $icon, $title, $description );
				?>
			<?php endif; ?>
		</div>

		<!-- Analytics Version (Temporary Toggle) -->
		<!-- Data Management -->
		<div class="cta-section" style="margin-bottom: var(--cta-spacing-xl);">
			<?php
			$title             = __( 'Data Management', 'cta-manager' );
			$help_modal_target = '#cta-docs-modal';
			$help_icon_label   = __( 'View data management settings help', 'cta-manager' );
			$attrs             = 'data-docs-page="settings-data-management"';
			include CTA_PLUGIN_DIR . 'templates/admin/partials/section-header-with-actions.php';
			unset( $title, $help_modal_target, $help_icon_label, $actions_html, $attrs );
			?>

			<div class="cta-form-group">
				<?php
				$delete_enabled = $is_pro ? ( $settings['data_management']['delete_on_uninstall'] ?? true ) : true;
				if ( ! $is_pro ) :
					?>
					<input type="hidden" name="settings[data_management][delete_on_uninstall]" value="1" />
					<?php
				endif;
				$id                  = 'cta-delete-data-toggle';
				$name                = 'settings[data_management][delete_on_uninstall]';
				$checked             = $delete_enabled;
				$label               = __( 'Delete All Data on Uninstall', 'cta-manager' );
				$disabled            = ! $is_pro;
				$show_badge          = ! $is_pro;
				$extra_wrapper_class = '';
				include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-toggle-row.php';
				unset( $id, $name, $checked, $label, $disabled, $show_badge, $extra_wrapper_class );
				?>

				<?php if ( ! $is_pro ) : ?>
					<?php
					$icon        = 'database';
					$title       = __( 'Unlock Data Management Controls', 'cta-manager' );
					$description = __( 'Upgrade to Pro to choose whether your data is preserved or deleted when uninstalling, and gain full control over your CTA data lifecycle.', 'cta-manager' );
					include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
					unset( $icon, $title, $description );
					?>
				<?php endif; ?>
			</div>
		</div>

	</form>

<?php include __DIR__ . '/partials/page-wrapper-end.php'; ?>

<?php
// Remove License Confirmation Modal
if ( $is_pro_class_available && $has_saved_license ) :
	ob_start();
	?>
	<div class="cta-info-box cta-info-box--warning">
		<span class="cta-info-box__icon dashicons dashicons-warning"></span>
		<div>
			<p class="cta-info-box__title"><?php esc_html_e( 'Are you sure you want to remove this license?', 'cta-manager' ); ?></p>
			<p class="cta-info-box__body">
				<?php esc_html_e( 'This will deactivate Pro features on this site. You can reactivate it later by entering the license key again.', 'cta-manager' ); ?>
			</p>
		</div>
	</div>
	<?php
	$body_html = ob_get_clean();

	ob_start();
	?>
	<div style="display: flex; justify-content: flex-end; gap: var(--cta-spacing-sm);">
		<button type="button" class="cta-button cta-button-secondary" data-close-modal>
			<?php esc_html_e( 'Cancel', 'cta-manager' ); ?>
		</button>
		<button type="button" id="cta-confirm-remove-license" class="cta-button cta-button-danger">
			<span class="dashicons dashicons-trash"></span>
			<?php esc_html_e( 'Remove License', 'cta-manager' ); ?>
		</button>
	</div>
	<?php
	$footer_html = ob_get_clean();

	$modal = [
		'id'          => 'cta-remove-license-modal',
		'title_html'  => '<span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Remove License', 'cta-manager' ),
		'body_html'   => $body_html,
		'footer_html' => $footer_html,
		'extra_class' => 'cta-modal-confirm',
		'size_class'  => 'cta-modal-sm',
		'display'     => 'none',
	];
	include CTA_PLUGIN_DIR . 'templates/admin/partials/modal.php';
	unset( $modal, $body_html, $footer_html );
endif;
?>

<?php if ( $is_pro ) : ?>
<?php
$modal = [
	'id'         => 'cta-add-icon-modal',
	'title_html' => esc_html__( 'Add Custom Icon', 'cta-manager' ),
	'template'   => CTA_PLUGIN_DIR . 'templates/admin/modals/add-icon.php',
	'display'    => 'none',
];
include CTA_PLUGIN_DIR . 'templates/admin/partials/modal.php';
unset( $modal );
?>

<?php
$modal = [
	'id'          => 'cta-delete-icon-modal',
	'title_html'  => esc_html__( 'Delete Icon', 'cta-manager' ),
	'template'    => CTA_PLUGIN_DIR . 'templates/admin/modals/delete-icon.php',
	'extra_class' => 'cta-modal-confirm',
	'display'     => 'none',
];
include CTA_PLUGIN_DIR . 'templates/admin/partials/modal.php';
unset( $modal );
?>
<?php
endif; // end if ( $is_pro )
?>
