<?php
/**
 * Admin Partial Template - Tooltip
 *
 * Handles markup rendering for the tooltip admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cta-tooltip" id="<?php echo esc_attr( $tooltip_id ); ?>" style="display: none;"></div>
