<?php
/**
 * Top Pages Analytics Table Partial
 *
 * Reusable table for top pages by clicks or impressions.
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 *
 * @var string $table_title Table header title
 * @var array  $columns Array of column labels
 * @var string $tbody_id ID for tbody element
 * @var string $pagination_id ID for pagination element
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$table_title   = isset( $table_title ) ? $table_title : '';
$columns       = isset( $columns ) ? $columns : [];
$tbody_id      = isset( $tbody_id ) ? $tbody_id : '';
$pagination_id = isset( $pagination_id ) ? $pagination_id : '';
?>

<div class="cta-table-section cta-top-pages-table">
	<div class="cta-table-header">
		<h3><?php echo esc_html( $table_title ); ?></h3>
	</div>
	<div class="cta-table-empty-state" style="display: none;">
		<?php
		$icon        = 'admin-page';
		$title       = __( 'No Page Data', 'cta-manager' );
		$description = __( 'Page performance data will appear once CTAs receive traffic.', 'cta-manager' );
		include CTA_PLUGIN_DIR . 'templates/admin/partials/empty-state.php';
		unset( $icon, $title, $description );
		?>
	</div>
	<div class="cta-table-wrapper">
		<table class="cta-events-table widefat striped">
			<thead>
				<tr>
					<?php foreach ( $columns as $column ) : ?>
						<th<?php echo isset( $column['class'] ) ? ' class="' . esc_attr( $column['class'] ) . '"' : ''; ?>>
							<?php echo esc_html( $column['label'] ); ?>
						</th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody id="<?php echo esc_attr( $tbody_id ); ?>">
				<tr>
					<td colspan="<?php echo count( $columns ); ?>" style="text-align: center;">
						<em><?php esc_html_e( 'Loading...', 'cta-manager' ); ?></em>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="cta-pagination" id="<?php echo esc_attr( $pagination_id ); ?>"></div>
</div>
