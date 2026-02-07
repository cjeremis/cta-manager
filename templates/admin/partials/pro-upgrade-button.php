<?php
/**
 * Pro Upgrade Button Partial
 *
 * Drop-in upgrade button component that stays consistent across admin pages.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label        = isset( $label ) ? $label : __( 'Upgrade to Pro', 'cta-manager' );
$url          = isset( $url ) ? $url : admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
$variant      = isset( $variant ) ? $variant : 'primary'; // primary|ghost|block|small
$extra_class  = isset( $extra_class ) ? $extra_class : '';
$icon         = isset( $icon ) ? $icon : 'star-filled';
$icon_class   = isset( $icon_class ) ? $icon_class : ( 'star-filled' === $icon ? 'cta-animate-default' : '' );
$target       = isset( $target ) && in_array( $target, [ '_self', '_blank' ], true ) ? $target : '_self';
$rel          = isset( $rel ) ? $rel : ( '_blank' === $target ? 'noopener noreferrer' : 'nofollow' );
$extra_attrs  = isset( $extra_attrs ) ? $extra_attrs : '';

$classes = [
	'cta-button',
	'cta-pro-upgrade-button',
	'cta-pro-upgrade-button--' . $variant,
	trim( $extra_class ),
];

if ( 'primary' === $variant ) {
	$classes[] = 'cta-button-primary';
} elseif ( 'ghost' === $variant ) {
	$classes[] = 'cta-button-secondary';
} elseif ( 'block' === $variant ) {
	$classes[] = 'cta-button-primary';
	$classes[] = 'cta-button-block';
} elseif ( 'small' === $variant ) {
	$classes[] = 'cta-button-small';
}
?>

<a
	href="<?php echo esc_url( $url ); ?>"
	class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>"
	target="<?php echo esc_attr( $target ); ?>"
	rel="<?php echo esc_attr( $rel ); ?>"
	<?php echo $extra_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
	<?php if ( $icon ) : ?>
		<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?><?php echo $icon_class ? ' ' . esc_attr( $icon_class ) : ''; ?>"></span>
	<?php endif; ?>
	<span class="cta-pro-upgrade-button__label"><?php echo esc_html( $label ); ?></span>
</a>
