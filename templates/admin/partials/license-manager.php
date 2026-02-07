<?php
/**
 * License Key Management Section Partial
 *
 * Displays a complete license management interface with status badge, input field, and action buttons.
 *
 * Variables:
 * - $license_key (optional) - Current license key value (default: '')
 * - $license_status (optional) - License status: 'active', 'inactive', etc. (default: 'inactive')
 * - $is_license_active (optional) - Whether license is currently active (default: false)
 * - $has_saved_license (optional) - Whether a saved license exists (default: false)
 * - $license_key_min_length (optional) - Minimum license key length (default: 19)
 * - $section_id (optional) - Section ID attribute (default: 'cta-inline-license')
 * - $section_title (optional) - Section heading text (default: 'Pro License')
 * - $input_id (optional) - Input field ID (default: 'cta-pro-license-key')
 * - $input_name (optional) - Input field name (default: 'cta_pro_license_key')
 * - $activate_button_id (optional) - ID for activate button (default: 'cta-license-activate')
 * - $deactivate_button_id (optional) - ID for deactivate button (default: 'cta-license-deactivate')
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Set defaults
$license_key            = $license_key ?? '';
$license_status         = $license_status ?? 'inactive';
$is_license_active      = $is_license_active ?? false;
$has_saved_license      = $has_saved_license ?? ( ! empty( $license_key ) && $is_license_active );
$license_key_min_length = $license_key_min_length ?? 12;
$license_pattern        = $license_pattern ?? '[A-Za-z0-9\-]{12,64}';
$section_id             = $section_id ?? 'cta-inline-license';
$section_title          = $section_title ?? __( 'Pro License', 'cta-manager' );
$input_id               = $input_id ?? 'cta-pro-license-key';
$input_name             = $input_name ?? 'cta_pro_license_key';
$activate_button_id     = $activate_button_id ?? 'cta-license-activate';
$deactivate_button_id   = $deactivate_button_id ?? 'cta-license-deactivate';
?>
<div class="cta-section" id="<?php echo esc_attr( $section_id ); ?>" data-license-status="<?php echo esc_attr( $license_status ); ?>" data-min-length="<?php echo esc_attr( $license_key_min_length ); ?>">
	<h2 class="cta-section-title">
		<?php echo esc_html( $section_title ); ?>
		<?php
		$variant      = $is_license_active ? 'success' : 'inactive';
		$text         = $is_license_active ? __( 'Active', 'cta-manager' ) : __( 'Inactive', 'cta-manager' );
		$extra_styles = 'margin-left: 12px; vertical-align: middle;';
		$extra_attrs  = 'id="cta-license-status-badge"';
		include __DIR__ . '/status-badge.php';
		unset( $variant, $text, $extra_styles, $extra_attrs );
		?>
	</h2>

	<div class="cta-form-group">
		<label for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'License Key', 'cta-manager' ); ?></label>
		<div style="display: flex; gap: var(--cta-spacing-sm); align-items: flex-start;">
			<input
				type="text"
				id="<?php echo esc_attr( $input_id ); ?>"
				name="<?php echo esc_attr( $input_name ); ?>"
				value="<?php echo esc_attr( $has_saved_license ? str_repeat( '*', strlen( $license_key ) ) : $license_key ); ?>"
				placeholder="<?php esc_attr_e( 'Enter or paste your license key', 'cta-manager' ); ?>"
				minlength="12"
				maxlength="64"
				data-min-length="<?php echo esc_attr( $license_key_min_length ); ?>"
				pattern="<?php echo esc_attr( $license_pattern ); ?>"
				<?php echo $has_saved_license ? 'disabled="disabled"' : ''; ?>
				data-actual-key="<?php echo esc_attr( $license_key ); ?>"
				style="flex: 1;"
			/>
			<button type="button" class="cta-button-primary" id="<?php echo esc_attr( $activate_button_id ); ?>" <?php echo $has_saved_license ? 'style="display:none;"' : ''; ?>>
				<?php echo esc_html( $is_license_active ? __( 'Refresh License', 'cta-manager' ) : __( 'Activate License', 'cta-manager' ) ); ?>
			</button>
			<?php if ( $has_saved_license ) : ?>
				<button type="button" class="cta-button-danger" id="cta-license-remove" data-modal-target="#cta-remove-license-modal">
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Remove License', 'cta-manager' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>
</div>
