<?php
/**
 * Pro-gated select partial
 *
 * Renders a select element that marks Pro-only options with a badge and error message.
 *
 * Expected variables:
 * - $select_id (required)     : Select element ID
 * - $select_name (optional)   : Select element name (defaults to $select_id)
 * - $options (required)       : Array of option/optgroup configs
 * - $selected_value (optional): Currently selected value
 * - $error_message (optional) : Error message to show for Pro-only selections
 * - $error_id (optional)      : ID for the error element (defaults to {$select_id}-pro-error)
 * - $badge_id (optional)      : ID for the badge element (defaults to {$select_id}-pro-badge)
 * - $is_pro (optional)        : Whether the current user has Pro (default: false)
 * - $show_badge (optional)    : Whether to render the badge (default: true)
 * - $select_class (optional)  : CSS classes for the select (default: 'cta-select')
 * - $select_attrs (optional)  : Extra raw attributes for the select
 * - $wrapper_class (optional) : Extra wrapper classes
 *
 * @package CTA_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$select_id      = $select_id ?? '';
$select_name    = ! empty( $select_name ) ? $select_name : $select_id;
$options        = $options ?? [];
$selected_value = $selected_value ?? '';
$is_pro         = ! empty( $is_pro );
$show_badge     = isset( $show_badge ) ? (bool) $show_badge : true;
$select_class   = $select_class ?? 'cta-select';
$select_attrs   = $select_attrs ?? '';
$wrapper_class  = $wrapper_class ?? '';
$badge_id       = ! empty( $badge_id ) ? $badge_id : ( $select_id ? $select_id . '-pro-badge' : '' );
$error_id       = ! empty( $error_id ) ? $error_id : ( $select_id ? $select_id . '-pro-error' : '' );
$error_message  = $error_message ?? '';

$render_options = function ( array $option_items ) use ( &$render_options, $selected_value, $is_pro ) {
	foreach ( $option_items as $option ) {
		// Optgroup handling.
		if ( isset( $option['options'] ) && is_array( $option['options'] ) ) {
			$group_label = $option['label'] ?? '';
			echo '<optgroup label="' . esc_attr( $group_label ) . '">';
			$render_options( $option['options'] );
			echo '</optgroup>';
			continue;
		}

		$value         = $option['value'] ?? '';
		$label         = $option['label'] ?? $value;
		$requires_pro  = ! empty( $option['requires_pro'] );
		$data_attrs    = '';
		$option_attrs  = $requires_pro ? ' data-requires-pro="1"' : ' data-requires-pro="0"';
		$extra_attrs   = $option['attrs'] ?? '';

		// Add (Free)/(Pro) suffix and disabled state for non-Pro users.
		if ( ! $is_pro ) {
			$suffix       = $requires_pro ? ' (Pro)' : ' (Free)';
			$label       .= $suffix;
			$option_attrs .= $requires_pro ? ' disabled' : '';
		}

		if ( ! empty( $option['data'] ) && is_array( $option['data'] ) ) {
			foreach ( $option['data'] as $data_key => $data_value ) {
				$data_attrs .= ' data-' . esc_attr( $data_key ) . '="' . esc_attr( $data_value ) . '"';
			}
		}

		printf(
			'<option value="%1$s"%2$s%3$s%4$s>%5$s</option>',
			esc_attr( $value ),
			selected( $selected_value, $value, false ),
			$option_attrs,
			$data_attrs . ( $extra_attrs ? ' ' . trim( $extra_attrs ) : '' ),
			esc_html( $label )
		);
	}
};
?>
<div class="cta-pro-gated-select <?php echo esc_attr( $wrapper_class ); ?>">
	<select
		id="<?php echo esc_attr( $select_id ); ?>"
		name="<?php echo esc_attr( $select_name ); ?>"
		class="<?php echo esc_attr( $select_class ); ?>"
		data-is-pro="<?php echo $is_pro ? '1' : '0'; ?>"
		<?php echo $select_attrs ? ' ' . trim( $select_attrs ) : ''; ?>
	>
		<?php $render_options( $options ); ?>
	</select>

	<?php if ( ( $show_badge && $badge_id ) || ( $error_message && $error_id ) ) : ?>
		<?php
		$coming_soon_url = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
		include __DIR__ . '/pro-gated-message.php';
		unset( $coming_soon_url );
		?>
	<?php endif; ?>
</div>
