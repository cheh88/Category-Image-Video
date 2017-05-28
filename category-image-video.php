<?php
/**
 * Plugin Name: Category Image Video
 * Plugin URI:  https://github.com/cheh/
 * Description: This is a WordPress plugin that adds two additional fields to the taxonomy "Category".
 * Version:     1.0.0
 * Author:      Dmitriy Chekhovkiy <chehovskiy.dima@gmail.com>
 * Author URI:  https://github.com/cheh/
 * Text Domain: category-image-video
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * @package   Category_Image_Video
 * @author    Dmitriy Chekhovkiy <chehovskiy.dima@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/cheh/
 * @copyright 2017 Dmitriy Chekhovkiy
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	add_action( 'admin_notices', 'civ_fail_wp_version' );

} else {

	// Public-Facing Functionality.
	require_once( plugin_dir_path( __FILE__ ) . 'includes/class-category-image-video-tools.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'public/class-category-image-video.php' );
	add_action( 'plugins_loaded', array( 'CIV_Plugin', 'get_instance' ) );

	// Dashboard and Administrative Functionality.
	if ( is_admin() ) {

		require_once( plugin_dir_path( __FILE__ ) . 'admin/class-category-image-video-admin.php' );
		add_action( 'plugins_loaded', array( 'CIV_Plugin_Admin', 'get_instance' ) );
	}
}

add_action( 'plugins_loaded', 'civ_load_plugin_textdomain' );
/**
 * Load the plugin text domain for translation.
 *
 * @since 1.0.0
 */
function civ_load_plugin_textdomain() {
	load_plugin_textdomain( 'category-image-video', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Show in WP Dashboard notice about the plugin is not activated.
 *
 * @since 1.0.0
 */
function civ_fail_wp_version() {
	$message = esc_html__( 'This plugin requires WordPress version 4.4, plugin is currently NOT ACTIVE.', 'category-image-video' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );

	echo wp_kses_post( $html_message );
}
