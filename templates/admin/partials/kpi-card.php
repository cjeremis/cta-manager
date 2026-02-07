<?php
/**
 * KPI Metric Card Partial
 *
 * Displays a KPI metric card with label and value.
 *
 * Variables:
 * - $label (required) - KPI label text
 * - $value (required) - KPI value to display
 * - $value_id (optional) - ID for value element (for JS updates)
 * - $extra_class (optional) - Additional CSS classes
 * - $format (optional) - Value format: 'number', 'percentage', 'currency' (default: 'number')
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
