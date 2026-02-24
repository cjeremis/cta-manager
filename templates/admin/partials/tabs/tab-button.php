<?php
/**
 * Admin Tab Partial Template - Tab Button
 *
 * Handles markup rendering for the tab button admin tab partial.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Variable initialization (from lines 433-436 of backup)
$slide_in_settings = $editing_cta['slide_in_settings'] ?? [];
$current_button_animation = $editing_cta['button_animation'] ?? 'none';
$current_icon_animation   = $editing_cta['icon_animation'] ?? 'none';
?>

<!-- Button Section -->
<div class="cta-form-section cta-no-mt">
<div class="cta-form-row">
	<div class="cta-form-group">
		<div class="cta-label-with-icon">
			<label for="button-text">
				<?php esc_html_e( 'Button Text', 'cta-manager' ); ?>
			</label>
			<button type="button" class="cta-format-icon-btn" data-field="button_text" title="<?php esc_attr_e( 'Format button text', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-admin-customizer"></span>
			</button>
		</div>
		<input
			type="text"
			id="button-text"
			name="button_text"
			value="<?php echo esc_attr( $editing_cta['button_text'] ?? 'Call Now' ); ?>"
			placeholder="<?php esc_attr_e( 'Call Now', 'cta-manager' ); ?>"
			maxlength="50"
		/>
		<!-- Hidden inputs for button text formatting -->
		<input type="hidden" id="button_text_font_size" name="button_text_font_size" value="<?php echo esc_attr( $editing_cta['button_text_font_size'] ?? '16px' ); ?>" />
		<input type="hidden" id="button_text_font_family" name="button_text_font_family" value="<?php echo esc_attr( $editing_cta['button_text_font_family'] ?? 'inherit' ); ?>" />
		<input type="hidden" id="button_text_font_weight" name="button_text_font_weight" value="<?php echo esc_attr( $editing_cta['button_text_font_weight'] ?? '600' ); ?>" />
		<input type="hidden" id="button_text_color" name="button_text_color" value="<?php echo esc_attr( $editing_cta['button_text_color'] ?? '#ffffff' ); ?>" />
		<input type="hidden" id="button_text_alignment" name="button_text_alignment" value="<?php echo esc_attr( $editing_cta['button_text_alignment'] ?? 'center' ); ?>" />
		<input type="hidden" id="button_alignment" name="button_alignment" value="<?php echo esc_attr( $editing_cta['button_alignment'] ?? 'center' ); ?>" />
	</div>
	<div class="cta-form-group">
		<label for="button_width"><?php esc_html_e( 'Button Width', 'cta-manager' ); ?></label>
		<?php $button_width = $editing_cta['button_width'] ?? 'auto'; ?>
		<select id="button_width" name="button_width" class="cta-select">
			<option value="auto" <?php selected( $button_width, 'auto' ); ?>><?php esc_html_e( 'Auto (fit content)', 'cta-manager' ); ?></option>
			<option value="full" <?php selected( $button_width, 'full' ); ?>><?php esc_html_e( 'Full Width', 'cta-manager' ); ?></option>
		</select>
	</div>
</div>

<div class="cta-form-row">
	<div class="cta-form-group cta-button-alignment-group">
		<label><?php esc_html_e( 'Button Alignment', 'cta-manager' ); ?></label>
		<div class="cta-button-alignment-buttons" role="group" aria-label="<?php esc_attr_e( 'Button alignment', 'cta-manager' ); ?>">
			<?php $button_alignment = $editing_cta['button_alignment'] ?? 'center'; ?>
			<button type="button" class="cta-button-alignment-btn <?php echo 'left' === $button_alignment ? 'active' : ''; ?>" data-align="left" title="<?php esc_attr_e( 'Align Left', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-editor-alignleft"></span>
			</button>
			<button type="button" class="cta-button-alignment-btn <?php echo 'center' === $button_alignment ? 'active' : ''; ?>" data-align="center" title="<?php esc_attr_e( 'Align Center', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-editor-aligncenter"></span>
			</button>
			<button type="button" class="cta-button-alignment-btn <?php echo 'right' === $button_alignment ? 'active' : ''; ?>" data-align="right" title="<?php esc_attr_e( 'Align Right', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-editor-alignright"></span>
			</button>
		</div>
	</div>

	<?php if ( $is_pro ) : ?>
	<div class="cta-form-group" id="cta-button-icon-group">
		<label for="cta-icon">
			<?php esc_html_e( 'Button Icon', 'cta-manager' ); ?>
		</label>
		<?php
		$current_icon = $editing_cta['icon'] ?? 'none';

		// Base icon options (free version)
		$base_icon_options = [
			[
				'value' => 'none',
				'label' => __( 'None', 'cta-manager' ),
			],
		];

		// Allow pro plugin to extend icon options
		$icon_options = apply_filters( 'cta_button_icon_options', $base_icon_options, $editing_cta );
		?>
		<select id="cta-icon" name="cta_icon" class="cta-select">
			<?php foreach ( $icon_options as $option ) : ?>
				<?php if ( isset( $option['options'] ) ) : ?>
					<!-- Optgroup -->
					<optgroup label="<?php echo esc_attr( $option['label'] ); ?>">
						<?php foreach ( $option['options'] as $sub_option ) : ?>
							<option
								value="<?php echo esc_attr( $sub_option['value'] ); ?>"
								<?php selected( $current_icon, $sub_option['value'] ); ?>
								<?php if ( isset( $sub_option['data'] ) ) : ?>
									<?php foreach ( $sub_option['data'] as $data_key => $data_value ) : ?>
										data-<?php echo esc_attr( $data_key ); ?>="<?php echo esc_attr( $data_value ); ?>"
									<?php endforeach; ?>
								<?php endif; ?>
							>
								<?php echo esc_html( $sub_option['label'] ); ?>
							</option>
						<?php endforeach; ?>
					</optgroup>
				<?php else : ?>
					<!-- Regular option -->
					<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $current_icon, $option['value'] ); ?>>
						<?php echo esc_html( $option['label'] ); ?>
					</option>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</div>
	<?php endif; ?>
</div>

<hr class="cta-section-separator" />

<!-- Button Styling Section -->
<div class="cta-form-row">
	<div class="cta-form-group">
		<label for="cta-background-type">
			<?php esc_html_e( 'Background Type', 'cta-manager' ); ?>
		</label>
		<select id="cta-background-type" name="cta_background_type" class="cta-select">
			<option value="solid" <?php selected( $editing_cta['background_type'] ?? 'solid', 'solid' ); ?>>
				<?php esc_html_e( 'Solid Color', 'cta-manager' ); ?>
			</option>
			<option value="gradient" <?php selected( $editing_cta['background_type'] ?? 'solid', 'gradient' ); ?>>
				<?php esc_html_e( 'Gradient', 'cta-manager' ); ?>
			</option>
			<option value="transparent" <?php selected( $editing_cta['background_type'] ?? 'solid', 'transparent' ); ?>>
				<?php esc_html_e( 'Transparent', 'cta-manager' ); ?>
			</option>
		</select>
	</div>
	<div class="cta-form-group cta-bg-controls-column">
		<!-- Solid Color Controls (inline when solid is selected) -->
		<div class="cta-bg-solid-controls" style="<?php echo ( $editing_cta['background_type'] ?? 'solid' ) === 'solid' ? '' : 'display: none;'; ?>">
			<label for="cta-color" class="cta-label-sm-margin">
				<?php esc_html_e( 'Background Color', 'cta-manager' ); ?>
			</label>
			<input
				type="color"
				id="cta-color"
				name="cta_color"
				value="<?php echo esc_attr( $editing_cta['color'] ?? '#667eea' ); ?>"
			/>
		</div>
		<!-- Gradient Controls (inline when gradient is selected) -->
		<div class="cta-bg-gradient-controls" style="<?php echo ( $editing_cta['background_type'] ?? 'solid' ) === 'gradient' ? '' : 'display: none;'; ?>">
			<label><?php esc_html_e( 'Gradient Type', 'cta-manager' ); ?></label>
			<select id="cta-gradient-type" name="cta_gradient_type" class="cta-select">
				<option value="linear" <?php selected( $editing_cta['gradient_type'] ?? 'linear', 'linear' ); ?>>
					<?php esc_html_e( 'Linear', 'cta-manager' ); ?>
				</option>
				<option value="radial" <?php selected( $editing_cta['gradient_type'] ?? 'linear', 'radial' ); ?>>
					<?php esc_html_e( 'Radial', 'cta-manager' ); ?>
				</option>
			</select>
		</div>
	</div>
</div>

<!-- Gradient Details Row (shown when gradient is selected) -->
<div class="cta-gradient-details-row" style="<?php echo ( $editing_cta['background_type'] ?? 'solid' ) === 'gradient' ? '' : 'display: none;'; ?>">
	<div class="cta-gradient-controls-left">
		<div class="cta-gradient-angle-group" style="<?php echo ( $editing_cta['gradient_type'] ?? 'linear' ) === 'linear' ? '' : 'display: none;'; ?>">
			<label for="cta-gradient-angle"><?php esc_html_e( 'Angle', 'cta-manager' ); ?></label>
			<div class="cta-angle-control">
				<input
					type="range"
					id="cta-gradient-angle"
					name="cta_gradient_angle"
					min="0"
					max="360"
					value="<?php echo esc_attr( $editing_cta['gradient_angle'] ?? '90' ); ?>"
					class="cta-format-slider"
				/>
				<span class="cta-angle-value"><?php echo esc_html( $editing_cta['gradient_angle'] ?? '90' ); ?>°</span>
			</div>
		</div>
		<label><?php esc_html_e( 'Color Stops', 'cta-manager' ); ?></label>
		<div class="cta-gradient-stops">
			<div class="cta-gradient-stop">
				<input
					type="color"
					id="cta-gradient-start"
					name="cta_gradient_start"
					class="cta-stop-color"
					value="<?php echo esc_attr( $editing_cta['gradient_start'] ?? '#667eea' ); ?>"
				/>
				<input
					type="range"
					id="cta-gradient-start-position"
					name="cta_gradient_start_position"
					class="cta-stop-position"
					min="0"
					max="100"
					value="<?php echo esc_attr( $editing_cta['gradient_start_position'] ?? '0' ); ?>"
				/>
				<span class="cta-stop-label"><?php echo esc_html( $editing_cta['gradient_start_position'] ?? '0' ); ?>%</span>
			</div>
			<div class="cta-gradient-stop">
				<input
					type="color"
					id="cta-gradient-end"
					name="cta_gradient_end"
					class="cta-stop-color"
					value="<?php echo esc_attr( $editing_cta['gradient_end'] ?? '#764ba2' ); ?>"
				/>
				<input
					type="range"
					id="cta-gradient-end-position"
					name="cta_gradient_end_position"
					class="cta-stop-position"
					min="0"
					max="100"
					value="<?php echo esc_attr( $editing_cta['gradient_end_position'] ?? '100' ); ?>"
				/>
				<span class="cta-stop-label"><?php echo esc_html( $editing_cta['gradient_end_position'] ?? '100' ); ?>%</span>
			</div>
		</div>
	</div>
	<div class="cta-gradient-preview" style="background: linear-gradient(<?php echo esc_attr( $editing_cta['gradient_angle'] ?? '90' ); ?>deg, <?php echo esc_attr( $editing_cta['gradient_start'] ?? '#667eea' ); ?> <?php echo esc_attr( $editing_cta['gradient_start_position'] ?? '0' ); ?>%, <?php echo esc_attr( $editing_cta['gradient_end'] ?? '#764ba2' ); ?> <?php echo esc_attr( $editing_cta['gradient_end_position'] ?? '100' ); ?>%);"></div>
</div>

<!-- Close the row that was left open -->
<div class="cta-form-row" style="display: none;">
	</div>
</div>

<hr class="cta-section-separator" />

<div class="cta-form-row">
	<div class="cta-form-group">
		<label for="cta-border-style">
			<?php esc_html_e( 'Border Style', 'cta-manager' ); ?>
		</label>
		<select id="cta-border-style" name="cta_border_style" class="cta-select">
			<option value="none" <?php selected( $editing_cta['border_style'] ?? 'none', 'none' ); ?>>
				<?php esc_html_e( 'None', 'cta-manager' ); ?>
			</option>
			<option value="solid" <?php selected( $editing_cta['border_style'] ?? 'none', 'solid' ); ?>>
				<?php esc_html_e( 'Solid', 'cta-manager' ); ?>
			</option>
			<option value="dotted" <?php selected( $editing_cta['border_style'] ?? 'none', 'dotted' ); ?>>
				<?php esc_html_e( 'Dotted', 'cta-manager' ); ?>
			</option>
			<option value="dashed" <?php selected( $editing_cta['border_style'] ?? 'none', 'dashed' ); ?>>
				<?php esc_html_e( 'Dashed', 'cta-manager' ); ?>
			</option>
		</select>
	</div>
	<div class="cta-form-group cta-border-controls" style="<?php echo ( $editing_cta['border_style'] ?? 'none' ) !== 'none' ? '' : 'display: none;'; ?>">
		<label for="cta-border-color" class="cta-label-sm-margin">
			<?php esc_html_e( 'Border Color', 'cta-manager' ); ?>
		</label>
		<input
			type="color"
			id="cta-border-color"
			name="cta_border_color"
			value="<?php echo esc_attr( $editing_cta['border_color'] ?? '#667eea' ); ?>"
		/>
	</div>
</div>

<!-- Border Width Controls (shown when border style is not 'none') -->
<div class="cta-border-width-controls cta-border-controls" style="<?php echo ( $editing_cta['border_style'] ?? 'none' ) !== 'none' ? '' : 'display: none;'; ?>">
	<div class="cta-subsection-label-row">
		<label class="cta-subsection-label">
			<?php esc_html_e( 'Border Width', 'cta-manager' ); ?>
		</label>
		<button type="button" class="cta-border-width-link-btn" id="cta-border-width-link" title="<?php esc_attr_e( 'Link border width values', 'cta-manager' ); ?>">
			<span class="dashicons dashicons-admin-links"></span>
		</button>
	</div>
	<div class="cta-form-row cta-border-width-grid">
		<?php
		// Parse border width values to extract number and unit
		$border_width_top = $editing_cta['border_width_top'] ?? '2px';
		$border_width_right = $editing_cta['border_width_right'] ?? '2px';
		$border_width_bottom = $editing_cta['border_width_bottom'] ?? '2px';
		$border_width_left = $editing_cta['border_width_left'] ?? '2px';

		// Extract number and unit for each border width value
		preg_match('/^(\d+\.?\d*)(.*)$/', $border_width_top, $bw_top_parts);
		preg_match('/^(\d+\.?\d*)(.*)$/', $border_width_right, $bw_right_parts);
		preg_match('/^(\d+\.?\d*)(.*)$/', $border_width_bottom, $bw_bottom_parts);
		preg_match('/^(\d+\.?\d*)(.*)$/', $border_width_left, $bw_left_parts);

		$bw_top_value = $bw_top_parts[1] ?? '2';
		$bw_top_unit = $bw_top_parts[2] ?? 'px';
		$bw_right_value = $bw_right_parts[1] ?? '2';
		$bw_right_unit = $bw_right_parts[2] ?? 'px';
		$bw_bottom_value = $bw_bottom_parts[1] ?? '2';
		$bw_bottom_unit = $bw_bottom_parts[2] ?? 'px';
		$bw_left_value = $bw_left_parts[1] ?? '2';
		$bw_left_unit = $bw_left_parts[2] ?? 'px';
		?>

		<!-- Top Border Width -->
		<div class="cta-form-group cta-border-width-input-group">
			<label for="cta-border-width-top-value">
				<?php esc_html_e( 'Top', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-border-width-top-value"
					name="cta_border_width_top_value"
					class="cta-border-width-value"
					data-side="top"
					min="0"
					max="20"
					value="<?php echo esc_attr( $bw_top_value ); ?>"
				/>
				<select id="cta-border-width-top-unit" name="cta_border_width_top_unit" class="cta-border-width-unit" data-side="top">
					<option value="px" <?php selected( $bw_top_unit, 'px' ); ?>>px</option>
					<option value="rem" <?php selected( $bw_top_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $bw_top_unit, 'em' ); ?>>em</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-border-width-top-slider"
				class="cta-border-width-slider"
				data-side="top"
				min="0"
				max="20"
				value="<?php echo esc_attr( $bw_top_value ); ?>"
			/>
			<input type="hidden" id="cta-border-width-top" name="cta_border_width_top" value="<?php echo esc_attr( $border_width_top ); ?>" />
		</div>

		<!-- Right Border Width -->
		<div class="cta-form-group cta-border-width-input-group">
			<label for="cta-border-width-right-value">
				<?php esc_html_e( 'Right', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-border-width-right-value"
					name="cta_border_width_right_value"
					class="cta-border-width-value"
					data-side="right"
					min="0"
					max="20"
					value="<?php echo esc_attr( $bw_right_value ); ?>"
				/>
				<select id="cta-border-width-right-unit" name="cta_border_width_right_unit" class="cta-border-width-unit" data-side="right">
					<option value="px" <?php selected( $bw_right_unit, 'px' ); ?>>px</option>
					<option value="rem" <?php selected( $bw_right_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $bw_right_unit, 'em' ); ?>>em</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-border-width-right-slider"
				class="cta-border-width-slider"
				data-side="right"
				min="0"
				max="20"
				value="<?php echo esc_attr( $bw_right_value ); ?>"
			/>
			<input type="hidden" id="cta-border-width-right" name="cta_border_width_right" value="<?php echo esc_attr( $border_width_right ); ?>" />
		</div>

		<!-- Bottom Border Width -->
		<div class="cta-form-group cta-border-width-input-group">
			<label for="cta-border-width-bottom-value">
				<?php esc_html_e( 'Bottom', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-border-width-bottom-value"
					name="cta_border_width_bottom_value"
					class="cta-border-width-value"
					data-side="bottom"
					min="0"
					max="20"
					value="<?php echo esc_attr( $bw_bottom_value ); ?>"
				/>
				<select id="cta-border-width-bottom-unit" name="cta_border_width_bottom_unit" class="cta-border-width-unit" data-side="bottom">
					<option value="px" <?php selected( $bw_bottom_unit, 'px' ); ?>>px</option>
					<option value="rem" <?php selected( $bw_bottom_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $bw_bottom_unit, 'em' ); ?>>em</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-border-width-bottom-slider"
				class="cta-border-width-slider"
				data-side="bottom"
				min="0"
				max="20"
				value="<?php echo esc_attr( $bw_bottom_value ); ?>"
			/>
			<input type="hidden" id="cta-border-width-bottom" name="cta_border_width_bottom" value="<?php echo esc_attr( $border_width_bottom ); ?>" />
		</div>

		<!-- Left Border Width -->
		<div class="cta-form-group cta-border-width-input-group">
			<label for="cta-border-width-left-value">
				<?php esc_html_e( 'Left', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-border-width-left-value"
					name="cta_border_width_left_value"
					class="cta-border-width-value"
					data-side="left"
					min="0"
					max="20"
					value="<?php echo esc_attr( $bw_left_value ); ?>"
				/>
				<select id="cta-border-width-left-unit" name="cta_border_width_left_unit" class="cta-border-width-unit" data-side="left">
					<option value="px" <?php selected( $bw_left_unit, 'px' ); ?>>px</option>
					<option value="rem" <?php selected( $bw_left_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $bw_left_unit, 'em' ); ?>>em</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-border-width-left-slider"
				class="cta-border-width-slider"
				data-side="left"
				min="0"
				max="20"
				value="<?php echo esc_attr( $bw_left_value ); ?>"
			/>
			<input type="hidden" id="cta-border-width-left" name="cta_border_width_left" value="<?php echo esc_attr( $border_width_left ); ?>" />
		</div>
	</div>
	<!-- Hidden field to store link state -->
	<input type="hidden" id="cta-border-width-linked" name="cta_border_width_linked" value="<?php echo esc_attr( $editing_cta['border_width_linked'] ?? '1' ); ?>" />
</div>

<!-- Border Radius Controls (shown when border style is not 'none') -->
<div class="cta-border-radius-controls cta-border-controls" style="<?php echo ( $editing_cta['border_style'] ?? 'none' ) !== 'none' ? '' : 'display: none;'; ?>">
	<div class="cta-subsection-label-row">
		<label class="cta-subsection-label">
			<?php esc_html_e( 'Border Radius', 'cta-manager' ); ?>
		</label>
		<button type="button" class="cta-border-radius-link-btn" id="cta-border-radius-link" title="<?php esc_attr_e( 'Link border radius values', 'cta-manager' ); ?>">
			<span class="dashicons dashicons-admin-links"></span>
		</button>
	</div>
	<div class="cta-form-row cta-border-radius-grid">
		<?php
		// Parse border radius values to extract number and unit
		$border_radius_top_left = $editing_cta['border_radius_top_left'] ?? '8px';
		$border_radius_top_right = $editing_cta['border_radius_top_right'] ?? '8px';
		$border_radius_bottom_right = $editing_cta['border_radius_bottom_right'] ?? '8px';
		$border_radius_bottom_left = $editing_cta['border_radius_bottom_left'] ?? '8px';

		// Extract number and unit for each border radius value
		preg_match('/^(\d+\.?\d*)(.*)$/', $border_radius_top_left, $br_tl_parts);
		preg_match('/^(\d+\.?\d*)(.*)$/', $border_radius_top_right, $br_tr_parts);
		preg_match('/^(\d+\.?\d*)(.*)$/', $border_radius_bottom_right, $br_br_parts);
		preg_match('/^(\d+\.?\d*)(.*)$/', $border_radius_bottom_left, $br_bl_parts);

		$br_tl_value = $br_tl_parts[1] ?? '8';
		$br_tl_unit = $br_tl_parts[2] ?? 'px';
		$br_tr_value = $br_tr_parts[1] ?? '8';
		$br_tr_unit = $br_tr_parts[2] ?? 'px';
		$br_br_value = $br_br_parts[1] ?? '8';
		$br_br_unit = $br_br_parts[2] ?? 'px';
		$br_bl_value = $br_bl_parts[1] ?? '8';
		$br_bl_unit = $br_bl_parts[2] ?? 'px';
		?>

		<!-- Top Left Border Radius -->
		<div class="cta-form-group cta-border-radius-input-group">
			<label for="cta-border-radius-top-left-value">
				<?php esc_html_e( 'Top Left', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-border-radius-top-left-value"
					name="cta_border_radius_top_left_value"
					class="cta-border-radius-value"
					data-corner="top-left"
					min="0"
					max="100"
					value="<?php echo esc_attr( $br_tl_value ); ?>"
				/>
				<select id="cta-border-radius-top-left-unit" name="cta_border_radius_top_left_unit" class="cta-border-radius-unit" data-corner="top-left">
					<option value="px" <?php selected( $br_tl_unit, 'px' ); ?>>px</option>
					<option value="%" <?php selected( $br_tl_unit, '%' ); ?>>%</option>
					<option value="rem" <?php selected( $br_tl_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $br_tl_unit, 'em' ); ?>>em</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-border-radius-top-left-slider"
				class="cta-border-radius-slider"
				data-corner="top-left"
				min="0"
				max="100"
				value="<?php echo esc_attr( $br_tl_value ); ?>"
			/>
			<input type="hidden" id="cta-border-radius-top-left" name="cta_border_radius_top_left" value="<?php echo esc_attr( $border_radius_top_left ); ?>" />
		</div>

		<!-- Top Right Border Radius -->
		<div class="cta-form-group cta-border-radius-input-group">
			<label for="cta-border-radius-top-right-value">
				<?php esc_html_e( 'Top Right', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-border-radius-top-right-value"
					name="cta_border_radius_top_right_value"
					class="cta-border-radius-value"
					data-corner="top-right"
					min="0"
					max="100"
					value="<?php echo esc_attr( $br_tr_value ); ?>"
				/>
				<select id="cta-border-radius-top-right-unit" name="cta_border_radius_top_right_unit" class="cta-border-radius-unit" data-corner="top-right">
					<option value="px" <?php selected( $br_tr_unit, 'px' ); ?>>px</option>
					<option value="%" <?php selected( $br_tr_unit, '%' ); ?>>%</option>
					<option value="rem" <?php selected( $br_tr_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $br_tr_unit, 'em' ); ?>>em</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-border-radius-top-right-slider"
				class="cta-border-radius-slider"
				data-corner="top-right"
				min="0"
				max="100"
				value="<?php echo esc_attr( $br_tr_value ); ?>"
			/>
			<input type="hidden" id="cta-border-radius-top-right" name="cta_border_radius_top_right" value="<?php echo esc_attr( $border_radius_top_right ); ?>" />
		</div>

		<!-- Bottom Right Border Radius -->
		<div class="cta-form-group cta-border-radius-input-group">
			<label for="cta-border-radius-bottom-right-value">
				<?php esc_html_e( 'Bottom Right', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-border-radius-bottom-right-value"
					name="cta_border_radius_bottom_right_value"
					class="cta-border-radius-value"
					data-corner="bottom-right"
					min="0"
					max="100"
					value="<?php echo esc_attr( $br_br_value ); ?>"
				/>
				<select id="cta-border-radius-bottom-right-unit" name="cta_border_radius_bottom_right_unit" class="cta-border-radius-unit" data-corner="bottom-right">
					<option value="px" <?php selected( $br_br_unit, 'px' ); ?>>px</option>
					<option value="%" <?php selected( $br_br_unit, '%' ); ?>>%</option>
					<option value="rem" <?php selected( $br_br_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $br_br_unit, 'em' ); ?>>em</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-border-radius-bottom-right-slider"
				class="cta-border-radius-slider"
				data-corner="bottom-right"
				min="0"
				max="100"
				value="<?php echo esc_attr( $br_br_value ); ?>"
			/>
			<input type="hidden" id="cta-border-radius-bottom-right" name="cta_border_radius_bottom_right" value="<?php echo esc_attr( $border_radius_bottom_right ); ?>" />
		</div>

		<!-- Bottom Left Border Radius -->
		<div class="cta-form-group cta-border-radius-input-group">
			<label for="cta-border-radius-bottom-left-value">
				<?php esc_html_e( 'Bottom Left', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-border-radius-bottom-left-value"
					name="cta_border_radius_bottom_left_value"
					class="cta-border-radius-value"
					data-corner="bottom-left"
					min="0"
					max="100"
					value="<?php echo esc_attr( $br_bl_value ); ?>"
				/>
				<select id="cta-border-radius-bottom-left-unit" name="cta_border_radius_bottom_left_unit" class="cta-border-radius-unit" data-corner="bottom-left">
					<option value="px" <?php selected( $br_bl_unit, 'px' ); ?>>px</option>
					<option value="%" <?php selected( $br_bl_unit, '%' ); ?>>%</option>
					<option value="rem" <?php selected( $br_bl_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $br_bl_unit, 'em' ); ?>>em</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-border-radius-bottom-left-slider"
				class="cta-border-radius-slider"
				data-corner="bottom-left"
				min="0"
				max="100"
				value="<?php echo esc_attr( $br_bl_value ); ?>"
			/>
			<input type="hidden" id="cta-border-radius-bottom-left" name="cta_border_radius_bottom_left" value="<?php echo esc_attr( $border_radius_bottom_left ); ?>" />
		</div>
	</div>
	<!-- Hidden field to store link state -->
	<input type="hidden" id="cta-border-radius-linked" name="cta_border_radius_linked" value="<?php echo esc_attr( $editing_cta['border_radius_linked'] ?? '1' ); ?>" />
</div>

<hr class="cta-section-separator" />

<!-- Button Padding Controls -->
<div class="cta-padding-controls">
	<div class="cta-subsection-label-row">
		<label class="cta-subsection-label">
			<?php esc_html_e( 'Button Padding', 'cta-manager' ); ?>
		</label>
		<button type="button" class="cta-padding-link-btn" id="cta-padding-link" title="<?php esc_attr_e( 'Link padding values', 'cta-manager' ); ?>">
			<span class="dashicons dashicons-admin-links"></span>
		</button>
	</div>
	<div class="cta-form-row cta-padding-grid-with-link">
		<?php
		// Parse padding values to extract number and unit
		$padding_top = $editing_cta['padding_top'] ?? '12px';
		$padding_right = $editing_cta['padding_right'] ?? '24px';
		$padding_bottom = $editing_cta['padding_bottom'] ?? '12px';
		$padding_left = $editing_cta['padding_left'] ?? '24px';

		// Extract number and unit for each padding value
		preg_match('/^(\d+\.?\d*)(.*)$/', $padding_top, $top_parts);
		preg_match('/^(\d+\.?\d*)(.*)$/', $padding_right, $right_parts);
		preg_match('/^(\d+\.?\d*)(.*)$/', $padding_bottom, $bottom_parts);
		preg_match('/^(\d+\.?\d*)(.*)$/', $padding_left, $left_parts);

		$top_value = $top_parts[1] ?? '12';
		$top_unit = $top_parts[2] ?? 'px';
		$right_value = $right_parts[1] ?? '24';
		$right_unit = $right_parts[2] ?? 'px';
		$bottom_value = $bottom_parts[1] ?? '12';
		$bottom_unit = $bottom_parts[2] ?? 'px';
		$left_value = $left_parts[1] ?? '24';
		$left_unit = $left_parts[2] ?? 'px';
		?>

		<!-- Top Padding -->
		<div class="cta-form-group cta-padding-input-group">
			<label for="cta-padding-top-value">
				<?php esc_html_e( 'Top', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-padding-top-value"
					name="cta_padding_top_value"
					class="cta-padding-value"
					data-side="top"
					min="0"
					max="100"
					value="<?php echo esc_attr( $top_value ); ?>"
				/>
				<select id="cta-padding-top-unit" name="cta_padding_top_unit" class="cta-padding-unit" data-side="top">
					<option value="px" <?php selected( $top_unit, 'px' ); ?>>px</option>
					<option value="%" <?php selected( $top_unit, '%' ); ?>>%</option>
					<option value="rem" <?php selected( $top_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $top_unit, 'em' ); ?>>em</option>
					<option value="vh" <?php selected( $top_unit, 'vh' ); ?>>vh</option>
					<option value="vw" <?php selected( $top_unit, 'vw' ); ?>>vw</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-padding-top-slider"
				class="cta-padding-slider"
				data-side="top"
				min="0"
				max="100"
				value="<?php echo esc_attr( $top_value ); ?>"
			/>
			<input type="hidden" id="cta-padding-top" name="cta_padding_top" class="cta-padding-input" value="<?php echo esc_attr( $padding_top ); ?>" />
		</div>

		<!-- Right Padding -->
		<div class="cta-form-group cta-padding-input-group">
			<label for="cta-padding-right-value">
				<?php esc_html_e( 'Right', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-padding-right-value"
					name="cta_padding_right_value"
					class="cta-padding-value"
					data-side="right"
					min="0"
					max="100"
					value="<?php echo esc_attr( $right_value ); ?>"
				/>
				<select id="cta-padding-right-unit" name="cta_padding_right_unit" class="cta-padding-unit" data-side="right">
					<option value="px" <?php selected( $right_unit, 'px' ); ?>>px</option>
					<option value="%" <?php selected( $right_unit, '%' ); ?>>%</option>
					<option value="rem" <?php selected( $right_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $right_unit, 'em' ); ?>>em</option>
					<option value="vh" <?php selected( $right_unit, 'vh' ); ?>>vh</option>
					<option value="vw" <?php selected( $right_unit, 'vw' ); ?>>vw</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-padding-right-slider"
				class="cta-padding-slider"
				data-side="right"
				min="0"
				max="100"
				value="<?php echo esc_attr( $right_value ); ?>"
			/>
			<input type="hidden" id="cta-padding-right" name="cta_padding_right" class="cta-padding-input" value="<?php echo esc_attr( $padding_right ); ?>" />
		</div>

		<!-- Bottom Padding -->
		<div class="cta-form-group cta-padding-input-group">
			<label for="cta-padding-bottom-value">
				<?php esc_html_e( 'Bottom', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-padding-bottom-value"
					name="cta_padding_bottom_value"
					class="cta-padding-value"
					data-side="bottom"
					min="0"
					max="100"
					value="<?php echo esc_attr( $bottom_value ); ?>"
				/>
				<select id="cta-padding-bottom-unit" name="cta_padding_bottom_unit" class="cta-padding-unit" data-side="bottom">
					<option value="px" <?php selected( $bottom_unit, 'px' ); ?>>px</option>
					<option value="%" <?php selected( $bottom_unit, '%' ); ?>>%</option>
					<option value="rem" <?php selected( $bottom_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $bottom_unit, 'em' ); ?>>em</option>
					<option value="vh" <?php selected( $bottom_unit, 'vh' ); ?>>vh</option>
					<option value="vw" <?php selected( $bottom_unit, 'vw' ); ?>>vw</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-padding-bottom-slider"
				class="cta-padding-slider"
				data-side="bottom"
				min="0"
				max="100"
				value="<?php echo esc_attr( $bottom_value ); ?>"
			/>
			<input type="hidden" id="cta-padding-bottom" name="cta_padding_bottom" class="cta-padding-input" value="<?php echo esc_attr( $padding_bottom ); ?>" />
		</div>

		<!-- Left Padding -->
		<div class="cta-form-group cta-padding-input-group">
			<label for="cta-padding-left-value">
				<?php esc_html_e( 'Left', 'cta-manager' ); ?>
			</label>
			<div class="cta-size-control">
				<input
					type="number"
					id="cta-padding-left-value"
					name="cta_padding_left_value"
					class="cta-padding-value"
					data-side="left"
					min="0"
					max="100"
					value="<?php echo esc_attr( $left_value ); ?>"
				/>
				<select id="cta-padding-left-unit" name="cta_padding_left_unit" class="cta-padding-unit" data-side="left">
					<option value="px" <?php selected( $left_unit, 'px' ); ?>>px</option>
					<option value="%" <?php selected( $left_unit, '%' ); ?>>%</option>
					<option value="rem" <?php selected( $left_unit, 'rem' ); ?>>rem</option>
					<option value="em" <?php selected( $left_unit, 'em' ); ?>>em</option>
					<option value="vh" <?php selected( $left_unit, 'vh' ); ?>>vh</option>
					<option value="vw" <?php selected( $left_unit, 'vw' ); ?>>vw</option>
				</select>
			</div>
			<input
				type="range"
				id="cta-padding-left-slider"
				class="cta-padding-slider"
				data-side="left"
				min="0"
				max="100"
				value="<?php echo esc_attr( $left_value ); ?>"
			/>
			<input type="hidden" id="cta-padding-left" name="cta_padding_left" class="cta-padding-input" value="<?php echo esc_attr( $padding_left ); ?>" />
		</div>
	</div>
	<!-- Hidden field to store link state -->
	<input type="hidden" id="cta-padding-linked" name="cta_padding_linked" value="<?php echo esc_attr( $editing_cta['padding_linked'] ?? '0' ); ?>" />
</div>

<?php if ( $is_pro ) : ?>
<!-- Button and Icon Animations Row -->
<div class="cta-form-row">

	<div class="cta-form-group">
		<label for="button-animation">
			<?php esc_html_e( 'Button Animation', 'cta-manager' ); ?>
		</label>
		<?php
		// Base button animation options (free version)
		$button_animation_options = [
			[
				'value' => 'none',
				'label' => __( 'None', 'cta-manager' ),
			],
		];

		// Allow pro plugin to extend button animation options
		$button_animation_options = apply_filters( 'cta_button_animation_options', $button_animation_options, $editing_cta );

		$selected_button_animation = $current_button_animation;
		?>
		<select id="button-animation" name="button_animation" class="cta-select">
			<?php foreach ( $button_animation_options as $option ) : ?>
				<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $selected_button_animation, $option['value'] ); ?>>
					<?php echo esc_html( $option['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="cta-form-group">
		<label for="icon-animation">
			<?php esc_html_e( 'Icon Animation', 'cta-manager' ); ?>
		</label>
		<?php
		// Base icon animation options (free version)
		$icon_animation_options = [
			[
				'value' => 'none',
				'label' => __( 'None', 'cta-manager' ),
			],
		];

		// Allow pro plugin to extend icon animation options
		$icon_animation_options = apply_filters( 'cta_icon_animation_options', $icon_animation_options, $editing_cta );

		$selected_icon_animation = $current_icon_animation;
		?>
		<select id="icon-animation" name="icon_animation" class="cta-select">
			<?php foreach ( $icon_animation_options as $option ) : ?>
				<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $selected_icon_animation, $option['value'] ); ?>>
					<?php echo esc_html( $option['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
<?php endif; ?>

	<?php if ( ! $is_pro ) : ?>
		<!-- Pro Icons & Animations Upsell -->
		<div class="cta-form-row cta-full-width" style="margin-top: 20px;">
			<?php
			$icon        = 'admin-appearance';
			$title       = __( 'Unlock Premium Icons & Animations', 'cta-manager' );
			$description = __( 'Upgrade to Pro to access 10+ built-in icons, custom icon uploads, and eye-catching animations.', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
			unset( $icon, $title, $description );
			?>
		</div>
	<?php endif; ?>
</div>
