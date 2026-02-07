<?php
/**
 * CTA Global Form modal body
 *
 * @var array $context
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_markup = $context['form_markup'] ?? '';
?>
<div class="cta-global-form-container"></div>

<template id="cta-global-form-template">
	<?php echo $form_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</template>
