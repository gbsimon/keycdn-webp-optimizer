<?php
/**
 * WebP Admin Class
 * 
 * Handles the admin settings page and configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

class KeyCDN_WebP_Admin {
    
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
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_filter('plugin_action_links_' . plugin_basename(KEYCDN_WEBP_PLUGIN_FILE), array($this, 'add_plugin_action_links'));
        add_action('admin_notices', array($this, 'activation_notice'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('KeyCDN WebP Settings', 'keycdn-webp-optimizer'),
            __('KeyCDN WebP', 'keycdn-webp-optimizer'),
            'manage_options',
            'keycdn-webp-settings',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Add Settings link to plugins page
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=keycdn-webp-settings') . '">' . __('Settings', 'keycdn-webp-optimizer') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Show activation notice
     */
    public function activation_notice() {
        if (get_transient('keycdn_webp_activated')) {
            delete_transient('keycdn_webp_activated');
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <strong><?php _e('KeyCDN WebP Image Optimization', 'keycdn-webp-optimizer'); ?></strong> 
                    <?php _e('has been activated!', 'keycdn-webp-optimizer'); ?>
                    <a href="<?php echo admin_url('options-general.php?page=keycdn-webp-settings'); ?>" class="button button-primary" style="margin-left: 10px;">
                        <?php _e('Configure Settings', 'keycdn-webp-optimizer'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Register settings
        register_setting('keycdn_webp_settings', 'keycdn_webp_enabled', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));
        
        register_setting('keycdn_webp_settings', 'keycdn_webp_enhanced', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));
        
        register_setting('keycdn_webp_settings', 'keycdn_webp_debug', array(
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));
        
        register_setting('keycdn_webp_settings', 'keycdn_webp_quality', array(
            'type' => 'string',
            'default' => '80',
            'sanitize_callback' => array($this, 'sanitize_quality')
        ));
        
        // Add settings sections
        add_settings_section(
            'keycdn_webp_prerequisites',
            __('Prerequisites', 'keycdn-webp-optimizer'),
            array($this, 'prerequisites_section_callback'),
            'keycdn-webp-settings'
        );
        
        add_settings_section(
            'keycdn_webp_configuration',
            __('Configuration', 'keycdn-webp-optimizer'),
            array($this, 'configuration_section_callback'),
            'keycdn-webp-settings'
        );
        
        add_settings_section(
            'keycdn_webp_status',
            __('Status', 'keycdn-webp-optimizer'),
            array($this, 'status_section_callback'),
            'keycdn-webp-settings'
        );
        
        // Add settings fields
        add_settings_field(
            'keycdn_webp_enabled',
            __('Enable WebP Conversion', 'keycdn-webp-optimizer'),
            array($this, 'enabled_field_callback'),
            'keycdn-webp-settings',
            'keycdn_webp_configuration'
        );
        
        add_settings_field(
            'keycdn_webp_enhanced',
            __('Enhanced Mode', 'keycdn-webp-optimizer'),
            array($this, 'enhanced_field_callback'),
            'keycdn-webp-settings',
            'keycdn_webp_configuration'
        );
        
        add_settings_field(
            'keycdn_webp_debug',
            __('Debug Mode', 'keycdn-webp-optimizer'),
            array($this, 'debug_field_callback'),
            'keycdn-webp-settings',
            'keycdn_webp_configuration'
        );
        
        add_settings_field(
            'keycdn_webp_quality',
            __('WebP Quality', 'keycdn-webp-optimizer'),
            array($this, 'quality_field_callback'),
            'keycdn-webp-settings',
            'keycdn_webp_configuration'
        );
    }
    
    /**
     * Sanitize quality setting
     */
    public function sanitize_quality($value) {
        // Convert to integer and ensure it's within valid range
        $value = intval($value);
        
        // If conversion failed or value is 0, use default
        if ($value <= 0) {
            $value = 80;
        }
        
        // Ensure value is between 1 and 100
        $sanitized = max(1, min(100, $value));
        
        return $sanitized;
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_keycdn-webp-settings') {
            return;
        }
        
        wp_enqueue_style(
            'keycdn-webp-admin',
            KEYCDN_WEBP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            KEYCDN_WEBP_VERSION
        );
        
        wp_enqueue_script(
            'keycdn-webp-admin',
            KEYCDN_WEBP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            KEYCDN_WEBP_VERSION,
            true
        );
    }
    
    /**
     * Admin page callback
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('keycdn_webp_settings');
                do_settings_sections('keycdn-webp-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Prerequisites section callback
     */
    public function prerequisites_section_callback() {
        ?>
        <div class="keycdn-webp-prerequisites">
            <div class="prerequisites-info">
                <h3><?php _e('Prerequisites for WebP Optimization', 'keycdn-webp-optimizer'); ?></h3>
                
                <div class="prerequisite-item">
                    <h4><?php _e('1. WP Offload Media Plugin', 'keycdn-webp-optimizer'); ?></h4>
                    <ul>
                        <li><?php _e('Must be installed and configured', 'keycdn-webp-optimizer'); ?></li>
                        <li><?php _e('Images should be offloaded to Digital Ocean Spaces or similar S3-compatible storage', 'keycdn-webp-optimizer'); ?></li>
                    </ul>
                </div>
                
                <div class="prerequisite-item">
                    <h4><?php _e('2. KeyCDN Configuration', 'keycdn-webp-optimizer'); ?></h4>
                    <ul>
                        <li><?php _e('Your CDN must support format conversion via URL parameters', 'keycdn-webp-optimizer'); ?></li>
                        <li><?php _e('Enable ?format=webp parameter support in your CDN settings', 'keycdn-webp-optimizer'); ?></li>
                        <li><?php _e('The CDN should convert images to WebP on-the-fly', 'keycdn-webp-optimizer'); ?></li>
                    </ul>
                </div>
                
                <div class="how-it-works">
                    <h4><?php _e('How it works:', 'keycdn-webp-optimizer'); ?></h4>
                    <ul>
                        <li><?php _e('The plugin converts &lt;img&gt; tags to &lt;picture&gt; elements', 'keycdn-webp-optimizer'); ?></li>
                        <li><?php _e('Adds WebP source with ?format=webp&quality=X parameter', 'keycdn-webp-optimizer'); ?></li>
                        <li><?php _e('Browser automatically serves WebP to supported browsers', 'keycdn-webp-optimizer'); ?></li>
                        <li><?php _e('Falls back to original format for older browsers', 'keycdn-webp-optimizer'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Configuration section callback
     */
    public function configuration_section_callback() {
        echo '<p>' . __('Configure the WebP conversion settings below.', 'keycdn-webp-optimizer') . '</p>';
    }
    
    /**
     * Status section callback
     */
    public function status_section_callback() {
        $enabled = get_option('keycdn_webp_enabled', true);
        $enhanced = get_option('keycdn_webp_enhanced', true);
        $debug = get_option('keycdn_webp_debug', false);
        $quality = get_option('keycdn_webp_quality', 80);
        
        ?>
        <div class="keycdn-webp-status">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Current Status', 'keycdn-webp-optimizer'); ?></th>
                    <td>
                        <span class="status-indicator <?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                            <?php echo $enabled ? __('Enabled', 'keycdn-webp-optimizer') : __('Disabled', 'keycdn-webp-optimizer'); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Mode', 'keycdn-webp-optimizer'); ?></th>
                    <td>
                        <?php echo $enhanced ? __('Enhanced (handles srcset)', 'keycdn-webp-optimizer') : __('Basic', 'keycdn-webp-optimizer'); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Debug Mode', 'keycdn-webp-optimizer'); ?></th>
                    <td>
                        <?php echo $debug ? __('Enabled', 'keycdn-webp-optimizer') : __('Disabled', 'keycdn-webp-optimizer'); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Quality Setting', 'keycdn-webp-optimizer'); ?></th>
                    <td>
                        <?php echo $quality; ?>%
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Enabled field callback
     */
    public function enabled_field_callback() {
        $enabled = get_option('keycdn_webp_enabled', true);
        ?>
        <label>
            <input type="checkbox" name="keycdn_webp_enabled" value="1" <?php checked($enabled, 1); ?> />
            <?php _e('Enable WebP conversion for WordPress images', 'keycdn-webp-optimizer'); ?>
        </label>
        <p class="description"><?php _e('When enabled, the plugin will automatically convert img tags to picture elements with WebP support.', 'keycdn-webp-optimizer'); ?></p>
        <?php
    }
    
    /**
     * Enhanced field callback
     */
    public function enhanced_field_callback() {
        $enhanced = get_option('keycdn_webp_enhanced', true);
        ?>
        <label>
            <input type="checkbox" name="keycdn_webp_enhanced" value="1" <?php checked($enhanced, 1); ?> />
            <?php _e('Enable enhanced mode (handles srcset attributes)', 'keycdn-webp-optimizer'); ?>
        </label>
        <p class="description"><?php _e('Enhanced mode processes responsive images with srcset attributes for complete WebP coverage.', 'keycdn-webp-optimizer'); ?></p>
        <?php
    }
    
    /**
     * Debug field callback
     */
    public function debug_field_callback() {
        $debug = get_option('keycdn_webp_debug', false);
        ?>
        <label>
            <input type="checkbox" name="keycdn_webp_debug" value="1" <?php checked($debug, 1); ?> />
            <?php _e('Enable debug mode (shows conversion info in HTML comments)', 'keycdn-webp-optimizer'); ?>
        </label>
        <p class="description"><?php _e('Debug mode adds HTML comments showing which images were converted. Useful for troubleshooting.', 'keycdn-webp-optimizer'); ?></p>
        <?php
    }
    
    /**
     * Quality field callback
     */
    public function quality_field_callback() {
        $quality = get_option('keycdn_webp_quality', 80);
        ?>
        <input type="number" id="keycdn_webp_quality" name="keycdn_webp_quality" value="<?php echo esc_attr($quality); ?>" min="1" max="100" class="small-text" />
        <p class="description"><?php _e('WebP quality setting (1-100). Higher values mean better quality but larger file sizes.', 'keycdn-webp-optimizer'); ?></p>
        <?php
    }
}
