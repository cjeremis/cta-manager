<?php
/**
 * Analytics Filters Partial
 *
 * Refactored to use existing partials and responsive layouts
 *
 * @var string $from_date Selected from date
 * @var string $to_date Selected to date
 * @var string $selected_cta Selected CTA ID
 * @var array  $all_ctas Array of CTA objects with id and title
 * @var array  $event_types Array of selected event types
 * @var string $min_date Minimum selectable date (Y-m-d)
 * @var string $max_date Maximum selectable date (Y-m-d)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$from_date    = $from_date ?? '';
$to_date      = $to_date ?? '';
$selected_cta = $selected_cta ?? '';
$all_ctas     = $all_ctas ?? array();
$event_types  = $event_types ?? array( 'impression', 'click' );
$min_date     = $min_date ?? '';
$max_date     = $max_date ?? '';

$date_attrs = '';
if ( ! empty( $min_date ) ) {
	$date_attrs .= ' min="' . esc_attr( $min_date ) . '"';
}
if ( ! empty( $max_date ) ) {
	$date_attrs .= ' max="' . esc_attr( $max_date ) . '"';
}
?>
<div class="cta-analytics-filters-wrapper">
	<!-- Date Range Group -->
	<div class="cta-form-group">
		<label><?php esc_html_e( 'Date Range', 'cta-manager' ); ?></label>
		<div class="cta-date-range-inputs">
			<?php
			$input_id    = 'cta-analytics-from';
			$input_name  = 'from';
			$label       = __( 'FROM', 'cta-manager' );
			$value       = $from_date;
			$extra_attrs = $date_attrs;
			include __DIR__ . '/date-input.php';
			unset( $input_id, $input_name, $label, $value, $extra_attrs );
			?>

			<?php
			$input_id    = 'cta-analytics-to';
			$input_name  = 'to';
			$label       = __( 'TO', 'cta-manager' );
			$value       = $to_date;
			$extra_attrs = $date_attrs;
			include __DIR__ . '/date-input.php';
			unset( $input_id, $input_name, $label, $value, $extra_attrs );
			?>
		</div>
	</div>

	<!-- CTA Filter Group -->
	<div class="cta-form-group">
		<label for="cta-analytics-cta-filter"><?php esc_html_e( 'Filter by CTA', 'cta-manager' ); ?></label>
		<select id="cta-analytics-cta-filter" name="cta_id" class="cta-select">
			<option value=""><?php esc_html_e( 'All CTAs', 'cta-manager' ); ?></option>
			<?php foreach ( $all_ctas as $cta ) : ?>
				<option value="<?php echo esc_attr( $cta->id ); ?>" <?php selected( $selected_cta, $cta->id ); ?>>
					<?php echo esc_html( $cta->title ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<!-- Event Type Group -->
	<div class="cta-form-group">
		<label><?php esc_html_e( 'Event Type', 'cta-manager' ); ?></label>
		<div class="cta-toggle-group">
			<?php
			$input_name   = 'event_types[]';
			$input_value  = 'impression';
			$label        = __( 'Impressions', 'cta-manager' );
			$checked      = in_array( 'impression', $event_types, true );
			$show_status  = false;
			$size         = 'small';
			$extra_class  = '';
			$input_id     = '';
			$input_attrs  = '';
			$wrapper_attrs = '';
			include __DIR__ . '/toggle-switch.php';
			unset( $input_name, $input_value, $label, $checked, $show_status, $size, $extra_class, $input_id, $input_attrs, $wrapper_attrs );
			?>
			<?php
			$input_name   = 'event_types[]';
			$input_value  = 'click';
			$label        = __( 'Clicks', 'cta-manager' );
			$checked      = in_array( 'click', $event_types, true );
			$show_status  = false;
			$size         = 'small';
			$extra_class  = '';
			$input_id     = '';
			$input_attrs  = '';
			$wrapper_attrs = '';
			include __DIR__ . '/toggle-switch.php';
			unset( $input_name, $input_value, $label, $checked, $show_status, $size, $extra_class, $input_id, $input_attrs, $wrapper_attrs );
			?>
		</div>
	</div>

</div>
