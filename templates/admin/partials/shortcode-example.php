<?php
/**
 * Admin Partial Template - Shortcode Example
 *
 * Handles markup rendering for the shortcode example admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title        = isset( $title ) ? $title : __( 'Shortcode', 'cta-manager' );
$shortcode    = isset( $shortcode ) ? $shortcode : '[cta-manager]';
$description  = isset( $description ) ? $description : '';
$extra_cls    = isset( $extra_class ) ? $extra_class : '';
?>

<div class="cta-shortcode-example <?php echo esc_attr( $extra_cls ); ?>">
	<h4 class="cta-shortcode-example__title"><?php echo esc_html( $title ); ?></h4>
	<div class="cta-shortcode-box">
		<code class="cta-shortcode-code"><?php echo esc_html( $shortcode ); ?></code>
		<button type="button" class="cta-copy-btn cta-shortcode-copy" data-copy="<?php echo esc_attr( $shortcode ); ?>" title="<?php esc_attr_e( 'Copy to clipboard', 'cta-manager' ); ?>">
			<span class="dashicons dashicons-clipboard"></span>
		</button>
	</div>
	<?php if ( $description ) : ?>
		<p class="cta-help-text cta-shortcode-example__description"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
</div>
