<?php
/**
 * Admin Tab Partial Template - Tab Action
 *
 * Handles markup rendering for the tab action admin tab partial.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Action Section -->
<div class="cta-form-section cta-no-mt">
<div class="cta-form-row">
	<div class="cta-form-group">
		<label for="cta-type">
			<?php esc_html_e( 'CTA Type', 'cta-manager' ); ?>
		</label>
		<?php
		// Base CTA types (free version)
		$cta_type_options = [
			[
				'value' => 'phone',
				'label' => __( 'Phone Call', 'cta-manager' ),
			],
			[
				'value' => 'link',
				'label' => __( 'Link', 'cta-manager' ),
			],
			[
				'value' => 'email',
				'label' => __( 'Email', 'cta-manager' ),
			],
		];

		// Allow pro plugin to extend CTA types
		$cta_type_options = apply_filters( 'cta_type_options', $cta_type_options, $editing_cta );

		$selected_type = $editing_cta['type'] ?? 'phone';
		?>
		<select id="cta-type" name="cta_type" class="cta-select" required>
			<?php foreach ( $cta_type_options as $option ) : ?>
				<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $selected_type, $option['value'] ); ?>>
					<?php echo esc_html( $option['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="cta-form-group" id="cta-phone-number-group">
		<label for="phone-number">
			<?php esc_html_e( 'Phone Number', 'cta-manager' ); ?>
			<span style="color: #dc3545;">*</span>
		</label>
		<input
			type="tel"
			id="phone-number"
			name="phone_number"
			value="<?php echo esc_attr( $editing_cta['phone_number'] ?? '' ); ?>"
			placeholder=""
		/>
	</div>

	<div class="cta-form-group" id="cta-email-to-group" style="display:none;">
		<label for="cta-email-to">
			<?php esc_html_e( 'Email To', 'cta-manager' ); ?>
		</label>
		<input
			type="email"
			id="cta-email-to"
			name="email_to"
			value="<?php echo esc_attr( $editing_cta['email_to'] ?? '' ); ?>"
			placeholder="<?php esc_attr_e( 'support@example.com', 'cta-manager' ); ?>"
		/>
	</div>

	<div class="cta-form-group" id="cta-link-url-group" style="display:none;">
		<div class="cta-label-row">
			<label for="cta-link-url"><?php esc_html_e( 'Link URL', 'cta-manager' ); ?></label>
			<label class="cta-time-toggle">
				<input type="checkbox" id="cta-link-target-new-tab" name="link_target_new_tab" value="1" <?php checked( ( $editing_cta['link_target'] ?? '_self' ) === '_blank' ); ?> />
				<span class="cta-time-toggle-label"><?php esc_html_e( 'New Tab', 'cta-manager' ); ?></span>
			</label>
		</div>
		<input
			type="url"
			id="cta-link-url"
			name="link_url"
			value="<?php echo esc_attr( $editing_cta['link_url'] ?? '' ); ?>"
			placeholder="<?php esc_attr_e( 'https://example.com', 'cta-manager' ); ?>"
		/>
	</div>
</div>

	<?php if ( ! $is_pro ) : ?>
		<!-- Pro CTA Type Upsell -->
		<div class="cta-form-row cta-full-width" style="margin-top: 20px;">
			<?php
			$icon        = 'admin-tools';
			$title       = __( 'Unlock Additional CTA Types', 'cta-manager' );
			$description = __( 'Upgrade to Pro to access advanced CTA types including Popup Modals and Slide-in displays for enhanced user engagement.', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/pro-upgrade-empty-state.php';
			unset( $icon, $title, $description );
			?>
		</div>
	<?php endif; ?>
</div>
