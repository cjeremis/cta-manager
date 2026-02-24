<?php
/**
 * Admin Partial Template - Form Section Header
 *
 * Handles markup rendering for the form section header admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_pro_badge = $show_pro_badge ?? false;
?>
<h4 class="cta-form-section-title">
	<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
	<?php echo esc_html( $title ); ?>
	<?php if ( $show_pro_badge ) : ?>
		<?php cta_pro_badge_inline(); ?>
	<?php endif; ?>
</h4>
