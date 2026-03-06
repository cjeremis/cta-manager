<?php
/**
 * Admin Partial Template - Page Wrapper End
 *
 * Handles markup rendering for the page wrapper end admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Close page content and include footer
include __DIR__ . '/footer.php';
?>
</div>

<?php
// Render notifications panel (content populated by AJAX)
if ( class_exists( 'CTA_Notifications' ) ) {
	include __DIR__ . '/notifications-panel.php';
}

// Render Import Demo Modal (globally available for onboarding and tools page)
ob_start();
include CTA_PLUGIN_DIR . 'templates/admin/modals/import-demo-modal.php';
$demo_modal_body_html = ob_get_clean();

ob_start();
?>
<div style="display: flex; justify-content: flex-end; gap: var(--cta-spacing-sm);">
	<button type="button" id="cta-import-demo-submit" class="cta-button cta-button-primary">
		<span class="dashicons dashicons-download"></span>
		<?php esc_html_e( 'Import Selected', 'cta-manager' ); ?>
	</button>
</div>
<?php
$demo_modal_footer_html = ob_get_clean();
$modal = [
	'id'          => 'cta-import-demo-modal',
	'title_html'  => '<span class="dashicons dashicons-download"></span>' . esc_html__( 'Import Demo Data', 'cta-manager' ),
	'body_html'   => $demo_modal_body_html,
	'footer_html' => $demo_modal_footer_html,
	'size_class'  => 'cta-modal-md',
	'display'     => 'none',
];
include __DIR__ . '/modal.php';
unset( $modal, $demo_modal_body_html, $demo_modal_footer_html );

// Render documentation modal
$docs_has_pro = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();
$docs_title = $docs_has_pro ? __( 'Documentation for CTA Manager Pro', 'cta-manager' ) : __( 'Documentation for CTA Manager', 'cta-manager' );
$modal = [
	'id'              => 'cta-docs-modal',
	'title_html'      => '<span class="dashicons dashicons-book-alt"></span>' . $docs_title,
	'template'        => CTA_PLUGIN_DIR . 'templates/admin/modals/docs-modal.php',
	'size_class'      => 'cta-modal-xl',
	'extra_class'     => 'cta-docs-modal',
	'display'         => 'none',
	'close_in_header' => true,
];
include __DIR__ . '/modal.php';

// Render support ticket modal (globally available).
// Pro plugin can override this via the cta_support_modal_template filter.
$support_modal_template = apply_filters( 'cta_support_modal_template', __DIR__ . '/support-ticket-form.php' );
if ( is_string( $support_modal_template ) && file_exists( $support_modal_template ) ) {
	include $support_modal_template;
}
?>

<style id="cta-modal-maximize-shared-styles">
	.cta-modal.is-maximized .cta-modal-content {
		width: 100vw !important;
		max-width: 100vw !important;
		height: 100vh !important;
		max-height: 100vh !important;
		margin: 0 !important;
		border-radius: 0 !important;
	}

	.cta-modal .cta-modal-header {
		position: relative;
	}

	.cta-modal.is-maximized .cta-modal-header {
		padding-top: 10px !important;
		padding-bottom: 10px !important;
		min-height: 48px;
	}

	.cta-modal.is-maximized .cta-modal-header h2 {
		font-size: 21px;
		line-height: 1.2;
	}

	.cta-modal .cta-modal-maximize {
		position: absolute;
		top: 50%;
		right: 64px;
		transform: translateY(-50%);
		width: 30px;
		height: 30px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border: 1px solid rgba(255, 255, 255, 0.28);
		background: rgba(255, 255, 255, 0.09);
		border-radius: 8px;
		color: #ffffff;
		cursor: pointer;
		transition: background 0.15s ease, transform 0.15s ease;
	}

	.cta-modal .cta-modal-maximize:hover {
		background: rgba(255, 255, 255, 0.22);
		transform: translateY(calc(-50% - 1px));
	}

	.cta-modal .cta-modal-maximize .dashicons {
		font-size: 17px;
		width: 17px;
		height: 17px;
	}

	.cta-modal .cta-modal-close {
		top: 50% !important;
		transform: translateY(-50%) !important;
	}

	.cta-modal.is-maximized .cta-modal-maximize {
		top: 50%;
		transform: translateY(-50%);
	}

	@media (max-width: 782px) {
		.cta-modal .cta-modal-maximize {
			right: 58px;
			width: 28px;
			height: 28px;
			border-radius: 7px;
		}
	}
</style>

<script>
(function() {
	'use strict';

	var modalTargets = [
		'#cta-global-form-modal',
		'#cta-features-modal',
		'#cta-new-ticket-modal',
		'#cta-tools-export-data-modal'
	];
	var docsModalId = 'cta-docs-modal';

	function isTargetModal(modalEl) {
		if (!modalEl || modalEl.id === docsModalId) {
			return false;
		}
		return modalTargets.some(function(selector) {
			return modalEl.matches(selector);
		});
	}

	function syncMaximizeIcon(modalEl) {
		var button = modalEl.querySelector('.cta-modal-maximize');
		if (!button) {
			return;
		}

		var icon = button.querySelector('.dashicons');
		var isMaximized = modalEl.classList.contains('is-maximized');
		if (icon) {
			icon.className = 'dashicons ' + (isMaximized ? 'dashicons-editor-contract minimize' : 'dashicons-editor-expand');
		}
		button.setAttribute('aria-label', isMaximized ? 'Restore modal size' : 'Maximize modal');
		button.setAttribute('title', isMaximized ? 'Restore' : 'Maximize');
	}

	function ensureMaximizeButton(modalEl) {
		if (!isTargetModal(modalEl)) {
			return;
		}

		var header = modalEl.querySelector('.cta-modal-header');
		var closeBtn = modalEl.querySelector('.cta-modal-close');
		if (!header || !closeBtn || header.querySelector('.cta-modal-maximize')) {
			return;
		}

		var maximizeBtn = document.createElement('button');
		maximizeBtn.type = 'button';
		maximizeBtn.className = 'cta-modal-maximize';
		maximizeBtn.innerHTML = '<span class="dashicons dashicons-editor-expand" aria-hidden="true"></span>';
		header.insertBefore(maximizeBtn, closeBtn);
		syncMaximizeIcon(modalEl);
	}

	function setMaximized(modalEl, enabled) {
		if (!isTargetModal(modalEl)) {
			return;
		}
		modalEl.classList.toggle('is-maximized', !!enabled);
		syncMaximizeIcon(modalEl);
	}

	function closeVisibleTargetModalsOnEsc(e) {
		if (e.key !== 'Escape') {
			return;
		}

		var visibleModals = document.querySelectorAll('.cta-modal.is-maximized');
		visibleModals.forEach(function(modalEl) {
			if (!isTargetModal(modalEl)) {
				return;
			}
			var isVisible = modalEl.style.display !== 'none' && modalEl.offsetParent !== null;
			if (isVisible) {
				setMaximized(modalEl, false);
			}
		});
	}

	function bindModalMaximize() {
		if (window.__ctaSharedModalMaximizeBound) {
			return;
		}
		window.__ctaSharedModalMaximizeBound = true;

		modalTargets.forEach(function(selector) {
			var modalEl = document.querySelector(selector);
			if (modalEl) {
				ensureMaximizeButton(modalEl);
			}
		});

		document.addEventListener('click', function(e) {
			var maximizeBtn = e.target.closest('.cta-modal-maximize');
			if (!maximizeBtn) {
				return;
			}

			var modalEl = maximizeBtn.closest('.cta-modal');
			if (!isTargetModal(modalEl)) {
				return;
			}

			e.preventDefault();
			setMaximized(modalEl, !modalEl.classList.contains('is-maximized'));
		});

		document.addEventListener('ctaModalOpened', function(e, $modal) {
			var modalEl = $modal && $modal[0] ? $modal[0] : null;
			if (!isTargetModal(modalEl)) {
				return;
			}
			ensureMaximizeButton(modalEl);
			syncMaximizeIcon(modalEl);
		});

		document.addEventListener('ctaModalClosed', function(e, $modal) {
			var modalEl = $modal && $modal[0] ? $modal[0] : null;
			if (!isTargetModal(modalEl)) {
				return;
			}
			setMaximized(modalEl, false);
		});

		document.addEventListener('keydown', closeVisibleTargetModalsOnEsc);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bindModalMaximize);
	} else {
		bindModalMaximize();
	}
})();
</script>
