<?php
/**
 * Admin Tab Partial Template - Tab Display
 *
 * Handles markup rendering for the tab display admin tab partial.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
			$stored_layout = $editing_cta['layout'] ?? 'button';
			$card_layout_options = [
				'card-left'   => __( 'Text Left', 'cta-manager' ),
				'card-right'  => __( 'Text Right', 'cta-manager' ),
				'card-top'    => __( 'Text Above', 'cta-manager' ),
				'card-bottom' => __( 'Text Below', 'cta-manager' ),
			];
			$is_card_layout = isset( $card_layout_options[ $stored_layout ] );
			$selected_layout = $is_card_layout ? 'card' : 'button';
			$selected_card_layout = $is_card_layout ? $stored_layout : 'card-left';
			?>
			<select id="cta-layout" name="cta_layout_ui" class="cta-select" data-is-pro="<?php echo $is_pro ? '1' : '0'; ?>" required>
				<option value="button" <?php selected( $selected_layout, 'button' ); ?>>
					<?php esc_html_e( 'Button', 'cta-manager' ); ?>
				</option>
				<option value="card" data-requires-pro="1" <?php selected( $selected_layout, 'card' ); ?>>
					<?php esc_html_e( 'Card', 'cta-manager' ); ?>
				</option>
			</select>
			<input type="hidden" id="cta-layout-value" name="cta_layout" value="<?php echo esc_attr( $stored_layout ); ?>" />
		</div>

		<div class="cta-form-group" id="cta-card-layout-group" style="<?php echo 'card' === $selected_layout ? '' : 'display:none;'; ?>">
			<label for="cta-card-layout">
				<?php esc_html_e( 'Card Layout', 'cta-manager' ); ?>
			</label>
			<select id="cta-card-layout" name="cta_card_layout" class="cta-select">
				<?php foreach ( $card_layout_options as $layout_value => $layout_label ) : ?>
					<option value="<?php echo esc_attr( $layout_value ); ?>" <?php selected( $selected_card_layout, $layout_value ); ?>>
						<?php echo esc_html( $layout_label ); ?>
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
