<?php
/**
 * Admin Partial Template - Pro Toggle Row
 *
 * Handles markup rendering for the pro toggle row admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id                  = isset( $id ) ? $id : '';
$name                = isset( $name ) ? $name : '';
$checked             = isset( $checked ) ? (bool) $checked : false;
$label               = isset( $label ) ? $label : '';
$is_pro              = isset( $is_pro ) ? (bool) $is_pro : false;
$disabled            = isset( $disabled ) ? (bool) $disabled : false;
$show_badge          = isset( $show_badge ) ? (bool) $show_badge : true;
$status_on           = isset( $status_on ) ? $status_on : __( 'On', 'cta-manager' );
$status_off          = isset( $status_off ) ? $status_off : __( 'Off', 'cta-manager' );
$extra_wrapper_class = isset( $extra_wrapper_class ) ? $extra_wrapper_class : '';

$wrapper_classes = trim( 'cta-toggle ' . $extra_wrapper_class . ( $disabled ? ' cta-toggle-disabled' : '' ) );
?>

<label class="<?php echo esc_attr( $wrapper_classes ); ?>">
	<input
		type="checkbox"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo esc_attr( $id ); ?>"
		value="1"
		<?php checked( $checked, true ); ?>
		<?php disabled( $disabled ); ?>
	/>
	<span class="cta-toggle-track" aria-hidden="true">
		<span class="cta-toggle-thumb"></span>
	</span>
	<span class="cta-toggle-status" data-on="<?php echo esc_attr( $status_on ); ?>" data-off="<?php echo esc_attr( $status_off ); ?>"></span>
	<span class="cta-toggle-label">
		<?php echo esc_html( $label ); ?>
		<?php if ( $show_badge ) : ?>
			<span style="margin-left: 8px;">
				<?php cta_pro_badge_inline(); ?>
			</span>
		<?php endif; ?>
	</span>
</label>
