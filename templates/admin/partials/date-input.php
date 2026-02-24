<?php
/**
 * Admin Partial Template - Date Input
 *
 * Handles markup rendering for the date input admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
