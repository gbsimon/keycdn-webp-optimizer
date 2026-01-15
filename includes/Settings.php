<?php
/**
 * Settings Management
 * Handles plugin settings registration, sanitization, and field callbacks
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register settings
 */
function tomtom_image_optim_register_settings() {
	// Register settings
	register_setting( 'keycdn_webp_settings', 'keycdn_webp_enabled', array(
		'type' => 'boolean',
		'default' => true,
		'sanitize_callback' => 'rest_sanitize_boolean'
	) );
	
	register_setting( 'keycdn_webp_settings', 'keycdn_webp_enhanced', array(
		'type' => 'boolean',
		'default' => true,
		'sanitize_callback' => 'rest_sanitize_boolean'
	) );
	
	register_setting( 'keycdn_webp_settings', 'keycdn_webp_debug', array(
		'type' => 'boolean',
		'default' => false,
		'sanitize_callback' => 'rest_sanitize_boolean'
	) );
	
	register_setting( 'keycdn_webp_settings', 'keycdn_webp_quality', array(
		'type' => 'string',
		'default' => '80',
		'sanitize_callback' => 'tomtom_image_optim_sanitize_quality'
	) );
	
	// Add settings sections
	add_settings_section(
		'keycdn_webp_prerequisites',
		__( 'Prerequisites', 'tomtomdesign-image-optim-for-cdn' ),
		'tomtom_image_optim_prerequisites_section_callback',
		'tomtomdesign-image-optim-settings'
	);
	
	add_settings_section(
		'keycdn_webp_configuration',
		__( 'Configuration', 'tomtomdesign-image-optim-for-cdn' ),
		'tomtom_image_optim_configuration_section_callback',
		'tomtomdesign-image-optim-settings'
	);
	
	add_settings_section(
		'keycdn_webp_status',
		__( 'Status', 'tomtomdesign-image-optim-for-cdn' ),
		'tomtom_image_optim_status_section_callback',
		'tomtomdesign-image-optim-settings'
	);
	
	// Add settings fields
	add_settings_field(
		'keycdn_webp_enabled',
		__( 'Enable WebP Conversion', 'tomtomdesign-image-optim-for-cdn' ),
		'tomtom_image_optim_enabled_field_callback',
		'tomtomdesign-image-optim-settings',
		'keycdn_webp_configuration'
	);
	
	add_settings_field(
		'keycdn_webp_enhanced',
		__( 'Enhanced Mode', 'tomtomdesign-image-optim-for-cdn' ),
		'tomtom_image_optim_enhanced_field_callback',
		'tomtomdesign-image-optim-settings',
		'keycdn_webp_configuration'
	);
	
	add_settings_field(
		'keycdn_webp_debug',
		__( 'Debug Mode', 'tomtomdesign-image-optim-for-cdn' ),
		'tomtom_image_optim_debug_field_callback',
		'tomtomdesign-image-optim-settings',
		'keycdn_webp_configuration'
	);
	
	add_settings_field(
		'keycdn_webp_quality',
		__( 'WebP Quality', 'tomtomdesign-image-optim-for-cdn' ),
		'tomtom_image_optim_quality_field_callback',
		'tomtomdesign-image-optim-settings',
		'keycdn_webp_configuration'
	);
}
add_action( 'admin_init', 'tomtom_image_optim_register_settings' );

/**
 * Sanitize quality setting
 */
function tomtom_image_optim_sanitize_quality( $value ) {
	// Convert to integer and ensure it's within valid range
	$value = intval( $value );
	
	// If conversion failed or value is 0, use default
	if ( $value <= 0 ) {
		$value = 80;
	}
	
	// Ensure value is between 1 and 100
	$sanitized = max( 1, min( 100, $value ) );
	
	return $sanitized;
}

/**
 * Prerequisites section callback
 */
