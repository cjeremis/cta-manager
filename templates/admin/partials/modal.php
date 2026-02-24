<?php
/**
 * Admin Partial Template - Modal
 *
 * Handles markup rendering for the modal admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$modal = $modal ?? [];

// Support JSON-encoded configuration strings for flexibility
if ( is_string( $modal ) ) {
	$decoded = json_decode( $modal, true );
	if ( is_array( $decoded ) ) {
		$modal = $decoded;
	}
}

// IMPORTANT: Do NOT use fallback patterns that inherit from outer scope.
// Each modal must be independent to prevent content leakage between modals.
$defaults = [
	'id'                => '',
	'title_html'        => '',
	'body_html'         => '',
	'footer_html'       => '',
	'extra_class'       => '',
	'size_class'        => '',
	'display'           => 'none',
	'overlay_attrs'     => '',
	'close_attrs'       => '',
	'header_actions'    => '',
	'template'          => '', // single template file (replaces body_template + footer_template)
	'body_template'     => '',
	'body_context'      => [],
	'footer_template'   => '',
	'footer_context'    => [],
	'footer_buttons'    => [], // optional array of [ 'label' => '', 'class' => '', 'attrs' => '' ]
	'header_icon'       => '',
	'header_layout'     => 'inline', // inline|stacked
	'header_style'      => '',
	'close_in_header'   => true,
	'close_on_clickout' => true,
];

$modal = wp_parse_args( $modal, $defaults );

$modal_id        = $modal['id'];
$title_html      = $modal['title_html'];
$body_html       = $modal['body_html'];
$footer_html     = $modal['footer_html'];
$extra_class     = $modal['extra_class'];
$size_class      = $modal['size_class'];
$display         = $modal['display'];
$overlay_attrs   = $modal['overlay_attrs'];
$close_attrs     = $modal['close_attrs'];
$header_actions  = $modal['header_actions'];
$template        = $modal['template'];
$body_template   = $modal['body_template'];
$body_context    = $modal['body_context'];
$footer_template = $modal['footer_template'];
$footer_context  = $modal['footer_context'];
$footer_buttons  = $modal['footer_buttons'];
$header_icon     = $modal['header_icon'];
$header_layout   = $modal['header_layout'];
$header_style    = $modal['header_style'];
$close_in_header = (bool) $modal['close_in_header'];
$close_on_clickout = (bool) $modal['close_on_clickout'];

// Handle single template file (includes both body and footer)
if ( ! $body_html && ! $footer_html && $template && file_exists( $template ) ) {
	ob_start();
	$context = array_merge( $body_context, $footer_context );
	include $template;
	$combined_html = ob_get_clean();

	// Split on footer marker if present
	if ( strpos( $combined_html, '<!-- MODAL_FOOTER -->' ) !== false ) {
		$parts       = explode( '<!-- MODAL_FOOTER -->', $combined_html, 2 );
		$body_html   = $parts[0];
		$footer_html = $parts[1] ?? '';
	} else {
		// No footer marker - everything is body
		$body_html = $combined_html;
	}
}

if ( ! $body_html && $body_template && file_exists( $body_template ) ) {
	ob_start();
	$context = $body_context;
	include $body_template;
	$body_html = ob_get_clean();
}

if ( ! $footer_html && $footer_template && file_exists( $footer_template ) ) {
	ob_start();
	$context = $footer_context;
	include $footer_template;
	$footer_html = ob_get_clean();
} elseif ( ! $footer_html && is_array( $footer_buttons ) && ! empty( $footer_buttons ) ) {
	ob_start();
	?>
	<div class="cta-modal-footer-buttons">
		<?php foreach ( $footer_buttons as $button ) : ?>
			<button type="button"
				class="<?php echo esc_attr( $button['class'] ?? 'cta-button-secondary' ); ?>"
				<?php echo isset( $button['attrs'] ) ? esc_attr( $button['attrs'] ) : ''; ?>>
				<?php echo isset( $button['label'] ) ? esc_html( $button['label'] ) : ''; ?>
			</button>
		<?php endforeach; ?>
	</div>
	<?php
	$footer_html = ob_get_clean();
}

// Determine overlay/close attributes based on config
if ( ! $overlay_attrs ) {
	$overlay_attrs = $close_on_clickout ? 'data-close-modal' : 'data-close-on-clickout="false"';
}
if ( ! $close_attrs ) {
	$close_attrs = $close_in_header ? 'data-close-modal' : '';
}
?>

<div id="<?php echo esc_attr( $modal_id ); ?>" class="cta-modal <?php echo esc_attr( $extra_class ); ?>" style="display: <?php echo esc_attr( $display ); ?>;">
	<div class="cta-modal-overlay" <?php echo esc_attr( $overlay_attrs ); ?>></div>
	<div class="cta-modal-content <?php echo esc_attr( $size_class ); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $modal_id . '-title' ); ?>">
		<div class="cta-modal-header" style="<?php echo esc_attr( $header_style ); ?>">
			<div class="cta-modal-title-wrap cta-modal-title-wrap--<?php echo esc_attr( $header_layout ); ?>">
				<?php if ( $header_icon ) : ?>
					<span class="cta-modal-title-icon <?php echo esc_attr( $header_icon ); ?>"></span>
				<?php endif; ?>
				<h2 id="<?php echo esc_attr( $modal_id . '-title' ); ?>"><?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			</div>
			<?php if ( $header_actions ) : ?>
				<div class="cta-modal-header-actions"><?php echo $header_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
			<?php if ( $close_in_header ) : ?>
				<button type="button" class="cta-modal-close" <?php echo esc_attr( $close_attrs ); ?> aria-label="<?php esc_attr_e( 'Close modal', 'cta-manager' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			<?php endif; ?>
		</div>
		<div class="cta-modal-body">
			<?php echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php if ( $footer_html ) : ?>
			<div class="cta-modal-footer">
				<?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
	</div>
</div>
