<?php
/**
 * Admin Partial Template - Kpi Card
 *
 * Handles markup rendering for the kpi card admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$value_id    = $value_id ?? '';
$extra_class = $extra_class ?? '';
$format      = $format ?? 'number';
?>
<div class="cta-kpi-card <?php echo esc_attr( $extra_class ); ?>">
	<div class="cta-kpi-label"><?php echo esc_html( $label ); ?></div>
	<div class="cta-kpi-value" <?php echo $value_id ? 'id="' . esc_attr( $value_id ) . '"' : ''; ?>>
		<?php echo esc_html( $value ); ?>
	</div>
</div>
