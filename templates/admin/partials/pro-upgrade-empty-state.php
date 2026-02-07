<?php
/**
 * Pro Upgrade Empty State Component
 *
 * Reusable empty state that promotes Pro features with intelligent
 * status-aware messaging based on plugin/license status.
 *
 * Parameters:
 * - $icon        (string)  Dashicon name for the feature (default: 'star-filled')
 * - $title       (string)  Feature title (e.g., 'Advanced Event Logging')
 * - $description (string)  Feature description
 * - $extra_class (string)  Additional CSS classes
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Default parameters
$icon        = isset( $icon ) ? $icon : 'star-filled';
$title       = isset( $title ) ? $title : __( 'Pro Feature', 'cta-manager' );
$description = isset( $description ) ? $description : '';
$extra_class = isset( $extra_class ) ? $extra_class : '';

// Check Pro plugin status
$is_pro_enabled = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

// If Pro is fully enabled, don't show this component
if ( $is_pro_enabled ) {
	return;
}

// Check if Pro plugin file exists (installed but maybe not active)
$pro_plugin_file  = 'cta-manager-pro/cta-manager-pro.php';
$pro_plugin_path  = WP_PLUGIN_DIR . '/' . $pro_plugin_file;
$is_pro_installed = file_exists( $pro_plugin_path );

// Check if Pro plugin is active
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$is_pro_active = is_plugin_active( $pro_plugin_file );

// Determine the status and button configuration
if ( ! $is_pro_installed ) {
	// Pro is not installed
	$status_class = 'cta-pro-status-not-installed';
	$button_label = __( 'Upgrade to Pro', 'cta-manager' );
	$button_icon  = 'star-filled';
	$button_url   = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
	$extra_attrs  = '';
} elseif ( ! $is_pro_active ) {
	// Pro is installed but not activated
	$status_class = 'cta-pro-status-inactive';
	$button_label = __( 'Activate Pro', 'cta-manager' );
	$button_icon  = 'admin-plugins';
	$button_url   = admin_url( 'plugins.php' );
	$extra_attrs  = '';
} else {
	// Pro is active - check if license key is missing
	$has_api_key = ! empty( CTA_Pro_Feature_Gate::get_license_key() );

	if ( ! $has_api_key ) {
		// Pro is active but license key is missing
		$status_class = 'cta-pro-status-no-api-key';
		$button_label = __( 'Add Pro License Key', 'cta-manager' );
		$button_icon  = 'unlock';
		$button_url   = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
		$extra_attrs  = 'data-scroll-to="cta-pro-license-key" data-focus-field="cta_pro_license_key"';
	} else {
		// Pro is active but not licensed (license key exists but validation failed)
		$status_class = 'cta-pro-status-unlicensed';
		$button_label = __( 'Verify License', 'cta-manager' );
		$button_icon  = 'admin-network';
		$button_url   = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
		$extra_attrs  = 'data-scroll-to="cta-pro-license-key"';
	}
}

$wrapper_classes = array_filter( [
	'cta-empty-state',
	'cta-pro-upgrade-empty-state',
	$status_class,
	$extra_class,
] );
?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
	<div class="cta-empty-state-icon cta-pro-upgrade-empty-state__icon">
		<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
	</div>
	<div class="cta-empty-state-content">
		<h3 class="cta-empty-state-title">
			<?php echo esc_html( $title ); ?>
			<?php cta_pro_badge_inline(); ?>
		</h3>
		<?php if ( $description ) : ?>
			<p class="cta-empty-state-desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	$label       = $button_label;
	$url         = $button_url;
	$variant     = 'primary';
	$icon        = $button_icon;
	$extra_class = 'cta-pro-upgrade-empty-state__button';
	include __DIR__ . '/pro-upgrade-button.php';
	unset( $label, $url, $variant, $icon, $extra_class, $extra_attrs );
	?>
</div>
