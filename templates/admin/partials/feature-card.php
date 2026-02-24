<?php
/**
 * Admin Partial Template - Feature Card
 *
 * Handles markup rendering for the feature card admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon           = $icon ?? '';
$title          = $title ?? '';
$hook_name      = $hook_name ?? '';
$hook_type      = $hook_type ?? '';
$description    = $description ?? '';
$features       = $features ?? [];
$details        = $details ?? '';
$instructions   = $instructions ?? [];
$badge          = $badge ?? '';
$badge_text     = $badge_text ?? '';
$is_pro         = $is_pro ?? false;
$is_implemented = $is_implemented ?? false;

// Get labels from centralized data source
$feature_labels = CTA_Features::get_labels();

// Default badge text based on variant
if ( $badge && ! $badge_text ) {
	$badge_text = 'primary' === $badge ? $feature_labels['badge_coming_soon'] : ucwords( str_replace( '-', ' ', $badge ) );
}

// Map badge variants to pulse animation classes
$badge_pulse_map = [
	'primary' => 'cta-pulse-primary',
	'warning' => 'cta-pulse-warning',
];
$badge_pulse_class = isset( $badge_pulse_map[ $badge ] ) ? ' ' . $badge_pulse_map[ $badge ] : '';

// Build card classes based on feature state
// Priority: Coming Soon > Pro > Free
$card_classes = [ 'cta-feature-card' ];
if ( $badge ) {
	// Coming soon (purple border) - takes priority even if pro
	$card_classes[] = 'is-coming-soon';
} elseif ( $is_pro ) {
	// Pro implemented (gold border)
	$card_classes[] = 'is-pro';
} elseif ( $is_implemented ) {
	// Free implemented (green border)
	$card_classes[] = 'is-free';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
	<div class="cta-feature-header">
		<div class="cta-feature-icon"><?php echo esc_html( $icon ); ?></div>
		<h4>
			<?php echo esc_html( $title ); ?>
			<?php if ( ! empty( $hook_name ) ) : ?>
				<br><code style="font-size: 0.75em; font-weight: normal; color: #666;"><?php echo esc_html( $hook_name ); ?></code>
			<?php endif; ?>
		</h4>
	</div>
	<p class="cta-feature-description"><?php echo esc_html( $description ); ?></p>
	<?php if ( ! empty( $features ) ) : ?>
		<ul class="cta-feature-list">
			<?php foreach ( $features as $feature_item ) : ?>
				<li><?php echo esc_html( $feature_item ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php if ( ! empty( $details ) || ! empty( $instructions ) ) : ?>
		<div class="cta-feature-learn-more">
			<button type="button" class="cta-learn-more-button" data-feature-title="<?php echo esc_attr( $title ); ?>">
				<?php esc_html_e( 'Learn More', 'cta-manager' ); ?>
			</button>
		</div>
	<?php endif; ?>
	<div class="cta-feature-footer">
		<?php if ( $is_pro && $badge ) : ?>
			<?php // Pro + Coming Soon: show both badges ?>
			<span class="cta-pro-badge"><?php echo esc_html( $feature_labels['badge_pro'] ); ?></span>
			<span class="cta-badge cta-badge-<?php echo esc_attr( $badge ); ?><?php echo esc_attr( $badge_pulse_class ); ?>"><?php echo esc_html( $badge_text ); ?></span>
		<?php elseif ( $badge ) : ?>
			<?php // Coming Soon only (free features) ?>
			<span class="cta-badge cta-badge-<?php echo esc_attr( $badge ); ?><?php echo esc_attr( $badge_pulse_class ); ?>"><?php echo esc_html( $badge_text ); ?></span>
		<?php elseif ( $is_pro ) : ?>
			<?php // Pro only (implemented pro features) ?>
			<span class="cta-pro-badge"><?php echo esc_html( $feature_labels['badge_pro'] ); ?></span>
		<?php elseif ( $is_implemented && ! $is_pro ) : ?>
			<?php // Free badge (implemented free features) ?>
			<span class="cta-free-badge"><?php echo esc_html( $feature_labels['badge_free'] ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $hook_type ) ) : ?>
			<span class="cta-badge cta-badge-info cta-hook-badge" style="margin-left:auto; background:#e3f2fd; color:#1976d2;">
				<?php echo esc_html( 'filter' === $hook_type ? __( 'Filter Hook', 'cta-manager' ) : __( 'Action Hook', 'cta-manager' ) ); ?>
			</span>
		<?php endif; ?>
	</div>
</div>
