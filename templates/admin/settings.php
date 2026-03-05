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
	$has_saved_license      = false;

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
		unset( $license_status, $license_key, $is_license_active, $lock_license_input, $section_title, $license_key_min_length );

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
		unset( $license_status, $license_key, $is_license_active, $license_key_min_length );
		?>
	<?php endif; // end if ( ! $is_pro_installed ) ?>

	<?php
	// Check Pro status (used by Business Hours and all sections below)
	$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
	?>

	<!-- Business Hours -->
	<div class="cta-section" id="business-hours">
		<?php
		ob_start();
		?>
		<?php esc_html_e( 'Business Hours', 'cta-manager' ); ?>
		<span style="margin-left: 8px;"><?php cta_pro_badge_inline(); ?></span>
		<?php
		$title_raw         = ob_get_clean();
		$help_modal_target = '#cta-docs-modal';
		$help_icon_label   = __( 'View business hours settings help', 'cta-manager' );
		$attrs             = 'data-docs-page="settings-business-hours"';
		include CTA_PLUGIN_DIR . 'templates/admin/partials/section-header-with-actions.php';
		unset( $title_raw, $help_modal_target, $help_icon_label, $actions_html, $attrs );
		?>

		<?php if ( $is_pro && class_exists( 'CTA_Pro_Schedule' ) ) : ?>
			<?php
			$schedule_instance = CTA_Pro_Schedule::get_instance();
			$schedule_settings = $schedule_instance->get_settings();
			$schedule_weekdays = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
			?>

			<?php if ( isset( $_GET['schedule_saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
				<div class="notice notice-success is-dismissible" style="margin: 0 0 16px;">
					<p><?php esc_html_e( 'Business hours saved successfully.', 'cta-manager' ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'ctc_admin_nonce', 'ctc_admin_nonce_field' ); ?>
				<input type="hidden" name="action" value="cta_pro_save_schedule" />

				<div class="cta-form-group cta-business-hours-field">
					<?php
					cta_toggle_switch(
						'schedule_enabled',
						__( 'Show CTAs only during business hours', 'cta-manager' ),
						! empty( $schedule_settings['schedule_enabled'] ),
						'cta-schedule-enabled'
					);
					?>
					<p class="cta-text-muted" style="margin-top: 8px;">
						<?php esc_html_e( 'This is a global business-hours gate. If you prefer per-CTA scheduling, configure schedule rules on each CTA instead.', 'cta-manager' ); ?>
					</p>
				</div>

				<div class="cta-business-hours-meta-row">
					<div class="cta-form-group cta-business-hours-field">
						<label for="cta-schedule-timezone"><?php esc_html_e( 'Timezone', 'cta-manager' ); ?></label>
						<select id="cta-schedule-timezone" name="schedule_timezone" class="cta-select cta-business-hours-timezone">
							<?php
							$current_tz = $schedule_settings['schedule_timezone'] ?? '';
							if ( empty( $current_tz ) || ! in_array( $current_tz, timezone_identifiers_list(), true ) ) {
								$current_tz = wp_timezone_string();
							}
							echo wp_timezone_choice( $current_tz ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</select>
					</div>

					<div class="cta-form-group cta-business-hours-field">
						<?php
						$show_message_when_closed = 'show_message' === ( $schedule_settings['closed_behavior'] ?? 'hide' );
						cta_toggle_switch(
							'show_message_when_closed',
							__( 'Show message when closed', 'cta-manager' ),
							$show_message_when_closed,
							'cta-show-message-when-closed'
						);
						?>
						<input type="hidden" name="closed_behavior" id="cta-closed-behavior" value="<?php echo esc_attr( $show_message_when_closed ? 'show_message' : 'hide' ); ?>" />
						<textarea
							name="closed_message"
							id="cta-closed-message"
							rows="3"
							class="cta-input cta-business-hours-closed-message"
							placeholder="<?php esc_attr_e( 'We are currently closed. Please check back during business hours.', 'cta-manager' ); ?>"
							<?php echo $show_message_when_closed ? '' : 'style="display:none;"'; ?>
						><?php echo esc_textarea( $schedule_settings['closed_message'] ?? '' ); ?></textarea>
					</div>
				</div>

				<div class="cta-form-group cta-business-hours-field">
					<div class="cta-business-hours-schedule-wrap">
						<table class="widefat fixed striped cta-business-hours-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Day', 'cta-manager' ); ?></th>
								<th><?php esc_html_e( 'Open', 'cta-manager' ); ?></th>
								<th><?php esc_html_e( 'Close', 'cta-manager' ); ?></th>
								<th class="cta-business-hours-active-col"><?php esc_html_e( 'Active', 'cta-manager' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $schedule_weekdays as $day ) : $day_conf = $schedule_settings['schedule_hours'][ $day ]; ?>
							<tr>
								<td><?php echo esc_html( ucfirst( $day ) ); ?></td>
								<td><input type="time" name="schedule_hours[<?php echo esc_attr( $day ); ?>][start]" value="<?php echo esc_attr( $day_conf['start'] ); ?>" /></td>
								<td><input type="time" name="schedule_hours[<?php echo esc_attr( $day ); ?>][end]" value="<?php echo esc_attr( $day_conf['end'] ); ?>" /></td>
								<td class="cta-business-hours-active-cell">
									<?php
									cta_toggle_switch(
										'schedule_hours[' . $day . '][enabled]',
										'',
										! empty( $day_conf['enabled'] ),
										'cta-schedule-day-' . $day,
										[
											'size'        => 'small',
											'show_status' => false,
											'extra_class' => 'cta-toggle--day-active',
										]
									);
									?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					</div>
				</div>

				<div style="margin-top: var(--cta-spacing-md);">
					<button type="submit" class="cta-button cta-button-primary">
						<?php esc_html_e( 'Save Business Hours', 'cta-manager' ); ?>
					</button>
				</div>
			</form>

			<script>
			(function() {
				var section = document.getElementById('business-hours');
				if (!section) return;

				var scheduleToggle = section.querySelector('#cta-schedule-enabled');
				var form = section.querySelector('form[action*="admin-post.php"]');
				var warningModal = document.getElementById('cta-business-hours-warning-modal');
				var warningConfirm = document.getElementById('cta-business-hours-warning-confirm');
				var warningCancel = document.getElementById('cta-business-hours-warning-cancel');
				var showMessageToggle = section.querySelector('#cta-show-message-when-closed');
				var closedBehaviorInput = section.querySelector('#cta-closed-behavior');
				var closedMessage = section.querySelector('#cta-closed-message');
				var pendingEnable = false;

				function updateClosedMessageVisibility() {
					if (!showMessageToggle || !closedBehaviorInput || !closedMessage) return;
					var isEnabled = !!showMessageToggle.checked;
					closedBehaviorInput.value = isEnabled ? 'show_message' : 'hide';
					closedMessage.style.display = isEnabled ? '' : 'none';
				}

				if (showMessageToggle) {
					showMessageToggle.addEventListener('change', updateClosedMessageVisibility);
					updateClosedMessageVisibility();
				}

				if (scheduleToggle && warningModal && warningConfirm && warningCancel && window.ctaModalAPI) {
					scheduleToggle.addEventListener('change', function() {
						if (!scheduleToggle.checked) {
							pendingEnable = false;
							return;
						}
						scheduleToggle.checked = false;
						pendingEnable = true;
						window.ctaModalAPI.open(warningModal);
					});

					warningConfirm.addEventListener('click', function() {
						if (pendingEnable && scheduleToggle) {
							scheduleToggle.checked = true;
						}
						pendingEnable = false;
						window.ctaModalAPI.close(warningModal);
					});

					warningCancel.addEventListener('click', function() {
						pendingEnable = false;
						if (scheduleToggle) {
							scheduleToggle.checked = false;
						}
						window.ctaModalAPI.close(warningModal);
					});

					if (form) {
						form.addEventListener('submit', function(e) {
							if (pendingEnable) {
								e.preventDefault();
								window.ctaModalAPI.open(warningModal);
							}
						});
					}
				}
			})();
			</script>

			<style>
			#business-hours .cta-business-hours-field {
				margin-top: 10px;
				margin-bottom: 18px;
			}
			#business-hours .cta-business-hours-meta-row {
				display: flex;
				gap: 16px;
				flex-wrap: wrap;
				align-items: flex-start;
			}
			#business-hours .cta-business-hours-meta-row .cta-form-group {
				flex: 1 1 320px;
				min-width: 260px;
			}
			#business-hours .cta-business-hours-timezone {
				width: 100%;
				max-width: 320px;
			}
			#business-hours .cta-business-hours-closed-message {
				margin-top: 8px;
				width: 100%;
				max-width: 560px;
			}
			#business-hours .cta-business-hours-schedule-wrap {
				max-width: 760px;
				overflow-x: auto;
			}
			#business-hours .cta-business-hours-table {
				width: 100%;
				min-width: 620px;
			}
			#business-hours .cta-business-hours-active-col,
			#business-hours .cta-business-hours-active-cell {
				width: 92px;
				text-align: center;
			}
			#business-hours .cta-toggle--day-active {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				margin: 0 auto;
			}
			#business-hours .cta-toggle--day-active .cta-toggle-label-small {
				display: none;
			}
			@media (max-width: 782px) {
				#business-hours .cta-business-hours-meta-row {
					flex-direction: column;
				}
				#business-hours .cta-business-hours-meta-row .cta-form-group {
					width: 100%;
					min-width: 0;
				}
				#business-hours .cta-business-hours-timezone,
				#business-hours .cta-business-hours-closed-message {
					max-width: 100%;
				}
			}
			</style>

			<div id="cta-business-hours-warning-modal" class="cta-modal cta-modal-sm" style="display: none;">
				<div class="cta-modal-overlay" data-close-modal></div>
				<div class="cta-modal-content" role="dialog" aria-modal="true" aria-labelledby="cta-business-hours-warning-title">
					<div class="cta-modal-header">
						<div class="cta-modal-title-wrap">
							<span class="cta-modal-title-icon dashicons dashicons-warning"></span>
							<h2 id="cta-business-hours-warning-title"><?php esc_html_e( 'Global Business Hours Warning', 'cta-manager' ); ?></h2>
						</div>
						<button type="button" class="cta-modal-close" data-close-modal aria-label="<?php esc_attr_e( 'Close modal', 'cta-manager' ); ?>">
							<span class="dashicons dashicons-no-alt"></span>
						</button>
					</div>
					<div class="cta-modal-body">
						<p><?php esc_html_e( 'Enabling this setting forces ALL CTAs to be hidden during business hours regardless of their individual configurations and will affect all CTAs site-wide.', 'cta-manager' ); ?></p>
						<p><?php esc_html_e( 'Each CTA can be individually set to respect business hours if you prefer to set CTA scheduling individually.', 'cta-manager' ); ?></p>
					</div>
					<div class="cta-modal-footer">
						<div class="cta-modal-footer-buttons">
							<button type="button" id="cta-business-hours-warning-cancel" class="cta-button-secondary" data-close-modal>
								<?php esc_html_e( 'Cancel', 'cta-manager' ); ?>
							</button>
							<button type="button" id="cta-business-hours-warning-confirm" class="cta-button-primary">
								<?php esc_html_e( 'Enable Site-Wide Business Hours', 'cta-manager' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>

		<?php else : ?>
			<?php
			$icon        = 'clock';
			$title       = __( 'Unlock Business Hours Scheduling', 'cta-manager' );
			$description = __( 'Upgrade to Pro to configure business hours and automatically show or hide your CTAs based on your schedule.', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
			unset( $icon, $title, $description );
			?>
		<?php endif; ?>
	</div>

	<?php
	$privacy_page_id = (int) get_option( 'tda_shared_privacy_page_id' );
	$terms_page_id   = (int) get_option( 'tda_shared_terms_page_id' );
	$privacy_url     = $privacy_page_id ? get_permalink( $privacy_page_id ) : 'https://topdevamerica.com/privacy-policy';
	$terms_url       = $terms_page_id ? get_permalink( $terms_page_id ) : 'https://topdevamerica.com/terms';

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

		<!-- Custom Global CSS -->
		<div class="cta-section">
			<?php
			ob_start();
			?>
			<?php esc_html_e( 'Custom Global CSS', 'cta-manager' ); ?>
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
							<?php esc_html_e( 'Save Custom Global CSS', 'cta-manager' ); ?>
						</button>
					</div>
				</div>
			<?php else : ?>
				<?php
				$icon        = 'editor-code';
				$title       = __( 'Unlock Custom Global CSS Styling', 'cta-manager' );
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

		<!-- Support & Privacy -->
		<div class="cta-section">
			<?php
			$title             = __( 'Support & Privacy', 'cta-manager' );
			$help_modal_target = '#cta-docs-modal';
			$help_icon_label   = __( 'View support privacy and legal disclosures', 'cta-manager' );
			$attrs             = 'data-docs-page="category-overview-support"';
			include CTA_PLUGIN_DIR . 'templates/admin/partials/section-header-with-actions.php';
			unset( $title, $help_modal_target, $help_icon_label, $actions_html, $attrs );

			$live_sync_enabled = isset( $settings['support']['live_notifications_enabled'] )
				? (bool) $settings['support']['live_notifications_enabled']
				: $is_pro;
			?>

			<div class="cta-form-group">
				<?php
				cta_toggle_switch(
					'settings[support][live_notifications_enabled]',
					'',
					$live_sync_enabled,
					'cta-live-support-notifications'
				);
				?>
				<p class="cta-text-muted" style="margin-top: 8px;">
					<?php esc_html_e( 'Live support reply notifications are enabled by default for Pro users and disabled by default for Free users. Disable this to receive only local/plugin-update notices.', 'cta-manager' ); ?>
				</p>
			</div>

			<div class="cta-info-box cta-info-box--info">
				<span class="dashicons dashicons-privacy"></span>
				<div>
					<strong><?php esc_html_e( 'Legal Disclosures', 'cta-manager' ); ?></strong>
					<p>
						<?php esc_html_e( 'By using support and license services, you agree to our legal disclosures.', 'cta-manager' ); ?>
						<a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy Policy', 'cta-manager' ); ?></a>
						|
						<a href="<?php echo esc_url( $terms_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Terms of Service', 'cta-manager' ); ?></a>
					</p>
				</div>
			</div>
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
