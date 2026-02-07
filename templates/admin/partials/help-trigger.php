<?php
/**
 * Help Trigger Partial
 *
 * Renders a help trigger link or button that opens a modal.
 *
 * Variables:
 * - $modal_target (required) - Modal selector (e.g., '#modal-id')
 * - $text (optional) - Trigger text (default: '?')
 * - $variant (optional) - Style variant: 'link' or 'button' (default: 'link')
 * - $icon (optional) - Dashicon name (default: 'editor-help')
 * - $extra_class (optional) - Additional CSS classes
 * - $attrs (optional) - Additional HTML attributes
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text        = $text ?? '?';
$variant     = $variant ?? 'link';
$icon        = $icon ?? 'editor-help';
$extra_class = $extra_class ?? '';
$attrs       = $attrs ?? '';

$base_class = $variant === 'button' ? '' : 'cta-help-trigger';
$classes    = trim( $base_class . ' ' . $extra_class );
?>

<?php if ( $variant === 'button' ) : ?>
	<button type="button"
		class="<?php echo esc_attr( $classes ); ?>"
		data-open-modal="<?php echo esc_attr( $modal_target ); ?>"
		<?php echo $attrs; ?>>
		<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
		<?php if ( $text !== '?' ) : ?>
			<?php echo esc_html( $text ); ?>
		<?php endif; ?>
	</button>
<?php else : ?>
	<a href="<?php echo esc_attr( $modal_target ); ?>"
		class="<?php echo esc_attr( $classes ); ?>"
		data-open-modal="<?php echo esc_attr( $modal_target ); ?>"
		<?php echo $attrs; ?>>
		<?php if ( $icon ) : ?>
			<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
		<?php endif; ?>
		<?php echo esc_html( $text ); ?>
	</a>
<?php endif; ?>
