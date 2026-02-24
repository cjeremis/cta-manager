<?php
/**
 * Admin Partial Template - Faq Accordion Item
 *
 * Handles markup rendering for the faq accordion item admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$question = $question ?? '';
$answer   = $answer ?? '';
$index    = isset( $index ) ? (int) $index : 0;
$is_open  = isset( $is_open ) ? (bool) $is_open : false;

$panel_id  = 'cta-faq-panel-' . $index;
$trigger_id = 'cta-faq-trigger-' . $index;
?>
<div class="cta-faq-accordion__item <?php echo $is_open ? 'is-open' : ''; ?>">
	<button
		class="cta-faq-accordion__trigger"
		type="button"
		id="<?php echo esc_attr( $trigger_id ); ?>"
		aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
		aria-controls="<?php echo esc_attr( $panel_id ); ?>"
	>
		<span class="cta-faq-accordion__question"><?php echo esc_html( $question ); ?></span>
		<span class="cta-faq-accordion__icon" aria-hidden="true"></span>
	</button>
	<div
		class="cta-faq-accordion__panel"
		id="<?php echo esc_attr( $panel_id ); ?>"
		role="region"
		aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
		<?php if ( ! $is_open ) : ?>
			hidden
		<?php endif; ?>
	>
		<p class="cta-faq-accordion__answer"><?php echo esc_html( $answer ); ?></p>
	</div>
</div>
