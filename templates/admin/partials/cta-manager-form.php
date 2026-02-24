<?php
/**
 * Admin Partial Template - Cta Manager Form
 *
 * Handles markup rendering for the cta manager form admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cta_form_context = isset( $cta_form_context ) && is_array( $cta_form_context ) ? $cta_form_context : [];
$editing_cta = $cta_form_context['editing_cta'] ?? ( $editing_cta ?? null );
$can_add = $cta_form_context['can_add'] ?? ( $can_add ?? false );
$show_form = $cta_form_context['show_form'] ?? ( $show_form ?? false );
$form_action = $cta_form_context['form_action'] ?? '';
$force_render = ! empty( $cta_form_context['force_render'] );
$is_pro = array_key_exists( 'is_pro', $cta_form_context ) ? (bool) $cta_form_context['is_pro'] : ( class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled() );
$pro_numbers = $cta_form_context['pro_numbers'] ?? null;
if ( null === $pro_numbers ) {
	$pro_numbers = ( $is_pro && class_exists( 'CTA_Pro_Phone_Numbers' ) ) ? CTA_Pro_Phone_Numbers::get_instance()->get_all() : [];
}
?>

<!-- Add/Edit CTA Form -->
	<?php if ( $force_render || $editing_cta || $can_add ) :
		$is_pro = isset( $is_pro ) ? $is_pro : ( class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled() );
		$pro_numbers = isset( $pro_numbers ) ? $pro_numbers : ( $is_pro && class_exists( 'CTA_Pro_Phone_Numbers' ) ? CTA_Pro_Phone_Numbers::get_instance()->get_all() : [] );
		?>
		<div class="cta-section" id="cta-cta-section" style="<?php echo $show_form ? '' : 'display:none;'; ?>">
			<?php
			ob_start();
			?>
			<button type="button" class="cta-help-icon-btn cta-docs-trigger" data-docs-page="feature-shortcode-usage" title="<?php esc_attr_e( 'Shortcode Usage Help', 'cta-manager' ); ?>">
				<span class="dashicons dashicons-editor-help"></span>
			</button>
			<a href="<?php echo esc_url( CTA_Admin_Menu::get_admin_url( 'cta' ) ); ?>" class="cta-button-secondary">
				<?php esc_html_e( 'Cancel', 'cta-manager' ); ?>
			</a>
			<button type="submit" class="cta-button-primary" form="cta-cta-form">
				<?php echo esc_html( $editing_cta ? __( 'Update CTA', 'cta-manager' ) : __( 'Save CTA', 'cta-manager' ) ); ?>
			</button>
			<?php
			$actions_html = ob_get_clean();

			$title = $editing_cta ? __( 'Edit CTA', 'cta-manager' ) : __( 'Add New CTA', 'cta-manager' );
			include CTA_PLUGIN_DIR . 'templates/admin/partials/section-header-with-actions.php';
			unset( $actions_html, $title );
			?>

			<div id="cta-form-wrapper" style="<?php echo $show_form ? '' : 'display:none;'; ?>">
			<form method="post" id="cta-cta-form" class="cta-cta-form" autocomplete="off" <?php echo $form_action ? 'action="' . esc_url( $form_action ) . '"' : ''; ?>>
				<?php wp_nonce_field( 'cta_cta_action', 'cta_cta_nonce' ); ?>
				<input type="hidden" name="cta_action" value="<?php echo esc_attr( $editing_cta ? 'update' : 'create' ); ?>" />
				<?php if ( $editing_cta ) : ?>
					<input type="hidden" name="cta_id" value="<?php echo esc_attr( $editing_cta['id'] ); ?>" />
				<?php endif; ?>

				<?php
// ============================================================================
// Tab Configuration
// ============================================================================

$tabs = [
	[
		'id'        => 'cta-tab-general',
		'label'     => __( 'General', 'cta-manager' ),
		'icon'      => 'admin-settings',
		'is_active' => true,
	],
	[
		'id'        => 'cta-tab-display',
		'label'     => __( 'Display', 'cta-manager' ),
		'icon'      => 'visibility',
		'is_active' => false,
	],
	[
		'id'        => 'cta-tab-action',
		'label'     => __( 'Action', 'cta-manager' ),
		'icon'      => 'admin-links',
		'is_active' => false,
	],
	[
		'id'           => 'cta-tab-content',
		'label'        => __( 'Content', 'cta-manager' ),
		'icon'         => 'text-page',
		'is_active'    => false,
		'requires_pro' => true,
	],
	[
		'id'        => 'cta-tab-button',
		'label'     => __( 'Button', 'cta-manager' ),
		'icon'      => 'button',
		'is_active' => false,
	],
	[
		'id'           => 'cta-tab-advanced',
		'label'        => __( 'Advanced', 'cta-manager' ),
		'icon'         => 'admin-generic',
		'is_active'    => false,
		'requires_pro' => true,
	],
	[
		'id'           => 'cta-tab-blacklist',
		'label'        => __( 'Blacklist', 'cta-manager' ),
		'icon'         => 'dismiss',
		'is_active'    => false,
		'requires_pro' => true,
	],
	[
		'id'              => 'cta-tab-slides',
		'label'           => __( 'Slides', 'cta-manager' ),
		'icon'            => 'slides',
		'is_active'       => false,
		'requires_pro'    => true,
		'conditional'     => true,
		'show_condition'  => function( $cta ) {
			return ( $cta['type'] ?? 'phone' ) === 'slide-in';
		},
	],
];

// Include tab navigation component
include CTA_PLUGIN_DIR . 'templates/admin/partials/cta-tabs-nav.php';
?>

<!-- Tab Panels Container -->
<div class="cta-tab-panels">

	<!-- General Tab -->
	<div id="cta-tab-general" class="cta-tab-panel is-active" role="tabpanel" aria-labelledby="cta-tab-general">
		<?php include CTA_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-general.php'; ?>
	</div>

	<!-- Display Tab -->
	<div id="cta-tab-display" class="cta-tab-panel" role="tabpanel" aria-labelledby="cta-tab-display">
		<?php include CTA_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-display.php'; ?>
	</div>

	<!-- Action Tab -->
	<div id="cta-tab-action" class="cta-tab-panel" role="tabpanel" aria-labelledby="cta-tab-action">
		<?php include CTA_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-action.php'; ?>
	</div>

	<!-- Content Tab (Pro Only) -->
	<?php if ( $is_pro && defined( 'CTA_PRO_PLUGIN_DIR' ) && file_exists( CTA_PRO_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-content.php' ) ) : ?>
	<div id="cta-tab-content" class="cta-tab-panel" role="tabpanel" aria-labelledby="cta-tab-content">
		<?php include CTA_PRO_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-content.php'; ?>
	</div>
	<?php endif; ?>

	<!-- Button Tab -->
	<div id="cta-tab-button" class="cta-tab-panel" role="tabpanel" aria-labelledby="cta-tab-button">
		<?php include CTA_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-button.php'; ?>
	</div>

	<!-- Advanced Tab (Pro Only) -->
	<?php if ( $is_pro ) : ?>
	<div id="cta-tab-advanced" class="cta-tab-panel" role="tabpanel" aria-labelledby="cta-tab-advanced">
		<?php include CTA_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-advanced.php'; ?>
	</div>
	<?php endif; ?>

	<!-- Blacklist Tab (Pro Only) -->
	<?php if ( $is_pro ) : ?>
	<div id="cta-tab-blacklist" class="cta-tab-panel" role="tabpanel" aria-labelledby="cta-tab-blacklist">
		<?php include CTA_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-blacklist.php'; ?>
	</div>
	<?php endif; ?>

	<!-- Slides Tab (Conditional - Pro Only) -->
	<?php if ( $is_pro && defined( 'CTA_PRO_PLUGIN_DIR' ) && file_exists( CTA_PRO_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-slides.php' ) ) : ?>
	<div id="cta-tab-slides" class="cta-tab-panel" role="tabpanel" aria-labelledby="cta-tab-slides" style="display:none;">
		<?php include CTA_PRO_PLUGIN_DIR . 'templates/admin/partials/tabs/tab-slides.php'; ?>
	</div>
	<?php endif; ?>

</div><!-- .cta-tab-panels -->

<!-- Preview Section (Displayed on All Tabs) -->
<div class="cta-form-section cta-preview-section">
	<div class="cta-preview-header">
		<div class="cta-preview-header-left">
			<?php cta_form_section_header( 'visibility', __( 'Live Preview', 'cta-manager' ) ); ?>
			<label class="cta-preview-toggle">
				<input type="checkbox" id="cta-show-preview" checked />
				<span class="cta-preview-toggle-switch"></span>
			</label>
		</div>
		<div class="cta-preview-type-indicator" id="cta-preview-type-indicator">
			<span class="cta-preview-type-label"><?php esc_html_e( 'Phone Call', 'cta-manager' ); ?></span>
		</div>
	</div>
	<div class="cta-form-row cta-full-width cta-preview-content">
		<div class="cta-form-group">
			<div id="cta-live-preview" class="cta-live-preview">
				<div class="cta-preview-device-frame">
					<div class="cta-preview-card" data-layout="button">
						<div class="cta-preview-text">
							<div class="cta-preview-title"></div>
							<div class="cta-preview-tagline"></div>
							<div class="cta-preview-body"></div>
						</div>
						<a href="#" class="cta-preview-button cta-preview-button--unstyled" onclick="return false;">
							<span class="cta-preview-icon-wrapper">
								<span class="cta-preview-icon"></span>
							</span>
							<span class="cta-preview-label"><?php esc_html_e( 'Call Now', 'cta-manager' ); ?></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- CTA Metadata Section (populated by JavaScript when editing) -->
<div class="cta-form-section cta-metadata-section" id="cta-form-metadata" style="display: none;">
	<div class="cta-cta-dates cta-modal-metadata">
		<div class="cta-cta-dates-left">
			<div class="cta-cta-date" data-field="created">
				<span class="cta-cta-date-label"><?php esc_html_e( 'Created:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value">—</span>
			</div>
			<div class="cta-cta-date" data-field="modified">
				<span class="cta-cta-date-label"><?php esc_html_e( 'Modified:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value">—</span>
			</div>
			<div class="cta-cta-date" data-field="first-active">
				<span class="cta-cta-date-label"><?php esc_html_e( 'First Active:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value">—</span>
			</div>
			<div class="cta-cta-date" data-field="created-by">
				<span class="cta-cta-date-label"><?php esc_html_e( 'Created by:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value">—</span>
			</div>
			<div class="cta-cta-date" data-field="starts" style="display: none;">
				<span class="cta-cta-date-label"><?php esc_html_e( 'Starts:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value">—</span>
			</div>
			<div class="cta-cta-date" data-field="ends" style="display: none;">
				<span class="cta-cta-date-label"><?php esc_html_e( 'Ends:', 'cta-manager' ); ?></span>
				<span class="cta-cta-date-value">—</span>
			</div>
		</div>
		<div class="cta-cta-dates-right" style="display: none;">
			<div class="cta-scheduled-countdown">
				<span class="dashicons dashicons-clock"></span>
				<span class="cta-countdown-label"></span>
				<span class="cta-countdown-time"></span>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	'use strict';

	/**
	 * Enable a tab
	 */
	function enableTab($tabLink, $tabPanel) {
		$tabLink.removeClass('is-disabled').removeAttr('aria-disabled').attr('tabindex', '0');
		$tabLink.find('.cta-tab-dependency-hint').remove();
	}

	/**
	 * Disable a tab with optional hint text
	 */
	function disableTab($tabLink, $tabPanel, hint) {
		$tabLink.addClass('is-disabled').attr('aria-disabled', 'true').attr('tabindex', '-1');

		// Add hint text if provided and not already present
		if (hint && !$tabLink.find('.cta-tab-dependency-hint').length) {
			$tabLink.append('<span class="cta-tab-dependency-hint">' + hint + '</span>');
		}

		// If currently active, switch to General tab
		if ($tabPanel.hasClass('is-active')) {
			var $container = $tabLink.closest('.cta-section, .cta-global-form-container');
			$container.find('.cta-tab-link[data-tab-target="cta-tab-general"]').trigger('click');
		}
	}

	/**
	 * Update conditional tabs based on CTA type and layout
	 */
	function updateConditionalTabs() {
		var $container = $(this).closest('.cta-section, .cta-global-form-container');
		if (!$container.length) {
			$container = $('.cta-section').first();
		}

		var ctaType = $container.find('#cta-type').val() || $('#cta-type').val();
		var layout = $container.find('#cta-layout').val() || $('#cta-layout').val();

		// Content tab - enabled only for card layouts (not button-only) AND Pro is active
		var $contentTabLink = $container.find('.cta-tab-link[data-tab-target="cta-tab-content"]');
		if (!$contentTabLink.length) {
			$contentTabLink = $('.cta-tab-link[data-tab-target="cta-tab-content"]');
		}
		var $contentTabPanel = $container.find('#cta-tab-content');
		if (!$contentTabPanel.length) {
			$contentTabPanel = $('#cta-tab-content');
		}

		// Only enable Content tab if Pro is active and layout is a card
		if ($contentTabLink.length) {
			var isPro = <?php echo $is_pro ? 'true' : 'false'; ?>;
			if (!isPro) {
				// Pro required - keep disabled (tab nav already handles this, but ensure it stays disabled)
				disableTab($contentTabLink, $contentTabPanel, '<?php echo esc_js( __( 'Pro feature', 'cta-manager' ) ); ?>');
			} else if (layout && layout !== 'button') {
				enableTab($contentTabLink, $contentTabPanel);
			} else {
				disableTab($contentTabLink, $contentTabPanel, '<?php echo esc_js( __( 'Requires card layout', 'cta-manager' ) ); ?>');
			}
		}

		// Slides tab - enabled only for slide-in type
		var $slidesTabLink = $container.find('.cta-tab-link[data-tab-target="cta-tab-slides"]');
		if (!$slidesTabLink.length) {
			$slidesTabLink = $('.cta-tab-link[data-tab-target="cta-tab-slides"]');
		}
		var $slidesTabPanel = $container.find('#cta-tab-slides');
		if (!$slidesTabPanel.length) {
			$slidesTabPanel = $('#cta-tab-slides');
		}

		if (ctaType === 'slide-in') {
			enableTab($slidesTabLink, $slidesTabPanel);
		} else {
			disableTab($slidesTabLink, $slidesTabPanel, '<?php echo esc_js( __( 'Requires slide-in type', 'cta-manager' ) ); ?>');
		}
	}

	// Prevent clicking disabled tabs
	$(document).on('click', '.cta-tab-link.is-disabled', function(e) {
		e.preventDefault();
		e.stopPropagation();
		return false;
	});

	// Run on page load
	updateConditionalTabs();

	// Run when CTA type or layout changes
	$(document).on('change', '#cta-type, #cta-layout', updateConditionalTabs);

	// Also run when form is initialized in modal
	$(document).on('ctc:initCtaForm', function(e, $form) {
		setTimeout(updateConditionalTabs, 50);
	});

	// Toggle live preview visibility (using delegation for modal support)
	$(document).on('change', '#cta-show-preview', function() {
		var $container = $(this).closest('.cta-section, .cta-global-form-container');
		var $previewContent = $container.find('.cta-preview-content');
		var $typeIndicator = $container.find('#cta-preview-type-indicator');

		if ($(this).is(':checked')) {
			$previewContent.slideDown(200);
			$typeIndicator.fadeIn(150);
		} else {
			$previewContent.slideUp(200);
			$typeIndicator.fadeOut(150);
		}
	});
});
</script>

			</form>

			<?php
			// Note: Blacklist and Format modals are included via footer.php
			// to avoid duplicate modal IDs when the form is used in different contexts.
			?>

			</div>
		</div>
	<?php endif; ?>

	
