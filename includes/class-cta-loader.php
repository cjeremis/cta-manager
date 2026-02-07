<?php
/**
 * Core loader class - orchestrates all plugin hooks
 *
 * @package CTAManager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CTA_Loader {

	use CTA_Singleton;

	/**
	 * @var array Actions to register
	 */
	private array $actions = [];

	/**
	 * @var array Filters to register
	 */
	private array $filters = [];

	/**
	 * @var array Shortcodes to register
	 */
	private array $shortcodes = [];

	/**
	 * Private constructor
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_global_hooks();
	}

	/**
	 * Load required dependencies
	 *
	 * @return void
	 */
	private function load_dependencies(): void {
		// Core classes are autoloaded
	}

	/**
	 * Define admin-specific hooks
	 *
	 * @return void
	 */
	private function define_admin_hooks(): void {
		$admin = CTA_Manager_Enqueue::get_instance();

		$this->add_action( 'admin_menu', [ CTA_Admin_Menu::get_instance(), 'register_menus' ] );
		$this->add_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_styles' ] );
		$this->add_action( 'admin_enqueue_scripts', [ $admin, 'enqueue_scripts' ] );
		$this->add_action( 'rest_api_init', [ CTA_Analytics_API::get_instance(), 'register_routes' ] );
		$this->add_action( 'admin_init', [ CTA_Manager::get_instance(), 'handle_form_submission' ] );

		// AJAX handlers
		$this->add_action( 'wp_ajax_cta_save_settings', [ CTA_Settings::get_instance(), 'ajax_save_settings' ] );
		$this->add_action( 'wp_ajax_cta_reset_settings', [ CTA_Settings::get_instance(), 'ajax_reset_settings' ] );
		$this->add_action( 'wp_ajax_cta_activate_pro_plugin', [ CTA_Settings::get_instance(), 'ajax_activate_pro_plugin' ] );
		$this->add_action( 'wp_ajax_cta_export_settings', [ CTA_Export_Import::get_instance(), 'ajax_export' ] );
		$this->add_action( 'wp_ajax_cta_import_settings', [ CTA_Export_Import::get_instance(), 'ajax_import' ] );
		$this->add_action( 'wp_ajax_cta_validate_import', [ CTA_Export_Import::get_instance(), 'ajax_validate_import' ] );
		$this->add_action( 'wp_ajax_cta_import_demo_data_selective', [ CTA_Demo_Data::get_instance(), 'ajax_import_demo_data_selective' ] );
		$this->add_action( 'wp_ajax_cta_empty_trash', [ CTA_Manager::get_instance(), 'ajax_empty_trash' ] );
		$this->add_action( 'admin_post_cta_export_settings', [ CTA_Export_Import::get_instance(), 'handle_export_request' ] );
		$this->add_action( 'admin_post_cta_import_settings', [ CTA_Export_Import::get_instance(), 'handle_import_request' ] );
		$this->add_action( 'admin_post_cta_import_demo_data', [ CTA_Demo_Data::get_instance(), 'handle_import_demo_data' ] );
		$this->add_action( 'admin_post_cta_delete_demo_data', [ CTA_Demo_Data::get_instance(), 'handle_delete_demo_data' ] );
		$this->add_action( 'admin_post_cta_reset_data', [ CTA_Data::get_instance(), 'handle_reset_data' ] );
		$this->add_action( 'admin_post_cta_toggle_debug', [ CTA_Tools::get_instance(), 'handle_debug_toggle' ] );
		$this->add_action( 'wp_ajax_cta_toggle_debug', [ CTA_Tools::get_instance(), 'ajax_toggle_debug' ] );

		// Gutenberg block registration
		$this->add_action( 'init', [ CTA_Block::get_instance(), 'register_block' ] );
		$this->add_action( 'rest_api_init', [ CTA_Block::get_instance(), 'register_rest_routes' ] );

		$this->add_action( 'wp_ajax_cta_dismiss_onboarding', [ CTA_Onboarding::get_instance(), 'ajax_dismiss' ] );
		$this->add_action( 'wp_ajax_cta_complete_onboarding', [ CTA_Onboarding::get_instance(), 'ajax_complete' ] );
		$this->add_action( 'wp_ajax_cta_onboarding_complete_step', [ CTA_Onboarding::get_instance(), 'ajax_complete_step' ] );

		// Custom icons AJAX handlers
		$this->add_action( 'wp_ajax_cta_get_custom_icons', [ CTA_Settings::get_instance(), 'ajax_get_custom_icons' ] );
		$this->add_action( 'wp_ajax_cta_add_custom_icon', [ CTA_Settings::get_instance(), 'ajax_add_custom_icon' ] );
		$this->add_action( 'wp_ajax_cta_delete_custom_icon', [ CTA_Settings::get_instance(), 'ajax_delete_custom_icon' ] );

		// Notifications AJAX handlers
		$this->add_action( 'wp_ajax_cta_get_notifications', [ CTA_Notifications_AJAX::get_instance(), 'ajax_get_notifications' ] );
		$this->add_action( 'wp_ajax_cta_delete_notification', [ CTA_Notifications_AJAX::get_instance(), 'ajax_delete_notification' ] );
		$this->add_action( 'wp_ajax_cta_get_notification_count', [ CTA_Notifications_AJAX::get_instance(), 'ajax_get_notification_count' ] );
		$this->add_action( 'wp_ajax_cta_mark_notification_read', [ CTA_Notifications_AJAX::get_instance(), 'ajax_mark_read' ] );
		$this->add_action( 'wp_ajax_cta_mark_notification_unread', [ CTA_Notifications_AJAX::get_instance(), 'ajax_mark_unread' ] );

		// Support AJAX handlers (remote to TopDevAmerica API)
		$this->add_action( 'wp_ajax_cta_submit_support_ticket', [ CTA_Support_AJAX::get_instance(), 'submit_ticket' ] );
		$this->add_action( 'wp_ajax_cta_get_support_tickets', [ CTA_Support_AJAX::get_instance(), 'get_tickets' ] );
		$this->add_action( 'wp_ajax_cta_get_support_ticket_detail', [ CTA_Support_AJAX::get_instance(), 'get_ticket_detail' ] );
		$this->add_action( 'wp_ajax_cta_submit_support_reply', [ CTA_Support_AJAX::get_instance(), 'submit_reply' ] );

		$this->add_filter( 'plugin_action_links_' . CTA_PLUGIN_BASENAME, [ $admin, 'add_action_links' ] );

		// Register cron hooks
		$this->add_action( 'cta_cleanup_analytics', [ CTA_Data::get_instance(), 'cleanup_old_analytics' ] );
		$this->add_action( 'cta_cleanup_analytics', [ CTA_Data::get_instance(), 'cleanup_old_events' ] );
	}

	/**
	 * Define hooks that run on both admin and frontend
	 *
	 * @return void
	 */
	private function define_global_hooks(): void {
		$this->add_action( 'admin_bar_menu', [ CTA_Admin_Menu::get_instance(), 'register_admin_bar_menu' ], 100 );
	}

	/**
	 * Define public-facing hooks
	 *
	 * @return void
	 */
	private function define_public_hooks(): void {
		$public = CTA_Public::get_instance();

		$this->add_action( 'wp_enqueue_scripts', [ $public, 'enqueue_styles' ] );
		$this->add_action( 'wp_enqueue_scripts', [ $public, 'enqueue_scripts' ] );
		$this->add_shortcode( 'cta-manager', [ CTA_Shortcode::get_instance(), 'render' ] );
		$this->add_action( 'wp_ajax_cta_track_click', [ CTA_Click_Tracker::get_instance(), 'track_click' ] );
		$this->add_action( 'wp_ajax_nopriv_cta_track_click', [ CTA_Click_Tracker::get_instance(), 'track_click' ] );
		$this->add_action( 'wp_ajax_cta_track_impression', [ CTA_Click_Tracker::get_instance(), 'track_impression' ] );
		$this->add_action( 'wp_ajax_nopriv_cta_track_impression', [ CTA_Click_Tracker::get_instance(), 'track_impression' ] );
		$this->add_action( 'wp_ajax_cta_track_page_view', [ CTA_Click_Tracker::get_instance(), 'track_page_view' ] );
		$this->add_action( 'wp_ajax_nopriv_cta_track_page_view', [ CTA_Click_Tracker::get_instance(), 'track_page_view' ] );

		// Initialize advanced embedding
		CTA_Advanced_Embedding::get_instance();
	}

	/**
	 * Add action
	 *
	 * @param string   $hook Hook name
	 * @param callable $callback Callback function
	 * @param int      $priority Hook priority
	 * @param int      $args Number of arguments
	 *
	 * @return void
	 */
	public function add_action( string $hook, callable $callback, int $priority = 10, int $args = 1 ): void {
		$this->actions[] = [
			'hook'     => $hook,
			'callback' => $callback,
			'priority' => $priority,
			'args'     => $args,
		];
	}

	/**
	 * Add filter
	 *
	 * @param string   $hook Hook name
	 * @param callable $callback Callback function
	 * @param int      $priority Hook priority
	 * @param int      $args Number of arguments
	 *
	 * @return void
	 */
	public function add_filter( string $hook, callable $callback, int $priority = 10, int $args = 1 ): void {
		$this->filters[] = [
			'hook'     => $hook,
			'callback' => $callback,
			'priority' => $priority,
			'args'     => $args,
		];
	}

	/**
	 * Add shortcode
	 *
	 * @param string   $tag Shortcode tag
	 * @param callable $callback Callback function
	 *
	 * @return void
	 */
	public function add_shortcode( string $tag, callable $callback ): void {
		$this->shortcodes[] = [
			'tag'      => $tag,
			'callback' => $callback,
		];
	}

	/**
	 * Register all hooks with WordPress
	 *
	 * @return void
	 */
	public function run(): void {
		foreach ( $this->actions as $action ) {
			add_action( $action['hook'], $action['callback'], $action['priority'], $action['args'] );
		}

		foreach ( $this->filters as $filter ) {
			add_filter( $filter['hook'], $filter['callback'], $filter['priority'], $filter['args'] );
		}

		foreach ( $this->shortcodes as $shortcode ) {
			add_shortcode( $shortcode['tag'], $shortcode['callback'] );
		}
	}
}
