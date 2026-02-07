<?php
/**
 * Pro CTA Partial Template
 *
 * Reusable upgrade call-to-action component.
 * Used to prompt users to upgrade to Pro version throughout the interface.
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
 * @var string $cta_style CTA style variant: 'standard' (default), 'card', 'inline', 'banner'
 * @var string $feature_name Feature name being promoted
 * @var string $upgrade_url Custom upgrade URL (defaults to pro page)
 * @var bool $show_details Show detailed feature explanation
 */

$cta_style = isset( $cta_style ) ? $cta_style : 'standard';
$feature_name = isset( $feature_name ) ? $feature_name : __( 'This Feature', 'cta-manager' );
$upgrade_url = isset( $upgrade_url ) ? $upgrade_url : admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
$show_details = isset( $show_details ) ? (bool) $show_details : true;
?>

<?php if ( 'banner' === $cta_style ) : ?>
	<!-- Banner Style CTA -->
	<div class="cta-pro-cta ctab-pro-cta-banner">
		<div class="cta-cta-content">
			<span class="cta-cta-icon">⭐</span>
			<div class="cta-cta-text">
				<h3><?php printf( esc_html__( 'Unlock %s', 'cta-manager' ), esc_html( $feature_name ) ); ?></h3>
				<?php if ( $show_details ) : ?>
					<p>
						<?php esc_html_e( 'Upgrade to Pro to access premium features and unlock your full potential.', 'cta-manager' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
		$label       = __( 'Upgrade Now', 'cta-manager' );
		$url         = $upgrade_url;
		$variant     = 'primary';
		$extra_class = 'cta-button';
		$icon        = '';
		include __DIR__ . '/pro-upgrade-button.php';
		unset( $label, $url, $variant, $extra_class, $icon );
		?>
	</div>

<?php elseif ( 'card' === $cta_style ) : ?>
	<!-- Card Style CTA -->
	<div class="cta-pro-cta ctab-pro-cta-card">
		<div class="cta-cta-header">
			<span class="cta-cta-icon-large">✨</span>
			<h3><?php esc_html_e( 'Upgrade to Pro', 'cta-manager' ); ?></h3>
		</div>
		<p class="cta-cta-message">
			<?php printf( 
				esc_html__( '%s is a Pro-only feature. Unlock it with a premium subscription.', 'cta-manager' ),
				esc_html( $feature_name )
			); ?>
		</p>
		<?php if ( $show_details ) : ?>
			<ul class="cta-cta-benefits">
				<li><?php esc_html_e( 'Advanced analytics', 'cta-manager' ); ?></li>
				<li><?php esc_html_e( 'Custom colors & themes', 'cta-manager' ); ?></li>
				<li><?php esc_html_e( 'Priority support', 'cta-manager' ); ?></li>
			</ul>
		<?php endif; ?>
		<?php
		$label       = __( 'View Pro Plans', 'cta-manager' );
		$url         = $upgrade_url;
		$variant     = 'block';
		$extra_class = 'cta-button';
		$icon        = '';
		include __DIR__ . '/pro-upgrade-button.php';
		unset( $label, $url, $variant, $extra_class, $icon );
		?>
	</div>

<?php elseif ( 'inline' === $cta_style ) : ?>
	<!-- Inline Style CTA -->
	<div class="cta-pro-cta ctab-pro-cta-inline">
		<span class="cta-cta-text">
			<?php printf(
				esc_html__( '%s requires Pro', 'cta-manager' ),
				esc_html( $feature_name )
			); ?>
		</span>
		<?php
		$label       = __( 'Upgrade', 'cta-manager' );
		$url         = $upgrade_url;
		$variant     = 'small';
		$extra_class = 'cta-button';
		$icon        = '';
		include __DIR__ . '/pro-upgrade-button.php';
		unset( $label, $url, $variant, $extra_class, $icon );
		?>
	</div>

<?php else : ?>
	<!-- Standard Style CTA -->
	<div class="cta-pro-cta ctab-pro-cta-standard">
		<p class="cta-cta-message"><?php esc_html_e( 'This feature is available in the Pro version.', 'cta-manager' ); ?></p>
		<?php
		$label       = __( 'Learn more', 'cta-manager' );
		$url         = $upgrade_url;
		$variant     = 'ghost';
		$extra_class = 'cta-button';
		$icon        = '';
		include __DIR__ . '/pro-upgrade-button.php';
		unset( $label, $url, $variant, $extra_class, $icon );
		?>
	</div>
<?php endif; ?>
