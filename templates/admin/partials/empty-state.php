<?php
/**
 * Reusable Empty State Component
 *
 * Simple empty state with icon, title, description, and optional action link.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon         = isset( $icon ) ? $icon : 'megaphone';
$title        = isset( $title ) ? $title : __( 'Nothing here yet', 'cta-manager' );
$description  = isset( $description ) ? $description : '';
$action_url   = isset( $action_url ) ? $action_url : '';
$action_text  = isset( $action_text ) ? $action_text : '';
$action_class = isset( $action_class ) ? $action_class : '';
$action_attrs = isset( $action_attrs ) ? $action_attrs : '';
$action_icon  = isset( $action_icon ) ? $action_icon : '';
$extra_cls    = isset( $extra_class ) ? $extra_class : '';
?>

<div class="cta-empty-state <?php echo esc_attr( $extra_cls ); ?>">
	<div class="cta-empty-state-icon">
		<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
	</div>
	<div class="cta-empty-state-content">
		<h3 class="cta-empty-state-title"><?php echo esc_html( $title ); ?></h3>
		<?php if ( $description ) : ?>
			<p class="cta-empty-state-desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $action_url && $action_text ) : ?>
		<a href="<?php echo esc_url( $action_url ); ?>" class="cta-button-primary <?php echo esc_attr( $action_class ); ?>" <?php echo $action_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( $action_icon ) : ?>
				<span class="dashicons dashicons-<?php echo esc_attr( $action_icon ); ?>"></span>
			<?php endif; ?>
			<?php echo esc_html( $action_text ); ?>
		</a>
	<?php endif; ?>
</div>
