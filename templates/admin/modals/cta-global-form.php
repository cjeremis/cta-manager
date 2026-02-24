<?php
/**
 * Admin Modal Template - Cta Global Form
 *
 * Handles markup rendering for the cta global form admin modal template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
