<?php
/**
 * Admin Partial Template - Status Badge
 *
 * Handles markup rendering for the status badge admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pulse_class  = $pulse_class ?? '';
$extra_styles = $extra_styles ?? '';
$extra_attrs  = $extra_attrs ?? '';
$cta_list_card_badge  = $cta_list_card_badge ?? false;
?>

<span class="cta-badge cta-badge-<?php echo esc_attr( $variant ); ?><?php echo $pulse_class ? ' ' . esc_attr( $pulse_class ) : ''; ?>" <?php echo $extra_styles ? 'style="' . esc_attr( $extra_styles ) . '"' : ''; ?> <?php echo $extra_attrs ? $extra_attrs : ''; ?>>
	
	<?php if ($cta_list_card_badge): ?>
		<span class="cta-badge-overlay" style="display: none;">
			<span class="dashicons dashicons-edit"></span>
		</span>
	<?php 
		$cta_list_card_badge = false;
		endif;
	?>

	<?php echo esc_html( $text ); ?>

</span>
