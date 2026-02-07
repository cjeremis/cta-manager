<?php
/**
 * Section Header with Actions Partial
 *
 * Displays a section header with title and action buttons.
 *
 * Variables:
 * - $title (required) - Section title text (plain string)
 * - $title_raw (optional) - If provided, rendered without escaping (use sanitized/known-safe HTML)
 * - $actions_html (optional) - HTML for action buttons
 * - $title_tag (optional) - HTML tag for title (default: 'h2')
 * - $extra_class (optional) - Additional CSS classes
 * - $help_modal_target (optional) - Modal ID to link help icon to (e.g., '#my-help-modal')
 * - $help_icon_label (optional) - Aria label for help icon (default: 'View help')
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$actions_html       = $actions_html ?? '';
$title_tag          = $title_tag ?? 'h2';
$extra_class        = $extra_class ?? '';
$title_raw          = $title_raw ?? '';
$help_modal_target  = $help_modal_target ?? '';
$help_icon_label    = $help_icon_label ?? __( 'View help', 'cta-manager' );

// If help icon is requested but no actions_html provided, generate help icon
if ( $help_modal_target && empty( $actions_html ) ) {
	ob_start();
	$text         = '?';
	$modal_target = $help_modal_target;
	$variant      = 'button';
	$icon         = 'editor-help';
	$help_attrs   = isset( $attrs ) ? trim( $attrs ) : '';
	$attrs        = trim( $help_attrs . ' aria-label="' . esc_attr( $help_icon_label ) . '"' );
	$help_icon_classes = [ 'cta-section-help-icon' ];
	if ( false !== strpos( $help_attrs, 'data-docs-page=' ) ) {
		$help_icon_classes[] = 'cta-docs-trigger';
	}
	$extra_class = implode( ' ', $help_icon_classes );
	include __DIR__ . '/help-trigger.php';
	unset( $text, $modal_target, $variant, $icon, $extra_class, $attrs, $help_icon_classes, $help_attrs );
	$actions_html = ob_get_clean();
}
?>
<div class="cta-section-header <?php echo esc_attr( $extra_class ); ?>">
	<<?php echo esc_attr( $title_tag ); ?> class="cta-section-title">
		<?php
		if ( $title_raw ) {
			echo $title_raw; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo esc_html( $title );
		}
		?>
	</<?php echo esc_attr( $title_tag ); ?>>
	<?php if ( $actions_html ) : ?>
			<?php echo $actions_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
</div>
