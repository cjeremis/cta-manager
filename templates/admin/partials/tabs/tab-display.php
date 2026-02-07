<?php
/**
 * CTA Form - Display Tab
 *
 * Contains: Layout, Visibility
 *
 * @package CTA_Manager
 * @since 1.0.0
 *
 * Expected variables:
 * @var array|null $editing_cta Current CTA data
 * @var bool $is_pro Whether Pro is enabled
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Display Settings Section -->
<div class="cta-form-section cta-no-mt">
	<div class="cta-form-row">
		<div class="cta-form-group">
			<label for="cta-layout">
				<?php esc_html_e( 'Layout', 'cta-manager' ); ?>
			</label>
			<?php
			// Base layout options (free version)
			$layout_options = [
				[
					'value' => 'button',
					'label' => __( 'Button only', 'cta-manager' ),
				],
			];

			// Allow pro plugin to extend layout options
			$layout_options = apply_filters( 'cta_layout_options', $layout_options, $editing_cta );

			$selected_layout = $editing_cta['layout'] ?? 'button';
			?>
			<select id="cta-layout" name="cta_layout" class="cta-select" required>
				<?php foreach ( $layout_options as $option ) : ?>
					<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $selected_layout, $option['value'] ); ?>>
						<?php echo esc_html( $option['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="cta-form-group">
			<label for="cta-visibility">
				<?php esc_html_e( 'Visibility', 'cta-manager' ); ?>
			</label>
			<?php
			// Base visibility options (free version)
			$visibility_options = [
				[
					'value' => 'all_devices',
					'label' => __( 'All Devices', 'cta-manager' ),
				],
			];

			// Allow Pro plugin to extend with additional options
			$visibility_options = apply_filters( 'cta_visibility_options', $visibility_options, $editing_cta );

			$current_value = $editing_cta['visibility'] ?? 'all_devices';
			?>
			<select id="cta-visibility" name="visibility" class="cta-form-control">
				<?php foreach ( $visibility_options as $option ) : ?>
					<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $current_value, $option['value'] ); ?>>
						<?php echo esc_html( $option['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<?php if ( ! $is_pro ) : ?>
		<!-- Pro Layout & Visibility Upsell -->
		<div class="cta-form-row cta-full-width" style="margin-top: 20px;">
			<?php
			$icon        = 'layout';
			$title       = __( 'Unlock Advanced Layouts & Targeting', 'cta-manager' );
			$description = __( 'Access card layouts with customizable positioning, device targeting, URL-based display rules, and schedule-based controls.', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
			unset( $icon, $title, $description );
			?>
		</div>
	<?php endif; ?>
</div>
