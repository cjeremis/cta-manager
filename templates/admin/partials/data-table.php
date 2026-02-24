<?php
/**
 * Admin Partial Template - Data Table
 *
 * Handles markup rendering for the data table admin partial template.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$table_class         = $table_class ?? 'widefat striped';
$table_id            = $table_id ?? '';
$tbody_content       = $tbody_content ?? null;
$pagination_id       = $pagination_id ?? null;
$table_section_class = $table_section_class ?? '';
$loading_colspan     = $loading_colspan ?? ( is_array( $headers ) ? count( $headers ) : 8 );
?>

<div class="cta-table-section <?php echo esc_attr( $table_section_class ); ?>">
	<div class="cta-table-empty-state" style="display: none;">
		<?php
		$icon        = 'list-view';
		$title       = __( 'No Events Found', 'cta-manager' );
		$description = __( 'Event data will appear here once CTAs are viewed or clicked.', 'cta-manager' );
		include CTA_PLUGIN_DIR . 'templates/admin/partials/empty-state.php';
		unset( $icon, $title, $description );
		?>
	</div>
	<div class="cta-table-wrapper">
		<table class="cta-events-table <?php echo esc_attr( $table_class ); ?>"<?php echo $table_id ? ' id="' . esc_attr( $table_id ) . '"' : ''; ?>>
			<thead>
				<tr>
					<?php if ( is_array( $headers ) ) : ?>
						<?php foreach ( $headers as $column ) : ?>
							<?php
							$th_class  = $column['class'] ?? '';
							$sortable  = ! empty( $column['sortable'] );
							$sort_key  = $column['sort_key'] ?? '';
							$th_class .= $sortable ? ' cta-sortable' : '';
							?>
							<th<?php echo $th_class ? ' class="' . esc_attr( trim( $th_class ) ) . '"' : ''; ?><?php echo $sortable && $sort_key ? ' data-sort-key="' . esc_attr( $sort_key ) . '"' : ''; ?>>
								<?php echo esc_html( $column['label'] ); ?>
								<?php if ( $sortable ) : ?>
									<span class="cta-sort-icon dashicons dashicons-sort"></span>
								<?php endif; ?>
							</th>
						<?php endforeach; ?>
					<?php else : ?>
						<?php echo $headers; // Allow custom HTML for complex headers ?>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody id="<?php echo esc_attr( $tbody_id ); ?>">
				<?php if ( $tbody_content !== null ) : ?>
					<?php echo $tbody_content; // Pre-rendered tbody content ?>
				<?php else : ?>
					<tr>
						<td colspan="<?php echo esc_attr( $loading_colspan ); ?>" style="text-align: center;">
							<em><?php esc_html_e( 'Loading...', 'cta-manager' ); ?></em>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php if ( $pagination_id ) : ?>
		<div class="cta-pagination" id="<?php echo esc_attr( $pagination_id ); ?>"></div>
	<?php endif; ?>
</div>
