<?php
/**
 * Tooltip Partial
 *
 * Displays an empty tooltip container for JavaScript-populated tooltips.
 *
 * Variables:
 * - $tooltip_id (required) - Unique ID for the tooltip element
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cta-tooltip" id="<?php echo esc_attr( $tooltip_id ); ?>" style="display: none;"></div>
