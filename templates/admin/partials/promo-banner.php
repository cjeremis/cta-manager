<?php
/**
 * Admin Partial Template - Promo Banner
 *
 * Handles markup rendering for the promo banner admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $config ) ) {
	return;
}

if ( is_string( $config ) ) {
	$config = json_decode( $config, true );
}

if ( ! is_array( $config ) ) {
	return;
}

$icon_html     = $config['icon_html'] ?? '';
$title         = $config['title'] ?? '';
$description   = $config['description'] ?? '';
$button_text   = $config['button_text'] ?? '';
$button_url    = $config['button_url'] ?? '#';
$button_target = in_array( $config['button_target'] ?? '_self', [ '_self', '_blank' ], true ) ? $config['button_target'] : '_self';
$button_icon   = $config['button_icon'] ?? 'star-filled';
$badge         = $config['badge'] ?? '';

$classes = [ 'cta-promo-banner' ];
if ( ! empty( $config['classes'] ) ) {
	$classes = array_merge( $classes, (array) $config['classes'] );
}

?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $classes ) ) ); ?>">
	<?php if ( $badge ) : ?>
		<span class="cta-promo-badge">
			<span class="dashicons dashicons-star-filled cta-animate-default" aria-hidden="true"></span>
			<?php echo esc_html( $badge ); ?>
		</span>
	<?php endif; ?>

	<?php if ( $icon_html ) : ?>
		<div class="cta-promo-icon" aria-hidden="true">
			<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>

	<div class="cta-promo-content">
		<?php if ( $title ) : ?>
			<div class="cta-promo-title"><?php echo esc_html( $title ); ?></div>
		<?php endif; ?>
		<?php if ( $description ) : ?>
			<p class="cta-promo-desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $button_text ) : ?>
		<div class="cta-promo-actions">
			<?php
			$label       = $button_text;
			$url         = $button_url;
			$variant     = 'primary';
			$extra_class = 'cta-promo-button';
			$icon        = $button_icon;
			$target      = $button_target;
			include __DIR__ . '/pro-upgrade-button.php';
			unset( $label, $url, $variant, $extra_class, $icon, $target );
			?>
		</div>
	<?php endif; ?>
</div>
