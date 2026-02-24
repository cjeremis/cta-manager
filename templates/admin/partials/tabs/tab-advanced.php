<?php
/**
 * Admin Tab Partial Template - Tab Advanced
 *
 * Handles markup rendering for the tab advanced admin tab partial.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Variable initialization (from lines 1089-1097 of backup)
$data_attributes = $editing_cta['data_attributes'] ?? [];
if ( empty( $data_attributes ) || ! is_array( $data_attributes ) ) {
	$data_attributes = [ [ 'key' => '', 'value' => '' ] ];
}
?>

<!-- Advanced Section -->
<div class="cta-form-section cta-no-mt">
<div class="cta-form-row">
	<div class="cta-form-group">
		<label for="cta-wrapper-id">
			<?php esc_html_e( 'Wrapper ID', 'cta-manager' ); ?>
			<?php if ( ! $is_pro ) : ?>
				<span style="margin-left: 8px;"><?php cta_pro_badge_inline(); ?></span>
			<?php endif; ?>
		</label>
		<input
			type="text"
			id="cta-wrapper-id"
			name="wrapper_id"
			value="<?php echo esc_attr( $editing_cta['wrapper_id'] ?? '' ); ?>"
			placeholder="<?php esc_attr_e( 'wrapper-id', 'cta-manager' ); ?>"
			<?php disabled( ! $is_pro ); ?>
		/>
	</div>

	<div class="cta-form-group">
		<label for="cta-wrapper-classes">
			<?php esc_html_e( 'Wrapper Classes', 'cta-manager' ); ?>
			<?php if ( ! $is_pro ) : ?>
				<span style="margin-left: 8px;"><?php cta_pro_badge_inline(); ?></span>
			<?php endif; ?>
		</label>
		<input
			type="text"
			id="cta-wrapper-classes"
			name="wrapper_classes"
			value="<?php echo esc_attr( $editing_cta['wrapper_classes'] ?? '' ); ?>"
			placeholder="<?php esc_attr_e( 'class-one, class-two', 'cta-manager' ); ?>"
			<?php disabled( ! $is_pro ); ?>
		/>
	</div>
</div>

<div class="cta-form-row">
	<div class="cta-form-group">
		<label for="cta-cta-html-id">
			<?php esc_html_e( 'CTA ID', 'cta-manager' ); ?>
			<?php if ( ! $is_pro ) : ?>
				<span style="margin-left: 8px;"><?php cta_pro_badge_inline(); ?></span>
			<?php endif; ?>
		</label>
		<input
			type="text"
			id="cta-cta-html-id"
			name="cta_html_id"
			value="<?php echo esc_attr( $editing_cta['cta_html_id'] ?? '' ); ?>"
			placeholder="<?php esc_attr_e( 'cta-id', 'cta-manager' ); ?>"
			<?php disabled( ! $is_pro ); ?>
		/>
	</div>

	<div class="cta-form-group">
		<label for="cta-cta-classes">
			<?php esc_html_e( 'CTA Classes', 'cta-manager' ); ?>
			<?php if ( ! $is_pro ) : ?>
				<span style="margin-left: 8px;"><?php cta_pro_badge_inline(); ?></span>
			<?php endif; ?>
		</label>
		<input
			type="text"
			id="cta-cta-classes"
			name="cta_classes"
			value="<?php echo esc_attr( $editing_cta['cta_classes'] ?? '' ); ?>"
			placeholder="<?php esc_attr_e( 'class-one, class-two', 'cta-manager' ); ?>"
			<?php disabled( ! $is_pro ); ?>
		/>
	</div>
</div>

<div class="cta-form-row cta-full-width">
	<div class="cta-form-group">
		<label>
			<?php esc_html_e( 'Data Attributes', 'cta-manager' ); ?>
			<?php if ( ! $is_pro ) : ?>
				<span style="margin-left: 8px;"><?php cta_pro_badge_inline(); ?></span>
			<?php endif; ?>
		</label>
		<div class="cta-data-attributes" id="cta-data-attributes" data-is-pro="<?php echo $is_pro ? '1' : '0'; ?>">
			<?php foreach ( $data_attributes as $attribute ) : ?>
				<div class="cta-data-attribute-row">
					<input
						type="text"
						name="cta_data_attribute_key[]"
						value="<?php echo esc_attr( $attribute['key'] ?? '' ); ?>"
						placeholder="<?php esc_attr_e( 'data-attr', 'cta-manager' ); ?>"
						<?php disabled( ! $is_pro ); ?>
					/>
					<input
						type="text"
						name="cta_data_attribute_value[]"
						value="<?php echo esc_attr( $attribute['value'] ?? '' ); ?>"
						placeholder="<?php esc_attr_e( 'Value', 'cta-manager' ); ?>"
						<?php disabled( ! $is_pro ); ?>
					/>
					<button type="button" class="cta-button-link cta-remove-attribute" <?php disabled( ! $is_pro ); ?>>
						<?php esc_html_e( 'Remove', 'cta-manager' ); ?>
					</button>
				</div>
			<?php endforeach; ?>
		</div>
		<button type="button" class="cta-button-secondary cta-add-attribute" <?php disabled( ! $is_pro ); ?>>
			<?php esc_html_e( 'Add Attribute', 'cta-manager' ); ?>
		</button>
	</div>
</div>

	<?php if ( ! empty( $pro_numbers ) ) : ?>
	<div class="cta-form-row">
		<div class="cta-form-group">
			<label for="pro-number-select">
				<?php esc_html_e( 'Pro Numbers', 'cta-manager' ); ?>
		</label>
		<select id="pro-number-select" class="regular-text">
			<option value=""><?php esc_html_e( 'Select a saved number', 'cta-manager' ); ?></option>
			<?php foreach ( $pro_numbers as $num ) : ?>
				<option value="<?php echo esc_attr( $num['phone_number'] ); ?>">
					<?php echo esc_html( $num['label'] ); ?>
				</option>
			<?php endforeach; ?>
			</select>
		</div>
	</div>
	<?php endif; ?>
</div>
