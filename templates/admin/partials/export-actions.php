<?php
/**
 * Export Action Buttons Partial
 *
 * Renders a group of export action buttons.
 *
 * Variables:
 * - $export_types (required) - Array of button configs
 *   Format: [
 *     ['id' => 'btn-id', 'text' => 'Button Text', 'icon' => 'dashicon-name', 'class' => 'btn-class', 'attrs' => 'extra attributes'],
 *   ]
 * - $wrapper_class (optional) - Additional wrapper CSS classes
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wrapper_class = $wrapper_class ?? '';
?>

<div class="cta-section-actions <?php echo esc_attr( $wrapper_class ); ?>">
	<?php foreach ( $export_types as $export ) : ?>
		<?php
		$button_id    = $export['id'] ?? '';
		$button_text  = $export['text'] ?? '';
		$icon         = $export['icon'] ?? 'download';
		$button_class = $export['class'] ?? 'cta-button-secondary';
		$attrs        = $export['attrs'] ?? '';
		?>
		<button type="button" class="<?php echo esc_attr( $button_class ); ?>" id="<?php echo esc_attr( $button_id ); ?>" <?php echo $attrs; ?>>
			<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
			<?php echo esc_html( $button_text ); ?>
		</button>
	<?php endforeach; ?>
</div>
