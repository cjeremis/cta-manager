<?php
/**
 * Admin Modal Template - Cta Format
 *
 * Handles markup rendering for the cta format admin modal template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cta-format-quick-grid">
	<div class="cta-format-card">
		<label for="cta-format-font-family"><?php esc_html_e( 'Font Family', 'cta-manager' ); ?></label>
		<select id="cta-format-font-family">
			<option value="inherit"><?php esc_html_e( 'Default (Inherit)', 'cta-manager' ); ?></option>
			<option value="Arial, sans-serif">Arial</option>
			<option value="'Helvetica Neue', Helvetica, sans-serif">Helvetica</option>
			<option value="Georgia, serif">Georgia</option>
			<option value="'Times New Roman', Times, serif">Times New Roman</option>
			<option value="'Courier New', Courier, monospace">Courier</option>
			<option value="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif"><?php esc_html_e( 'System Font', 'cta-manager' ); ?></option>
		</select>
	</div>

	<div class="cta-format-card">
		<label><?php esc_html_e( 'Text Alignment', 'cta-manager' ); ?></label>
		<div class="cta-alignment-buttons">
			<button type="button" class="cta-alignment-btn" data-align="left" title="<?php esc_attr_e( 'Left', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-editor-alignleft"></span>
			</button>
			<button type="button" class="cta-alignment-btn" data-align="center" title="<?php esc_attr_e( 'Center', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-editor-aligncenter"></span>
			</button>
			<button type="button" class="cta-alignment-btn" data-align="right" title="<?php esc_attr_e( 'Right', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-editor-alignright"></span>
			</button>
		</div>
	</div>

	<div class="cta-format-card">
		<label for="cta-format-font-size-value"><?php esc_html_e( 'Font Size', 'cta-manager' ); ?></label>
		<div class="cta-size-control">
			<input type="number" id="cta-format-font-size-value" min="8" max="72" value="16">
			<select id="cta-format-font-size-unit">
				<option value="px">px</option>
				<option value="rem">rem</option>
				<option value="em">em</option>
			</select>
		</div>
		<input type="range" id="cta-format-font-size-slider" min="8" max="72" value="16" class="cta-format-slider">
	</div>

	<div class="cta-format-card">
		<label for="cta-format-font-weight"><?php esc_html_e( 'Font Weight', 'cta-manager' ); ?></label>
		<select id="cta-format-font-weight">
			<option value="300"><?php esc_html_e( 'Light', 'cta-manager' ); ?></option>
			<option value="400" selected><?php esc_html_e( 'Normal', 'cta-manager' ); ?></option>
			<option value="500"><?php esc_html_e( 'Medium', 'cta-manager' ); ?></option>
			<option value="600"><?php esc_html_e( 'Semi-Bold', 'cta-manager' ); ?></option>
			<option value="700"><?php esc_html_e( 'Bold', 'cta-manager' ); ?></option>
		</select>
	</div>
</div>

<!-- Text Color picker (advanced with tabs) -->
<div class="cta-format-section cta-format-color-section">
	<div class="cta-section-heading">
		<label><?php esc_html_e( 'Text Color', 'cta-manager' ); ?></label>
	</div>
	<div class="cta-color-controls">
			<div class="cta-color-tabs">
				<button type="button" class="cta-color-tab-btn active" data-tab="hex"><?php esc_html_e( 'Solid', 'cta-manager' ); ?></button>
				<button type="button" class="cta-color-tab-btn" data-tab="rgba"><?php esc_html_e( 'RGBA', 'cta-manager' ); ?></button>
				<button type="button" class="cta-color-tab-btn" data-tab="gradient"><?php esc_html_e( 'Gradient', 'cta-manager' ); ?></button>
			</div>

			<div class="cta-color-tab-wrapper">
				<!-- Hex/Color Wheel Tab -->
				<div class="cta-color-tab-content active" data-tab="hex">
					<label for="cta-format-color"><?php esc_html_e( 'Hex Color', 'cta-manager' ); ?></label>
					<div class="cta-color-input-group">
						<input type="text" id="cta-format-color" class="cta-color-hex-input" value="#1e1e1e" placeholder="#667eea">
						<input type="color" id="cta-color-wheel" value="#1e1e1e" class="cta-native-color-picker">
					</div>
					<div class="cta-color-presets">
						<label><?php esc_html_e( 'Presets:', 'cta-manager' ); ?></label>
						<div class="cta-preset-swatches">
							<button type="button" class="cta-preset-swatch" style="background-color: #1e1e1e;" data-color="#1e1e1e" title="Black"></button>
							<button type="button" class="cta-preset-swatch" style="background-color: #333333;" data-color="#333333" title="Dark Gray"></button>
							<button type="button" class="cta-preset-swatch" style="background-color: #666666;" data-color="#666666" title="Gray"></button>
							<button type="button" class="cta-preset-swatch" style="background-color: #999999;" data-color="#999999" title="Light Gray"></button>
							<button type="button" class="cta-preset-swatch" style="background-color: #ffffff; border: 1px solid #ccc;" data-color="#ffffff" title="White"></button>
							<button type="button" class="cta-preset-swatch" style="background-color: #0073aa;" data-color="#0073aa" title="WordPress Blue"></button>
						</div>
					</div>
				</div>

				<!-- RGBA Tab -->
				<div class="cta-color-tab-content" data-tab="rgba">
					<div class="cta-rgba-inputs">
						<div class="cta-rgba-input-group">
							<label for="cta-rgba-r">R</label>
							<input type="range" id="cta-rgba-r" min="0" max="255" value="30" class="cta-rgba-slider">
							<input type="number" class="cta-rgba-value" min="0" max="255" value="30">
						</div>
						<div class="cta-rgba-input-group">
							<label for="cta-rgba-g">G</label>
							<input type="range" id="cta-rgba-g" min="0" max="255" value="30" class="cta-rgba-slider">
							<input type="number" class="cta-rgba-value" min="0" max="255" value="30">
						</div>
						<div class="cta-rgba-input-group">
							<label for="cta-rgba-b">B</label>
							<input type="range" id="cta-rgba-b" min="0" max="255" value="30" class="cta-rgba-slider">
							<input type="number" class="cta-rgba-value" min="0" max="255" value="30">
						</div>
						<div class="cta-rgba-input-group">
							<label for="cta-rgba-a">A</label>
							<input type="range" id="cta-rgba-a" min="0" max="100" value="100" class="cta-rgba-slider">
							<input type="number" class="cta-rgba-value" min="0" max="100" value="100">
						</div>
					</div>
					<div class="cta-rgba-preview" style="background-color: rgba(30, 30, 30, 1);"></div>
				</div>

				<!-- Gradient Tab -->
				<div class="cta-color-tab-content" data-tab="gradient">
					<div class="cta-gradient-controls">
						<div class="cta-gradient-type-angle-row">
							<div class="cta-gradient-type-group">
								<label for="cta-gradient-type"><?php esc_html_e( 'Gradient Type', 'cta-manager' ); ?></label>
								<select id="cta-gradient-type">
									<option value="linear"><?php esc_html_e( 'Linear', 'cta-manager' ); ?></option>
									<option value="radial"><?php esc_html_e( 'Radial', 'cta-manager' ); ?></option>
								</select>
							</div>

							<div class="cta-gradient-angle-group">
								<label for="cta-gradient-angle"><?php esc_html_e( 'Angle', 'cta-manager' ); ?></label>
								<div class="cta-angle-control">
									<input type="range" id="cta-gradient-angle" min="0" max="360" value="90" class="cta-format-slider">
									<span class="cta-angle-value">90°</span>
								</div>
							</div>
						</div>

						<label><?php esc_html_e( 'Color Stops', 'cta-manager' ); ?></label>
						<div class="cta-gradient-stops">
							<div class="cta-gradient-stop">
								<input type="color" class="cta-stop-color" value="#667eea">
								<input type="range" min="0" max="100" value="0" class="cta-stop-position">
								<span class="cta-stop-label">0%</span>
							</div>
							<div class="cta-gradient-stop">
								<input type="color" class="cta-stop-color" value="#764ba2">
								<input type="range" min="0" max="100" value="100" class="cta-stop-position">
								<span class="cta-stop-label">100%</span>
							</div>
						</div>
					</div>
					<div class="cta-gradient-preview" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>
				</div>
			</div>
		</div>
	</div>
</div>
