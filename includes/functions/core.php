<?php
namespace TenUp\A1D_Monitoring_and_Management\Core;

// Include administrastive functions
include_once( __DIR__ . '/admin.php' );
// Include custom post functions
include_once( __DIR__ . '/custom-page.php' );

/**
 * Default setup routine
 *
 * @uses add_action()
 * @uses do_action()
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'init' ) );
  add_action( 'init', $n( 'a1dmonitor_register_custom_post' ) );
  add_action( 'admin_menu', $n( 'register_a1dmonitor_admin' ) );
  add_action( 'admin_menu', $n( 'a1dmonitor_settings_init' ) );
  add_action( 'admin_enqueue_scripts', $n( 'a1dmonitor_load_admin_styles' ) );

	do_action( 'a1dmonitor_loaded' );
	do_action( 'register_a1dmonitor_admin' );
}

/**
 * Registers the default textdomain.
 *
 * @uses apply_filters()
 * @uses get_locale()
 * @uses load_textdomain()
 * @uses load_plugin_textdomain()
 * @uses plugin_basename()
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'a1dmonitor' );
	load_textdomain( 'a1dmonitor', WP_LANG_DIR . '/a1dmonitor/a1dmonitor-' . $locale . '.mo' );
	load_plugin_textdomain( 'a1dmonitor', false, plugin_basename( A1DMONITOR_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @uses do_action()
 *
 * @return void
 */
function init() {
	do_action( 'a1dmonitor_init' );
}

/**
 * Activate the plugin
 *
 * @uses init()
 * @uses flush_rewrite_rules()
 *
 * @return void
 */
function activate() {
	// First load the init scripts in case any rewrite functionality is being loaded
	init();
	flush_rewrite_rules();
}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {

}
