<?php
/**
 * Radio Group Partial
 *
 * Displays a group of radio button options with labels and descriptions.
 *
 * Variables:
 * - $name (required) - Input name attribute (shared by all options)
 * - $options (required) - Array of option arrays with keys: 'value', 'label', 'description'
 *   Optional keys: 'class' (input class string), 'attributes' (extra raw attributes)
 * - $selected_value (optional) - Currently selected value
 * - $extra_class (optional) - Additional CSS classes for the group
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$selected_value = $selected_value ?? '';
$extra_class    = $extra_class ?? '';
?>
<div class="cta-radio-group <?php echo esc_attr( $extra_class ); ?>">
	<?php foreach ( $options as $option ) : ?>
		<?php
		$input_class = $option['class'] ?? '';
		$input_attrs = $option['attributes'] ?? '';
		?>
		<label class="cta-radio-label">
			<input type="radio"
			       name="<?php echo esc_attr( $name ); ?>"
			       value="<?php echo esc_attr( $option['value'] ); ?>"
			       <?php checked( $selected_value, $option['value'] ); ?>
			       <?php echo $input_class ? 'class="' . esc_attr( $input_class ) . '"' : ''; ?>
			       <?php echo $input_attrs; ?> />
			<span class="cta-radio-text">
				<strong><?php echo esc_html( $option['label'] ); ?></strong>
				<?php if ( ! empty( $option['description'] ) ) : ?>
					<span class="cta-radio-description"><?php echo esc_html( $option['description'] ); ?></span>
				<?php endif; ?>
			</span>
		</label>
	<?php endforeach; ?>
</div>
