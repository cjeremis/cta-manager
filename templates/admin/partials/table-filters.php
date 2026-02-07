<?php
/**
 * Table Filters Partial
 *
 * Renders a row of filter controls for data tables.
 *
 * Variables:
 * - $filters (required) - Array of filter configs
 *   Format: [
 *     ['type' => 'text', 'id' => 'search', 'placeholder' => 'Search...', 'class' => 'cta-input'],
 *     ['type' => 'select', 'id' => 'filter', 'options' => [...], 'class' => 'cta-select'],
 *   ]
 * - $actions_html (optional) - Additional action buttons HTML
 * - $wrapper_class (optional) - Additional wrapper CSS classes
 *
 * @package CTA_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$actions_html  = $actions_html ?? '';
$wrapper_class = $wrapper_class ?? '';
?>

<div class="cta-table-filters <?php echo esc_attr( $wrapper_class ); ?>">
	<?php foreach ( $filters as $filter ) : ?>
		<?php $filter_type = $filter['type'] ?? 'text'; ?>

		<?php if ( $filter_type === 'text' ) : ?>
			<input
				type="text"
				id="<?php echo esc_attr( $filter['id'] ?? '' ); ?>"
				class="<?php echo esc_attr( $filter['class'] ?? 'cta-input' ); ?>"
				placeholder="<?php echo esc_attr( $filter['placeholder'] ?? '' ); ?>"
				<?php echo isset( $filter['style'] ) ? 'style="' . esc_attr( $filter['style'] ) . '"' : ''; ?>
			/>
		<?php elseif ( $filter_type === 'select' ) : ?>
			<select
				id="<?php echo esc_attr( $filter['id'] ?? '' ); ?>"
				class="<?php echo esc_attr( $filter['class'] ?? 'cta-select' ); ?>"
				<?php echo isset( $filter['style'] ) ? 'style="' . esc_attr( $filter['style'] ) . '"' : ''; ?>>
				<?php if ( isset( $filter['options'] ) && is_array( $filter['options'] ) ) : ?>
					<?php foreach ( $filter['options'] as $option ) : ?>
						<option value="<?php echo esc_attr( $option['value'] ?? '' ); ?>">
							<?php echo esc_html( $option['label'] ?? '' ); ?>
						</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php if ( $actions_html ) : ?>
		<div class="cta-table-filter-actions">
			<?php echo $actions_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>
</div>
