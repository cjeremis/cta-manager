<?php
/**
 * Form Section Header Partial
 *
 * Displays a section header with an icon for form sections.
 *
 * Variables:
 * - $icon (required) - Dashicon name without 'dashicons-' prefix
 * - $title (required) - Section title text
 * - $show_pro_badge (optional) - Whether to show Pro badge after title (default: false)
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_pro_badge = $show_pro_badge ?? false;
?>
<h4 class="cta-form-section-title">
	<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
	<?php echo esc_html( $title ); ?>
	<?php if ( $show_pro_badge ) : ?>
		<?php cta_pro_badge_inline(); ?>
	<?php endif; ?>
</h4>
