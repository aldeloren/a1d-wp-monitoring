<?php
namespace TenUp\A1D_Monitoring_and_Management\Core;

/* 
 * Build admin dashboard
 * Uses the Uptime Robot API (https://uptimerobot.com/api) to gather and
 * create new monitors for individual sites
 *
 */

/*
 * Register plugin administrative styles
 *
 * @uses wp_register_style()
 * @uses wp_enqueue_style()
 *
 * @returns void
 */

function a1dmonitor_load_admin_styles() {

  wp_register_style( 'a1dmonitor_admin_css' , A1DMONITOR_URL . '/assets/css/ad-wp-monitoring-and-management-admin.css', false );
  wp_enqueue_style( 'a1dmonitor_admin_css' );
} 


/*
 * Generate available plugin settings
 *
 * @uses register_settings()
 * @uses add_settings_section()
 * @uses add_settings_field()
 *
 * @return void
 */

function a1dmonitor_settings_init() {

  register_setting(
    'a1dmonitor_monitoring_options',
    'a1dmonitor_monitoring_options',
    __NAMESPACE__ . '\a1dmonitor_options_validation'
  );  
  add_settings_section(
    'a1dmonitor_monitoring_settings',
    'Monitoring Settings',
    __NAMESPACE__ . '\a1dmonitor_settings_info',
    'a1d-monitoring'
  );
  add_settings_field(
    'a1dmonitor_api_key',
    'Uptime Robot API key',
    __NAMESPACE__ . '\a1dmonitor_settings_api_key', 
    'a1d-monitoring',
    'a1dmonitor_monitoring_settings'
  );
}

/*
 * Display Plugin info and helper text
 *
 * @uses get_option()
 *
 * @returns string html
 */

function a1dmonitor_settings_info() {

    $options = get_option( 'a1dmonitor_monitoring_options' );
    $info = '';
    $is_registered = false;
    if ( array_key_exists( 'api_key', $options ) ) {
      if ( true === is_uptime_robot_api_key_is_valid( $options['api_key'] ) ) {
        $is_registered = true;
      } 
    }; 

  if ( false == $is_registered ) {
    $info .= "<p class='a1dmonitor_important'>This plugin utilizes the Uptime Robot service to monitor your WordPress site(s). Please register with the site <a href='https://uptimerobot.com/#newUser' target='_blank'>here</a> by clicking the 'Sign-up (free)' button. Once registered, please retreive your Main API key <a href='https://uptimerobot.com/dashboard#mySettings' target='_blank'>here</a>, and scrolling to the API Settings section.";
  }
}

/*
 * Accept user API input
 *
 *@return string 32 char API key
 */

function a1dmonitor_settings_api_key() {
  
  $options = get_option( 'a1dmonitor_monitoring_options' );
  if ( array_key_exists( 'api_key', $options ) ) {
    $api_key_option = "<input id='a1dmonitor_api_key' type='text' name='a1dmonitor_monitoring_options[api_key]' value='{$options['api_key']}' required  title='Please enter a valid 32 character Main API key'>";
  } else {
    $api_key_option = "<input id='a1dmonitor_api_key' type='text' name='a1dmonitor_monitoring_options[api_key]' value='' required title='Please enter a valid 32 character Main API key'>";
  }
  echo $api_key_option;
}

/*
 * Validates Uptime Robot API key
 *
 * @returns  bool
 */

function is_uptime_robot_api_key_is_valid( $key ) {

  $key = trim( $key );
  $validity = false;
  if ( ctype_alnum( $key ) && 32 === strlen( $key ) ) {
    $validity = true;
  }
  return $validity;
}

/*
 * Generate Admin dashboard
 *
 * @uses add_options_page()
 *
 * @return void
 */

function register_a1dmonitor_admin() {

  add_menu_page( 'A1D Monitoring and Mangagement', 'Monitoring', 'manage_options', 'a1d-monitoring', '\TenUp\A1D_Monitoring_and_Management\Core\a1dmonitor_dashboard', 'dashicons-desktop', 62 );
}

/* 
 * Build HTMl dashboard
 *
 * @return string html
 */

function a1dmonitor_dashboard() {

  $admin_template = A1DMONITOR_INC . 'templates/admin.php';
  include_once( $admin_template );
}
