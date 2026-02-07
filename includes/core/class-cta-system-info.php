<?php
/**
 * CTA System Info - Auto-collect System Metadata
 *
 * Collects WordPress, PHP, theme, and plugin information for support tickets.
 *
 * @package CTA_Manager
 * @subpackage Core
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CTA_System_Info {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct() {
        // Constructor
    }

    /**
     * Get all system information
     *
     * @return array System information
     */
    public function get_all_info() {
        return array(
            'wp_version' => $this->get_wp_version(),
            'php_version' => $this->get_php_version(),
            'theme_name' => $this->get_theme_name(),
            'theme_version' => $this->get_theme_version(),
            'active_plugins' => $this->get_active_plugins(),
            'cta_manager_version' => $this->get_cta_manager_version(),
            'cta_manager_pro_version' => $this->get_cta_manager_pro_version(),
            'customer_site_url' => $this->get_site_url(),
            'customer_ip' => $this->get_client_ip(),
            'customer_user_agent' => $this->get_user_agent(),
        );
    }

    /**
     * Get WordPress version
     *
     * @return string WordPress version
     */
    public function get_wp_version() {
        global $wp_version;
        return $wp_version;
    }

    /**
     * Get PHP version
     *
     * @return string PHP version
     */
    public function get_php_version() {
        return phpversion();
    }

    /**
     * Get current theme name
     *
     * @return string Theme name
     */
    public function get_theme_name() {
        $theme = wp_get_theme();
        return $theme->get('Name');
    }

    /**
     * Get current theme version
     *
     * @return string Theme version
     */
    public function get_theme_version() {
        $theme = wp_get_theme();
        return $theme->get('Version');
    }

    /**
     * Get list of active plugins
     *
     * @return array Active plugins with name and version
     */
    public function get_active_plugins() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        $active_plugin_data = array();

        foreach ($active_plugins as $plugin_path) {
            if (isset($all_plugins[$plugin_path])) {
                $plugin_data = $all_plugins[$plugin_path];
                $active_plugin_data[] = array(
                    'name' => $plugin_data['Name'],
                    'version' => $plugin_data['Version'],
                    'path' => $plugin_path,
                );
            }
        }

        return $active_plugin_data;
    }

    /**
     * Get CTA Manager version
     *
     * @return string|null Plugin version or null if not active
     */
    public function get_cta_manager_version() {
        if (defined('CTA_VERSION')) {
            return CTA_VERSION;
        }

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_file = WP_PLUGIN_DIR . '/cta-manager/cta-manager.php';

        if (file_exists($plugin_file)) {
            $plugin_data = get_plugin_data($plugin_file);
            return $plugin_data['Version'] ?? null;
        }

        return null;
    }

    /**
     * Get CTA Manager Pro version
     *
     * @return string|null Plugin version or null if not active
     */
    public function get_cta_manager_pro_version() {
        if (defined('CTA_PRO_VERSION')) {
            return CTA_PRO_VERSION;
        }

        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_file = WP_PLUGIN_DIR . '/cta-manager-pro/cta-manager-pro.php';

        if (file_exists($plugin_file)) {
            $plugin_data = get_plugin_data($plugin_file);
            return $plugin_data['Version'] ?? null;
        }

        return null;
    }

    /**
     * Get site URL
     *
     * @return string Site URL
     */
    public function get_site_url() {
        return get_site_url();
    }

    /**
     * Get client IP address
     *
     * @return string|null Client IP address
     */
    public function get_client_ip() {
        $ip = null;

        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Validate and sanitize IP
        if ($ip) {
            $ip = filter_var($ip, FILTER_VALIDATE_IP);
        }

        return $ip ?: null;
    }

    /**
     * Get user agent
     *
     * @return string|null User agent string
     */
    public function get_user_agent() {
        return isset($_SERVER['HTTP_USER_AGENT'])
            ? sanitize_text_field($_SERVER['HTTP_USER_AGENT'])
            : null;
    }

    /**
     * Get server information
     *
     * @return array Server information
     */
    public function get_server_info() {
        return array(
            'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown',
            'mysql_version' => $this->get_mysql_version(),
            'max_upload_size' => wp_max_upload_size(),
            'php_memory_limit' => ini_get('memory_limit'),
            'wp_memory_limit' => WP_MEMORY_LIMIT,
            'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
        );
    }

    /**
     * Get MySQL version
     *
     * @return string MySQL version
     */
    public function get_mysql_version() {
        global $wpdb;
        return $wpdb->db_version();
    }

    /**
     * Get current user email
     *
     * @return string|null User email or null if not logged in
     */
    public function get_current_user_email() {
        $current_user = wp_get_current_user();

        if ($current_user && $current_user->ID > 0) {
            return $current_user->user_email;
        }

        return null;
    }

    /**
     * Get current user name
     *
     * @return string|null User display name or null if not logged in
     */
    public function get_current_user_name() {
        $current_user = wp_get_current_user();

        if ($current_user && $current_user->ID > 0) {
            return $current_user->display_name;
        }

        return null;
    }
}
