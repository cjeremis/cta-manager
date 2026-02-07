<?php
/**
 * Pro Badge Partial Template
 *
 * Reusable badge component to indicate Pro-only features.
 * Used throughout admin interface to mark premium functionality.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variables available in this template:
 *
 * @var string $badge_style Badge style variant: 'inline' (default), 'large', 'minimal'
 * @var string $custom_text Custom text override for badge (default: 'Pro')
 */

$badge_style = isset( $badge_style ) ? $badge_style : 'inline';
$custom_text = isset( $custom_text ) ? $custom_text : __( 'Pro', 'cta-manager' );
?>

<span class="cta-pro-badge cta-pulse-pro ctab-pro-badge-<?php echo esc_attr( $badge_style ); ?>" title="<?php esc_attr_e( 'This feature is available in Pro version', 'cta-manager' ); ?>">
	<?php echo esc_html( $custom_text ); ?>
</span>
