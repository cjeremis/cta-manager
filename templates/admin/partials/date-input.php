<?php
/**
 * Date Input Partial
 *
 * Displays a date input field with label.
 *
 * Variables:
 * - $input_id (required) - Input ID attribute
 * - $input_name (required) - Input name attribute
 * - $label (required) - Label text
 * - $value (optional) - Input value (default: '')
 * - $extra_attrs (optional) - Additional HTML attributes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$value       = $value ?? '';
$extra_attrs = $extra_attrs ?? '';
?>
<div class="cta-date-input">
	<input type="date"
	       id="<?php echo esc_attr( $input_id ); ?>"
	       name="<?php echo esc_attr( $input_name ); ?>"
	       value="<?php echo esc_attr( $value ); ?>"
	       <?php echo $extra_attrs; ?> />
	<span class="cta-date-label"><?php echo esc_html( $label ); ?></span>
</div>
