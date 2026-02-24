<?php
/**
 * Admin Partial Template - Table Filters
 *
 * Handles markup rendering for the table filters admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
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
