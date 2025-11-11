<?php
/**
 * Plugin Name: KeyCDN WebP Image Optimization
 * Description: Automatically converts WordPress images to WebP format using picture elements. Works with WP Offload Media and KeyCDN for on-the-fly WebP conversion.
 * Version: 1.0.3
 * Author: tom & tom
 * Author URI: https://tomtom.design
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: keycdn-webp-optimizer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KEYCDN_WEBP_VERSION', '1.0.3');
define('KEYCDN_WEBP_PLUGIN_FILE', __FILE__);
define('KEYCDN_WEBP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KEYCDN_WEBP_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class KeyCDN_WebP_Optimizer {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once KEYCDN_WEBP_PLUGIN_DIR . 'includes/class-webp-converter.php';
        require_once KEYCDN_WEBP_PLUGIN_DIR . 'includes/class-webp-admin.php';
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize converter
        KeyCDN_WebP_Converter::get_instance();
        
        // Initialize admin if in admin area
        if (is_admin()) {
            KeyCDN_WebP_Admin::get_instance();
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_options = array(
            'keycdn_webp_enabled' => true,
            'keycdn_webp_enhanced' => true,
            'keycdn_webp_debug' => false,
            'keycdn_webp_quality' => 80
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
        
        // Set activation flag for admin notice
        set_transient('keycdn_webp_activated', true, 30);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
KeyCDN_WebP_Optimizer::get_instance();
