<?php
/**
 * Reusable Upgrade Notice Component
 *
 * Displays a Pro upsell with icon, title, body, and button.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon        = isset( $icon ) ? $icon : 'star-filled';
$title       = isset( $title ) ? $title : __( 'Upgrade to Pro', 'cta-manager' );
$message     = isset( $message ) ? $message : '';
$button_url  = isset( $button_url ) ? $button_url : admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
$button_text = isset( $button_text ) ? $button_text : __( 'Upgrade Now', 'cta-manager' );
$extra_class = isset( $extra_class ) ? $extra_class : '';
?>

<?php
$promo_config = [
	'icon_html'   => '<span class="dashicons dashicons-' . esc_attr( $icon ) . '"></span>',
	'title'       => $title,
	'description' => $message,
	'button_text' => $button_text,
	'button_url'  => $button_url,
	'button_icon' => 'star-filled',
	'classes'     => array_filter( [ 'cta-pro-upgrade-notice', $extra_class ] ),
];

include __DIR__ . '/promo-banner.php';
unset( $promo_config );
?>
