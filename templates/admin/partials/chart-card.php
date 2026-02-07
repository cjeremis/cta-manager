<?php
/**
 * Chart Card Partial
 *
 * Renders a chart container with canvas and legend.
 *
 * Variables:
 * - $title (required) - Chart title
 * - $canvas_id (required) - Canvas element ID for Chart.js
 * - $legend_id (required) - Legend element ID
 * - $full_width (optional) - Whether chart should be full width (default: false)
 * - $is_donut (optional) - Whether this is a donut chart (default: false)
 * - $card_id (optional) - Card container ID
 * - $canvas_height (optional) - Canvas height attribute (default: '120')
 * - $chart_type (optional) - Chart type for additional classes
 * - $extra_class (optional) - Additional CSS classes
 * - $show_empty_state (optional) - Whether to show empty state immediately (default: false)
 * - $empty_icon (optional) - Dashicon name for empty state (default: 'chart-bar')
 * - $empty_title (optional) - Title for empty state
 * - $empty_description (optional) - Description for empty state
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$full_width        = $full_width ?? false;
$is_donut          = $is_donut ?? false;
$card_id           = $card_id ?? '';
$canvas_height     = $canvas_height ?? '120';
$chart_type        = $chart_type ?? '';
$extra_class       = $extra_class ?? '';
$show_empty_state  = $show_empty_state ?? false;
$empty_icon        = $empty_icon ?? 'chart-bar';
$empty_title       = $empty_title ?? __( 'No Data Available', 'cta-manager' );
$empty_description = $empty_description ?? __( 'Data will appear here once analytics are collected for the selected period.', 'cta-manager' );

$card_classes = 'cta-chart-card';
if ( $full_width ) {
	$card_classes .= ' cta-chart-full-width';
}
if ( $extra_class ) {
	$card_classes .= ' ' . $extra_class;
}

// Determine visibility based on empty state
$chart_display = $show_empty_state ? 'display: none;' : '';
$empty_display = $show_empty_state ? '' : 'display: none;';
?>

<div class="<?php echo esc_attr( $card_classes ); ?>" <?php echo $card_id ? 'id="' . esc_attr( $card_id ) . '"' : ''; ?>>
	<h3 class="cta-chart-title"><?php echo esc_html( $title ); ?></h3>
	<div class="cta-chart-container <?php echo $is_donut ? 'cta-chart-donut' : ''; ?>" style="<?php echo esc_attr( $chart_display ); ?>">
		<canvas id="<?php echo esc_attr( $canvas_id ); ?>" height="<?php echo esc_attr( $canvas_height ); ?>"></canvas>
	</div>
	<div class="cta-chart-empty-state" style="<?php echo esc_attr( $empty_display ); ?>">
		<?php
		$icon        = $empty_icon;
		$title       = $empty_title;
		$description = $empty_description;
		include CTA_PLUGIN_DIR . 'templates/admin/partials/empty-state.php';
		unset( $icon, $title, $description );
		?>
	</div>
	<div class="cta-chart-legend" id="<?php echo esc_attr( $legend_id ); ?>" style="<?php echo esc_attr( $chart_display ); ?>"></div>
</div>