function tomtom_image_optim_prerequisites_section_callback() {
	?>
	<div class="tomtomdesign-image-optim-prerequisites">
		<div class="prerequisites-info">
			<h3><?php esc_html_e( 'Prerequisites for WebP Optimization', 'tomtomdesign-image-optim-for-cdn' ); ?></h3>
			
			<div class="prerequisite-item">
				<h4><?php esc_html_e( '1. WP Offload Media Plugin', 'tomtomdesign-image-optim-for-cdn' ); ?></h4>
				<ul>
					<li><?php esc_html_e( 'Must be installed and configured', 'tomtomdesign-image-optim-for-cdn' ); ?></li>
					<li><?php esc_html_e( 'Images should be offloaded to Digital Ocean Spaces or similar S3-compatible storage', 'tomtomdesign-image-optim-for-cdn' ); ?></li>
				</ul>
			</div>
			
			<div class="prerequisite-item">
				<h4><?php esc_html_e( '2. CDN Configuration', 'tomtomdesign-image-optim-for-cdn' ); ?></h4>
				<ul>
					<li><?php esc_html_e( 'Your CDN must support format conversion via URL parameters', 'tomtomdesign-image-optim-for-cdn' ); ?></li>
					<li><?php esc_html_e( 'Enable ?format=webp parameter support in your CDN settings', 'tomtomdesign-image-optim-for-cdn' ); ?></li>
					<li><?php esc_html_e( 'The CDN should convert images to WebP on-the-fly', 'tomtomdesign-image-optim-for-cdn' ); ?></li>
				</ul>
			</div>
			
			<div class="how-it-works">
				<h4><?php esc_html_e( 'How it works:', 'tomtomdesign-image-optim-for-cdn' ); ?></h4>
				<ul>
					<li><?php esc_html_e( 'The plugin converts &lt;img&gt; tags to &lt;picture&gt; elements', 'tomtomdesign-image-optim-for-cdn' ); ?></li>
					<li><?php esc_html_e( 'Adds WebP source with ?format=webp&quality=X parameter', 'tomtomdesign-image-optim-for-cdn' ); ?></li>
					<li><?php esc_html_e( 'Browser automatically serves WebP to supported browsers', 'tomtomdesign-image-optim-for-cdn' ); ?></li>
					<li><?php esc_html_e( 'Falls back to original format for older browsers', 'tomtomdesign-image-optim-for-cdn' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Configuration section callback
 */
function tomtom_image_optim_configuration_section_callback() {
	echo '<p>' . esc_html__( 'Configure the WebP conversion settings below.', 'tomtomdesign-image-optim-for-cdn' ) . '</p>';
}

/**
 * Status section callback
 */
function tomtom_image_optim_status_section_callback() {
	$enabled = get_option( 'keycdn_webp_enabled', true );
	$enhanced = get_option( 'keycdn_webp_enhanced', true );
	$debug = get_option( 'keycdn_webp_debug', false );
	$quality = get_option( 'keycdn_webp_quality', 80 );
	
	?>
	<div class="tomtomdesign-image-optim-status">
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Current Status', 'tomtomdesign-image-optim-for-cdn' ); ?></th>
				<td>
					<span class="status-indicator <?php echo esc_attr( $enabled ? 'enabled' : 'disabled' ); ?>">
						<?php echo esc_html( $enabled ? __( 'Enabled', 'tomtomdesign-image-optim-for-cdn' ) : __( 'Disabled', 'tomtomdesign-image-optim-for-cdn' ) ); ?>
					</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Mode', 'tomtomdesign-image-optim-for-cdn' ); ?></th>
				<td>
					<?php echo esc_html( $enhanced ? __( 'Enhanced (handles srcset)', 'tomtomdesign-image-optim-for-cdn' ) : __( 'Basic', 'tomtomdesign-image-optim-for-cdn' ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Debug Mode', 'tomtomdesign-image-optim-for-cdn' ); ?></th>
				<td>
					<?php echo esc_html( $debug ? __( 'Enabled', 'tomtomdesign-image-optim-for-cdn' ) : __( 'Disabled', 'tomtomdesign-image-optim-for-cdn' ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Quality Setting', 'tomtomdesign-image-optim-for-cdn' ); ?></th>
				<td>
					<?php echo esc_html( $quality ); ?>%
				</td>
			</tr>
		</table>
	</div>
	<?php
}

/**
 * Enabled field callback
 */
function tomtom_image_optim_enabled_field_callback() {
	$enabled = get_option( 'keycdn_webp_enabled', true );
	?>
	<label>
		<input type="checkbox" name="keycdn_webp_enabled" value="1" <?php checked( $enabled, 1 ); ?> />
		<?php esc_html_e( 'Enable WebP conversion for WordPress images', 'tomtomdesign-image-optim-for-cdn' ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'When enabled, the plugin will automatically convert img tags to picture elements with WebP support.', 'tomtomdesign-image-optim-for-cdn' ); ?></p>
	<?php
}

/**
 * Enhanced field callback
 */
function tomtom_image_optim_enhanced_field_callback() {
	$enhanced = get_option( 'keycdn_webp_enhanced', true );
	?>
	<label>
		<input type="checkbox" name="keycdn_webp_enhanced" value="1" <?php checked( $enhanced, 1 ); ?> />
		<?php esc_html_e( 'Enable enhanced mode (handles srcset attributes)', 'tomtomdesign-image-optim-for-cdn' ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'Enhanced mode processes responsive images with srcset attributes for complete WebP coverage.', 'tomtomdesign-image-optim-for-cdn' ); ?></p>
	<?php
}

/**
 * Debug field callback
 */
function tomtom_image_optim_debug_field_callback() {
	$debug = get_option( 'keycdn_webp_debug', false );
	?>
	<label>
		<input type="checkbox" name="keycdn_webp_debug" value="1" <?php checked( $debug, 1 ); ?> />
		<?php esc_html_e( 'Enable debug mode (shows conversion info in HTML comments)', 'tomtomdesign-image-optim-for-cdn' ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'Debug mode adds HTML comments showing which images were converted. Useful for troubleshooting.', 'tomtomdesign-image-optim-for-cdn' ); ?></p>
	<?php
}

/**
 * Quality field callback
 */
function tomtom_image_optim_quality_field_callback() {
	$quality = get_option( 'keycdn_webp_quality', 80 );
	?>
	<input type="number" id="keycdn_webp_quality" name="keycdn_webp_quality" value="<?php echo esc_attr( $quality ); ?>" min="1" max="100" class="small-text" />
	<p class="description"><?php esc_html_e( 'WebP quality setting (1-100). Higher values mean better quality but larger file sizes.', 'tomtomdesign-image-optim-for-cdn' ); ?></p>
	<?php
}

