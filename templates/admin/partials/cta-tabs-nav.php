<?php
/**
 * Admin Partial Template - Cta Tabs Nav
 *
 * Handles markup rendering for the cta tabs nav admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Default to empty array if not provided
$tabs = $tabs ?? [];
if ( empty( $tabs ) ) {
	return;
}

$is_pro = $is_pro ?? ( class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled() );
?>

<div class="cta-tabs">
	<div class="cta-tabs__nav" role="tablist">
		<?php foreach ( $tabs as $tab ) :
			// Skip conditional tabs if their condition isn't met
			if ( ! empty( $tab['conditional'] ) && isset( $tab['show_condition'] ) && is_callable( $tab['show_condition'] ) ) {
				if ( ! call_user_func( $tab['show_condition'], $editing_cta ?? null ) ) {
					continue;
				}
			}

			$tab_id = $tab['id'] ?? '';
			$label = $tab['label'] ?? '';
			$icon = $tab['icon'] ?? '';
			$is_active = $tab['is_active'] ?? false;
			$requires_pro = $tab['requires_pro'] ?? false;

			// Skip Pro tabs entirely if Pro is not active (don't even show them)
			if ( $requires_pro && ! $is_pro ) {
				continue;
			}

			$is_disabled = $requires_pro && ! $is_pro;

			if ( empty( $tab_id ) || empty( $label ) ) {
				continue;
			}

			$tab_classes = 'cta-tab-link';
			if ( $is_active ) {
				$tab_classes .= ' is-active';
			}
			if ( $is_disabled ) {
				$tab_classes .= ' is-disabled';
			}
			?>
			<button
				type="button"
				class="<?php echo esc_attr( $tab_classes ); ?>"
				data-tab-target="<?php echo esc_attr( $tab_id ); ?>"
				aria-controls="<?php echo esc_attr( $tab_id ); ?>"
				aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
				role="tab"
				<?php if ( $is_disabled ) : ?>
					disabled
					aria-disabled="true"
					title="<?php esc_attr_e( 'Pro feature - upgrade to unlock', 'cta-manager' ); ?>"
				<?php endif; ?>
			>
				<?php if ( ! empty( $icon ) ) : ?>
					<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
				<?php endif; ?>
				<span class="cta-tab-label"><?php echo esc_html( $label ); ?></span>
				<?php if ( $requires_pro && ! $is_pro ) : ?>
					<span class="cta-tab-pro-badge"><?php cta_pro_badge_inline(); ?></span>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div>
</div>
