<?php
/**
 * FAQ Accordion
 *
 * Renders a collection of FAQ items as an accordion.
 *
 * Expected variables:
 * - array $faq_items Array of ['question' => string, 'answer' => string]
 *
 * @package CTA_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$faq_items = $faq_items ?? [];
?>
<div class="cta-faq-accordion" data-cta-faq-accordion>
	<?php foreach ( $faq_items as $index => $faq_item ) : ?>
		<?php
		$question = $faq_item['question'] ?? '';
		$answer   = $faq_item['answer'] ?? '';
		$is_open  = $index === 0; // open first by default
		include __DIR__ . '/faq-accordion-item.php';
		unset( $question, $answer, $is_open );
		?>
	<?php endforeach; ?>
</div>
