<?php
/**
 * Pricing Card Partial
 *
 * Displays a pricing plan card.
 *
 * Variables:
 * - $plan_name (required) - Plan name
 * - $price_display (required) - Price display text (can include HTML)
 * - $features (required) - Array of feature strings
 * - $cta_url (required) - Call-to-action URL
 * - $cta_text (required) - Call-to-action button text
 * - $is_featured (optional) - Whether this is the featured plan (default: false)
 * - $badge_text (optional) - Badge text (only shown if set)
 * - $price_type (optional) - Price type class: 'price', 'free' (default: 'price')
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_featured = $is_featured ?? false;
$badge_text  = $badge_text ?? '';
$price_type  = $price_type ?? 'amount';
?>
<div class="cta-pricing-card <?php echo $is_featured ? 'cta-pricing-card-featured' : ''; ?>">
	<?php if ( $badge_text ) : ?>
		<div class="cta-pricing-badge"><?php echo esc_html( $badge_text ); ?></div>
	<?php endif; ?>
	<div class="cta-pricing-header">
		<h3><?php echo esc_html( $plan_name ); ?></h3>
		<p class="cta-pricing-<?php echo esc_attr( $price_type ); ?>"><?php echo wp_kses_post( $price_display ); ?></p>
	</div>
	<div class="cta-pricing-features">
		<ul>
			<?php foreach ( $features as $feature ) : ?>
				<?php if ( is_array( $feature ) ) : ?>
					<li class="<?php echo esc_attr( $feature['class'] ?? '' ); ?>"><?php echo esc_html( $feature['text'] ); ?></li>
				<?php else : ?>
					<li><?php echo esc_html( $feature ); ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
	<a href="<?php echo esc_url( $cta_url ); ?>" class="cta-button <?php echo $is_featured ? 'cta-button-primary' : 'cta-button-secondary'; ?>">
		<?php echo esc_html( $cta_text ); ?>
	</a>
</div>
