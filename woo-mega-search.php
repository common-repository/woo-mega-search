<?php
/**
 * WooCommerce Mega Search
 *
 * Plugin Name: WooCommerce Mega Search
 * Plugin URI: https://pluginrox.com/plugin/woocommerce-mega-search/
 * Description: WooCommerce Mega Search By PluginRox
 * Version: 1.0.1
 * Author: PluginRox
 * Author URI: https://pluginrox.com/
 * License: GPLv2 or later
 * Text Domain: woo-mega-search
 * Domain Path: /languages/
 *
 * @package RoxWCMS
 * @subpackage SearchLite
 *
 */
if( ! function_exists( 'add_action' ) ) {
    header('HTTP/1.0 403 Forbidden');
    die("<h1>Forbidden</h1><br><br><p>Go Away!</p><hr><p>Just Go Away!!!</p>");
}
/**
 * Plugin basename
 * @var string
 */
define( 'ROX_WOO_SEARCH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
/**
 * Plugin Path
 * @var string
 */
define( 'ROX_WOO_SEARCH_LITE_PATH', plugin_dir_path( __FILE__ ) );
/**
 * Plugin URL
 * @var string
 */
define( 'ROX_WOO_SEARCH_LITE_URL', plugins_url( '/', __FILE__ ) );
/**
 * Plugin Template Folder
 * @var string
 */
define( 'ROX_WOO_SEARCH_LITE_TEMPLATES', 'templates' );
/**
 * Plugin Version
 * @var string
 */
define( 'ROX_WOO_SEARCH_VERSION', '1.0.1' );
/**
 * Plugin Activation Callback.
 *
 * @return void
 */
function rox_wcms_activation() {
	if( ! rox_is_woocommerce_activated() ) {
		// @TODO Change/Update this error message
		die( __( 'WooCommerce Mega Search needs WooCommerce. Please, install and active WooCommerce.', 'woo-mega-search' ) );
	}
}
register_activation_hook( __FILE__, 'rox_wcms_activation' );
// Include helper
require_once( ROX_WOO_SEARCH_LITE_PATH . '/includes/helper.php' );
// Include Classes
if( ! class_exists( 'RoxWCMSIcons', FALSE ) ) {
	require_once( ROX_WOO_SEARCH_LITE_PATH . '/includes/class.RoxWCMSIcons.php' );
}
if( ! class_exists( 'RoxWCMSSettings', FALSE ) ) {
	require_once( ROX_WOO_SEARCH_LITE_PATH . '/includes/class.RoxWCMSSettings.php' );
}
if( ! class_exists( 'RoxWCMS' ) ) {
	require_once( ROX_WOO_SEARCH_LITE_PATH . '/includes/class.RoxWCMS.php' );
}
function RoxWCMS() {
    return RoxWCMS::getInstance();
}
$GLOBALS['RoxWCMS']  = RoxWCMS();
// End of file woo-mega-search.php