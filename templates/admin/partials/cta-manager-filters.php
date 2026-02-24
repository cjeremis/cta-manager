<?php
/**
 * Admin Partial Template - Cta Manager Filters
 *
 * Handles markup rendering for the cta manager filters admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

// Get custom icons for the icon filter (Pro only)
$custom_icons = $is_pro ? CTA_Data::get_instance()->get_custom_icons() : [];

// Check if demo count is available (passed from parent template)
$demo_count = isset( $demo_count ) ? $demo_count : 0;
?>

<div class="cta-cta-filters-wrapper">

	<?php if ( $demo_count > 0 ) : ?>
	<!-- Show Demo CTAs Filter - Only shown if demo data exists -->
	<div class="cta-form-group">
		<label><?php esc_html_e( 'Demo Data', 'cta-manager' ); ?></label>
		<div class="cta-checkbox-group cta-filter-toggle-row">
			<span class="cta-filter-toggle-label"><?php esc_html_e( 'Show Demo CTAs', 'cta-manager' ); ?></span>
			<?php
			$input_name  = 'show_demo';
			$input_id    = 'cta-show-demo-filter';
			$input_value = '1';
			$checked     = true;
			$label       = '';
			$show_status = false;
			$extra_class = 'cta-toggle--filter';
			$input_attrs = 'aria-label="' . esc_attr__( 'Show Demo CTAs', 'cta-manager' ) . '"';
			include CTA_PLUGIN_DIR . 'templates/admin/partials/toggle-switch.php';
			unset( $input_name, $input_id, $input_value, $checked, $label, $show_status, $extra_class, $input_attrs );
			?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( $is_pro ) : ?>
	<!-- Layout Filter - Pro only (free has only Button, nothing to filter) -->
	<div class="cta-form-group">
		<label for="cta-layout-filter"><?php esc_html_e( 'Layout', 'cta-manager' ); ?></label>
		<select id="cta-layout-filter" name="layout" class="cta-select">
			<option value=""><?php esc_html_e( 'All Layouts', 'cta-manager' ); ?></option>
			<option value="button"><?php esc_html_e( 'Button', 'cta-manager' ); ?></option>
			<option value="card-top"><?php esc_html_e( 'Card - Top', 'cta-manager' ); ?></option>
			<option value="card-left"><?php esc_html_e( 'Card - Left', 'cta-manager' ); ?></option>
			<option value="card-right"><?php esc_html_e( 'Card - Right', 'cta-manager' ); ?></option>
			<option value="card-bottom"><?php esc_html_e( 'Card - Bottom', 'cta-manager' ); ?></option>
		</select>
	</div>

	<?php endif; ?>

	<?php
	// Get visibility options (allows pro plugin to extend)
	$filter_visibility_options = apply_filters( 'cta_visibility_options', [
		[ 'value' => 'all_devices', 'label' => __( 'All Devices', 'cta-manager' ) ],
	], null );

	// Only show visibility filter if there are multiple options (Pro adds mobile_only, desktop_only, tablet_only)
	if ( count( $filter_visibility_options ) > 1 ) :
	?>
	<!-- Visibility Filter - Only shown when Pro is active (provides multiple options) -->
	<div class="cta-form-group">
		<label for="cta-visibility-filter"><?php esc_html_e( 'Visibility', 'cta-manager' ); ?></label>
		<select id="cta-visibility-filter" name="visibility" class="cta-select">
			<option value=""><?php esc_html_e( 'All', 'cta-manager' ); ?></option>
			<?php foreach ( $filter_visibility_options as $option ) : ?>
				<option value="<?php echo esc_attr( $option['value'] ); ?>">
					<?php echo esc_html( $option['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php endif; ?>

	<!-- CTA Type Filter -->
	<div class="cta-form-group">
		<label for="cta-type-filter"><?php esc_html_e( 'CTA Type', 'cta-manager' ); ?></label>
		<select id="cta-type-filter" name="type" class="cta-select">
			<option value=""><?php esc_html_e( 'All Types', 'cta-manager' ); ?></option>
			<?php
			// Get CTA type options (allows pro plugin to extend)
			$filter_type_options = apply_filters( 'cta_type_options', [
				[ 'value' => 'phone', 'label' => __( 'Phone', 'cta-manager' ) ],
				[ 'value' => 'link', 'label' => __( 'Link', 'cta-manager' ) ],
				[ 'value' => 'email', 'label' => __( 'Email', 'cta-manager' ) ],
			], null );

			foreach ( $filter_type_options as $option ) :
				?>
				<option value="<?php echo esc_attr( $option['value'] ); ?>">
					<?php echo esc_html( $option['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<!-- Status Filter - Always shown -->
	<div class="cta-form-group">
		<label for="cta-status-filter"><?php esc_html_e( 'Status', 'cta-manager' ); ?></label>
		<select id="cta-status-filter" name="status" class="cta-select">
			<option value="all" selected><?php esc_html_e( 'All', 'cta-manager' ); ?></option>
			<option value="published"><?php esc_html_e( 'Active', 'cta-manager' ); ?></option>
			<option value="scheduled"><?php esc_html_e( 'Scheduled', 'cta-manager' ); ?></option>
			<option value="draft"><?php esc_html_e( 'Draft', 'cta-manager' ); ?></option>
			<option value="archived"><?php esc_html_e( 'Archived', 'cta-manager' ); ?></option>
			<option value="trash"><?php esc_html_e( 'Trash', 'cta-manager' ); ?></option>
		</select>
	</div>

	<!-- Empty Trash Button - Only shown when Trash filter is selected -->
	<div class="cta-form-group cta-empty-trash-wrapper" id="cta-empty-trash-wrapper" style="display: none;">
		<button type="button" id="cta-empty-trash-btn" class="cta-button cta-button-danger" style="width: 100%;">
			<span class="dashicons dashicons-trash"></span>
			<?php esc_html_e( 'Empty Trash', 'cta-manager' ); ?>
		</button>
	</div>

	<?php if ( $is_pro ) : ?>
	<!-- Icon Filter - Pro only (free has only None, nothing to filter) -->
	<div class="cta-form-group">
		<label for="cta-icon-filter"><?php esc_html_e( 'Icon', 'cta-manager' ); ?></label>
		<select id="cta-icon-filter" name="icon" class="cta-select">
			<option value=""><?php esc_html_e( 'All Icons', 'cta-manager' ); ?></option>
			<option value="none"><?php esc_html_e( 'No Icon', 'cta-manager' ); ?></option>
			<option value="__has_icon__"><?php esc_html_e( 'Has Icon', 'cta-manager' ); ?></option>
			<optgroup label="<?php esc_attr_e( 'Built-in Icons', 'cta-manager' ); ?>">
				<option value="phone"><?php esc_html_e( 'Phone', 'cta-manager' ); ?></option>
				<option value="phone-alt"><?php esc_html_e( 'Mobile Phone', 'cta-manager' ); ?></option>
				<option value="star"><?php esc_html_e( 'Star', 'cta-manager' ); ?></option>
				<option value="star-filled"><?php esc_html_e( 'Glowing Star', 'cta-manager' ); ?></option>
				<option value="chat"><?php esc_html_e( 'Chat Bubble', 'cta-manager' ); ?></option>
				<option value="email"><?php esc_html_e( 'Email', 'cta-manager' ); ?></option>
				<option value="arrow-right"><?php esc_html_e( 'Arrow Right', 'cta-manager' ); ?></option>
				<option value="calendar"><?php esc_html_e( 'Calendar', 'cta-manager' ); ?></option>
				<option value="cart"><?php esc_html_e( 'Shopping Cart', 'cta-manager' ); ?></option>
				<option value="download"><?php esc_html_e( 'Download', 'cta-manager' ); ?></option>
				<option value="heart"><?php esc_html_e( 'Heart', 'cta-manager' ); ?></option>
				<option value="bolt"><?php esc_html_e( 'Lightning Bolt', 'cta-manager' ); ?></option>
			</optgroup>
			<?php if ( ! empty( $custom_icons ) ) : ?>
			<optgroup label="<?php esc_attr_e( 'Custom Icons', 'cta-manager' ); ?>">
				<?php foreach ( $custom_icons as $custom_icon ) : ?>
					<option value="<?php echo esc_attr( $custom_icon['id'] ); ?>"><?php echo esc_html( $custom_icon['name'] ); ?></option>
				<?php endforeach; ?>
			</optgroup>
			<?php endif; ?>
		</select>
	</div>

	<!-- Button Animation Filter -->
	<div class="cta-form-group">
		<label for="cta-button-animation-filter"><?php esc_html_e( 'Button Animation', 'cta-manager' ); ?></label>
		<?php
		// Base button animation filter options (free version)
		$button_animation_filter_options = [
			[
				'value' => '',
				'label' => __( 'All Animations', 'cta-manager' ),
			],
			[
				'value' => 'none',
				'label' => __( 'No Animation', 'cta-manager' ),
			],
		];

		// Allow pro plugin to extend button animation filter options
		$button_animation_filter_options = apply_filters( 'cta_button_animation_filter_options', $button_animation_filter_options, null );
		?>
		<select id="cta-button-animation-filter" name="button_animation" class="cta-select">
			<?php foreach ( $button_animation_filter_options as $option ) : ?>
				<option value="<?php echo esc_attr( $option['value'] ); ?>">
					<?php echo esc_html( $option['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<!-- Icon Animation Filter -->
	<div class="cta-form-group">
		<label for="cta-icon-animation-filter"><?php esc_html_e( 'Icon Animation', 'cta-manager' ); ?></label>
		<?php
		// Base icon animation filter options (free version)
		$icon_animation_filter_options = [
			[
				'value' => '',
				'label' => __( 'All Animations', 'cta-manager' ),
			],
			[
				'value' => 'none',
				'label' => __( 'No Animation', 'cta-manager' ),
			],
		];

		// Allow pro plugin to extend icon animation filter options
		$icon_animation_filter_options = apply_filters( 'cta_icon_animation_filter_options', $icon_animation_filter_options, null );
		?>
		<select id="cta-icon-animation-filter" name="icon_animation" class="cta-select">
			<?php foreach ( $icon_animation_filter_options as $option ) : ?>
				<option value="<?php echo esc_attr( $option['value'] ); ?>">
					<?php echo esc_html( $option['label'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php endif; ?>

</div>
