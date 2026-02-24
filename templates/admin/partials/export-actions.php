<?php
/**
 * Admin Partial Template - Export Actions
 *
 * Handles markup rendering for the export actions admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
