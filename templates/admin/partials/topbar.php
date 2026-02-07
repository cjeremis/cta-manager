<?php
/**
 * CTA Manager Topbar
 *
 * Provides navigation across plugin pages and a right-side action slot.
 *
 * Expected variables:
 * - string $current_page   Page slug ('' for dashboard, 'cta', 'settings', 'tools', etc.)
 * - array  $topbar_actions Array of HTML strings to render on the right
 *
 * @package CTA_Manager
 * @subpackage Templates/Admin/Partials
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_page   = isset( $current_page ) ? $current_page : '';
$topbar_actions = isset( $topbar_actions ) ? (array) $topbar_actions : [];
$is_pro_active  = class_exists( 'CTA_Pro_Feature_Gate' ) && CTA_Pro_Feature_Gate::is_pro_enabled();

/**
 * Top Menu Configuration
 *
 * Define all menu items with their properties:
 * - type: 'link' | 'modal' | 'upgrade' | 'pro-badge'
 * - slug: Page slug or URL fragment
 * - label: Menu item text (translatable)
 * - classes: Array of CSS classes
 * - id: Optional element ID
 * - icon: Optional dashicon class (without 'dashicons-' prefix)
 * - data_attrs: Optional array of data attributes ['key' => 'value']
 * - condition: Optional boolean or callable to determine if item should render
 */
const CTA_TOPBAR_MENU_CONFIG = [
	[
		'type'    => 'link',
		'slug'    => '',
		'label'   => 'Dashboard',
		'classes' => [ 'cta-topbar-link' ],
	],
	[
		'type'    => 'link',
		'slug'    => 'cta',
		'label'   => 'Manage CTAs',
		'classes' => [ 'cta-topbar-link' ],
	],
	[
		'type'    => 'link',
		'slug'    => 'analytics',
		'label'   => 'Analytics',
		'classes' => [ 'cta-topbar-link' ],
	],
	[
		'type'    => 'link',
		'slug'    => 'settings',
		'label'   => 'Settings',
		'classes' => [ 'cta-topbar-link' ],
	],
	[
		'type'    => 'link',
		'slug'    => 'tools',
		'label'   => 'Tools',
		'classes' => [ 'cta-topbar-link' ],
	],
];

// Build menu items from config
$nav_items = [];
foreach ( CTA_TOPBAR_MENU_CONFIG as $item ) {
	// Evaluate conditions
	if ( isset( $item['condition'] ) ) {
		if ( $item['condition'] === 'show_upgrade' && $is_pro_active ) {
			continue;
		}
		if ( $item['condition'] === 'show_pro_badge' && ! $is_pro_active ) {
			continue;
		}
	}

	// Translate label
	$item['label'] = __( $item['label'], 'cta-manager' );

	// For pro-badge type, format the version string
	if ( $item['type'] === 'pro-badge' ) {
		$pro_version   = defined( 'CTA_PRO_VERSION' ) ? CTA_PRO_VERSION : '';
		$item['label'] = sprintf( $item['label'], $pro_version );
	}

	$nav_items[] = $item;
}
?>

<?php
$coming_soon_url = admin_url( 'admin.php?page=cta-manager-settings#cta-pro-license-key' );
?>
<div class="cta-admin-topbar">
	<div class="cta-topbar-nav">
		<?php foreach ( $nav_items as $item ) : ?>
			<?php
			$type          = $item['type'];
			$slug          = $item['slug'] ?? '';
			$label         = $item['label'];
			$classes       = $item['classes'] ?? [];
			$item_id       = $item['id'] ?? '';
			$icon          = $item['icon'] ?? '';
			$data_attrs    = $item['data_attrs'] ?? [];
			$is_active     = false;
			$url           = '';

			// Determine URL and active state based on type
			if ( $type === 'link' || $type === 'modal' ) {
				if ( $type === 'modal' ) {
					$url = $slug; // Use slug directly for modals (e.g., #cta-global-form-modal)
				} else {
					$url       = CTA_Admin_Menu::get_admin_url( $slug );
					$is_active = $current_page === $slug;
				}
			} elseif ( $type === 'upgrade' ) {
				$url = admin_url( $slug );
			}

			// Add active class if applicable
			if ( $is_active ) {
				$classes[] = 'is-active';
			}

			// Build class string
			$class_string = implode( ' ', $classes );

			// Build data attributes string
			$data_attrs_string = '';
			foreach ( $data_attrs as $attr_key => $attr_value ) {
				$data_attrs_string .= sprintf( ' data-%s="%s"', esc_attr( $attr_key ), esc_attr( $attr_value ) );
			}

			// Build ID attribute
			$id_attr = $item_id ? sprintf( ' id="%s"', esc_attr( $item_id ) ) : '';
			?>

			<?php if ( $type === 'pro-badge' ) : ?>
				<span class="<?php echo esc_attr( $class_string ); ?>"<?php echo $id_attr; ?>>
					<?php if ( $icon ) : ?>
						<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
					<?php endif; ?>
					<?php echo esc_html( $label ); ?>
				</span>
			<?php elseif ( $type === 'upgrade' ) : ?>
				<?php
				$label       = $label;
				$url         = $url;
				$variant     = 'ghost';
				$extra_class = $class_string;
				$icon        = $icon;
				$extra_attrs = $id_attr . ' ' . $data_attrs_string;
				include __DIR__ . '/pro-upgrade-button.php';
				unset( $label, $url, $variant, $extra_class, $icon, $extra_attrs );
				?>
			<?php else : ?>
				<a
					href="<?php echo esc_url( $url ); ?>"
					class="<?php echo esc_attr( $class_string ); ?>"
					<?php echo $id_attr; ?>
					<?php echo $data_attrs_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				>
					<?php if ( $icon ) : ?>
						<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
					<?php endif; ?>
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<?php if ( ! empty( $topbar_actions ) ) : ?>
		<div class="cta-topbar-actions">
			<?php foreach ( $topbar_actions as $action_html ) : ?>
				<?php echo $action_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<?php if ( ! $is_pro_active ) : ?>
	<?php
	$modal = [
		'id'          => 'cta-pro-upgrade-modal',
		'extra_class' => 'cta-pro-upgrade-modal',
		'title_html'  => '<span class="dashicons dashicons-star-filled cta-animate-slow"></span>' . esc_html__( 'CTA Manager Pro', 'cta-manager' ),
		'template'    => CTA_PLUGIN_DIR . 'templates/admin/modals/pro-upgrade.php',
		'size_class'  => 'cta-pro-modal-card',
		'display'     => 'none',
	];
	include __DIR__ . '/modal.php';
	unset( $modal );
	?>
<?php endif; ?>
