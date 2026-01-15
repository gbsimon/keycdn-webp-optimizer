<?php
/**
 * Admin Page and Settings
 * Handles admin settings page, admin scripts, and activation notice
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Settings link to plugin actions
 */
function tomtom_image_optim_add_plugin_action_links( $links ) {
	$settings_link = '<a href="' . admin_url( 'options-general.php?page=tomtomdesign-image-optim-settings' ) . '">' . __( 'Settings', 'tomtomdesign-image-optim-for-cdn' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( TOMTOM_IMAGE_OPTIM_PLUGIN_FILE ), 'tomtom_image_optim_add_plugin_action_links' );

/**
 * Add admin menu page
 */
function tomtom_image_optim_add_admin_menu() {
	add_options_page(
		__( 'tom & tom Image Optimization Settings', 'tomtomdesign-image-optim-for-cdn' ),
		__( 'tom & tom Image Optimization', 'tomtomdesign-image-optim-for-cdn' ),
		'manage_options',
		'tomtomdesign-image-optim-settings',
		'tomtom_image_optim_admin_page'
	);
}
add_action( 'admin_menu', 'tomtom_image_optim_add_admin_menu' );

/**
 * Show activation notice
 */
function tomtom_image_optim_activation_notice() {
	if ( get_transient( 'keycdn_webp_activated' ) ) {
		delete_transient( 'keycdn_webp_activated' );
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<strong><?php esc_html_e( 'tom & tom Image Optimization for CDN', 'tomtomdesign-image-optim-for-cdn' ); ?></strong> 
				<?php esc_html_e( 'has been activated!', 'tomtomdesign-image-optim-for-cdn' ); ?>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=tomtomdesign-image-optim-settings' ) ); ?>" class="button button-primary" style="margin-left: 10px;">
					<?php esc_html_e( 'Configure Settings', 'tomtomdesign-image-optim-for-cdn' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'tomtom_image_optim_activation_notice' );

/**
 * Admin page
 */
function tomtom_image_optim_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		
		<?php settings_errors(); ?>
		
		<form method="post" action="options.php">
			<?php
			settings_fields( 'keycdn_webp_settings' );
			do_settings_sections( 'tomtomdesign-image-optim-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Enqueue admin scripts and styles
 */
function tomtom_image_optim_admin_scripts( $hook ) {
	if ( $hook !== 'settings_page_tomtomdesign-image-optim-settings' ) {
		return;
	}
	
	wp_enqueue_style(
		'tomtomdesign-image-optim-admin',
		TOMTOM_IMAGE_OPTIM_PLUGIN_URL . 'assets/css/admin.css',
		array(),
		TOMTOM_IMAGE_OPTIM_VERSION
	);
	
	wp_enqueue_script(
		'tomtomdesign-image-optim-admin',
		TOMTOM_IMAGE_OPTIM_PLUGIN_URL . 'assets/js/admin.js',
		array( 'jquery' ),
		TOMTOM_IMAGE_OPTIM_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'tomtom_image_optim_admin_scripts' );

