<?php
/**
 * Plugin Name: tom & tom Image Optimization for CDN
 * Description: Automatically converts WordPress images to WebP format using picture elements. Works with WP Offload Media and CDN services for on-the-fly WebP conversion.
 * Version: 1.0.3
 * Author: tom & tom
 * Author URI: https://tomtom.design
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tomtomdesign-image-optim-for-cdn
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Define plugin constants
define( 'TOMTOM_IMAGE_OPTIM_VERSION', '1.0.3' );
define( 'TOMTOM_IMAGE_OPTIM_PLUGIN_FILE', __FILE__ );
define( 'TOMTOM_IMAGE_OPTIM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TOMTOM_IMAGE_OPTIM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Check WordPress version
function tomtom_image_optim_check_wp_version() {
	global $wp_version;
	$required_wp_version = '5.0';

	if ( version_compare( $wp_version, $required_wp_version, '<' ) ) {
		add_action( 'admin_notices', 'tomtom_image_optim_wp_version_notice' );
		return false;
	}
	return true;
}

// Check PHP version
function tomtom_image_optim_check_php_version() {
	$required_php_version = '7.4';

	if ( version_compare( PHP_VERSION, $required_php_version, '<' ) ) {
		add_action( 'admin_notices', 'tomtom_image_optim_php_version_notice' );
		return false;
	}
	return true;
}

// WordPress version notice
function tomtom_image_optim_wp_version_notice() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'tom & tom Image Optimization for CDN requires WordPress 5.0 or higher. Please update WordPress.', 'tomtomdesign-image-optim-for-cdn' ); ?></p>
	</div>
	<?php
}

// PHP version notice
function tomtom_image_optim_php_version_notice() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'tom & tom Image Optimization for CDN requires PHP 7.4 or higher. Please contact your host to update PHP.', 'tomtomdesign-image-optim-for-cdn' ); ?></p>
	</div>
	<?php
}

// Plugin activation
function tomtom_image_optim_activate() {
	// Set default options
	$default_options = array(
		'keycdn_webp_enabled' => true,
		'keycdn_webp_enhanced' => true,
		'keycdn_webp_debug' => false,
		'keycdn_webp_quality' => 80
	);
	
	foreach ( $default_options as $option => $value ) {
		if ( get_option( $option ) === false ) {
			add_option( $option, $value );
		}
	}
	
	// Set activation flag for admin notice
	set_transient( 'keycdn_webp_activated', true, 30 );
	
	// Flush rewrite rules
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'tomtom_image_optim_activate' );

// Plugin deactivation
function tomtom_image_optim_deactivate() {
	// Flush rewrite rules
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'tomtom_image_optim_deactivate' );

// Initialize plugin only if requirements are met
if ( tomtom_image_optim_check_wp_version() && tomtom_image_optim_check_php_version() ) {
	// Include plugin files
	$tomtom_image_optim_plugin_files = array(
		'includes/Settings.php',
		'includes/Converter.php',
		'includes/AdminPage.php',
	);

	foreach ( $tomtom_image_optim_plugin_files as $tomtom_image_optim_file ) {
		$tomtom_image_optim_file_path = plugin_dir_path( __FILE__ ) . $tomtom_image_optim_file;
		if ( file_exists( $tomtom_image_optim_file_path ) ) {
			require_once $tomtom_image_optim_file_path;
		}
	}
}
