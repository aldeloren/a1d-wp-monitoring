<?php
namespace TenUp\A1D_Monitoring_and_Management\Core;

/*
 * Register custom post type
 * used to display individual monitors and update settings
 *
 * @uses register_post_type()
 *
 * @returns void
 */

function a1dmonitor_register_custom_post() {

  $labels = array(
    'name' => _x( 'Monitor', 'post type general name' ),
    'singular_name' => _x( 'Monitor', 'post type singular name' ),
    'add_new' => _x( 'Add New Monitor', 'Monitor' ),
    'add_new_item' => __( 'Add New Post type' ),
    'edit_item' => __( 'Edit Monitor' ),
    'new_item' => __( 'New Monitor' ),
    'all_items' => __( 'All Monitors' ),
    'view_item' => __( 'View Monitors' ),
    'search_items' => __( 'Search Monitors' ),
    'not_found' => __( 'No Monitors found' ),
    'not_found_in_trash' => __( 'No Monitors found in Trash' ),
    'parent_item_colon' => '',
    'menu_name' => __( 'Monitors' )
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'rewrite' => true,
    'capability_type' => 'page',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => 20,
    'supports' => array( 'title' ),
    'register_meta_box_cb' => __NAMESPACE__ . '\a1dmonitor_add_metaboxes'
  );
  register_post_type( 'Monitor', $args );
}

/*
 * Generate metaboxes
 *
 * @uses add_meta_box()
 *
 * @returns void
 */

function a1dmonitor_add_metaboxes() {

  // Add URL metabox
  add_meta_box( 
    'a1dmonitor_monitor_site_url',
    'Site URL',
    __NAMESPACE__ . '\a1dmonitor_monitor_site_url',
    'monitor'
  );
}

/* 
 * Generate HTML for URL metabox
 *
 * @uses get_post_meta()
 *
 * @returns string html
 */

function a1dmonitor_monitor_site_url() {

  global $post;
  $site_url = get_post_meta( $post->ID, 'a1dmonitor_site_url', true );

  echo "<input type='hidden' name='a1dmonitor_site_url_noncename' id='a1dmonitor_site_url_noncename' value='" . wp_create_nonce( plugin_basename(__FILE__) ) . "' />";
  echo "<input type='url' name='a1dmonitor_site_url' value='{$site_url}' required title='Please enter valid site URL to monitor'  placeholder='Enter valid site URL'class='widefat' />";
}

/*
 * Validat and save user input of custom metabox
 *
 * @returns void
 */

function a1dmonitor_save_meta_values() {

  global $post;
  if( !wp_verify_nonce( $_POST['a1dmonitor_site_url_noncename'], plugin_basename(__FILE__) ) ) {
    return $post->ID;
  } 
  if( !current_user_can( 'edit_post', $post->ID ) ) {
    return $post->ID;
  }
  $monitor_meta['a1dmonitor_site_url'] = esc_url( $_POST['a1dmonitor_site_url'] );

  foreach( $monitor_meta as $key => $value ) { 
    if( $post->post_type == 'revision' ) return; 
    if( get_post_meta( $post->ID, $key, FALSE ) ) { 
      update_post_meta( $post->ID, $key, $value );
    } else { 
      $ur_monitor_id = a1dmonitor_new_monitor( $value );
      add_post_meta( $post->ID, $key, $value );
      add_post_meta( $post->ID, 'a1dmonitor_ur_id', $ur_monitor_id );
    }
    if( !$value ) delete_post_meta( $post->ID, $key );
  }
}

add_action( 'save_post', __NAMESPACE__ . '\a1dmonitor_save_meta_values', 1, 2 );

/*
 * Register a new Uptime Robot monitor
 *
 *
 *@returns string UR id 
 */

function a1dmonitor_new_monitor( $url ) {

  global $post;
  $options = get_option( 'a1dmonitor_monitoring_options' );
  $api_key = $options['api_key'];
  $friendly_name = urlencode( get_the_title( $post->ID ) ); 
  $monitor_url = urlencode( $url );
  $api_url = "https://api.uptimerobot.com/newMonitor?apiKey={$api_key}&monitorFriendlyName={$friendly_name}&monitorURL={$monitor_url}&monitorType=1";


  $response = wp_remote_get( $api_url ); 
  $xml = simplexml_load_string ( $response['body'] );
  // Error ids are within the 200's
  if ( 240 < intval( $xml->attributes()->id ) ) {
   return intval( $xml->attributes()->id ); 
  }
}

/*
 * Register templates for monitor pages
 *
 * @returns template path for individual monitor 
 */

add_filter( 'single_template', __NAMESPACE__ . '\a1dmonitor_register_custom_post_template' );

function a1dmonitor_register_custom_post_template() {

  global $wp_query, $post;
  if ( "monitor" == $post->post_type ) {
    if ( file_exists ( A1DMONITOR_INC . '/templates/monitor-page.php' ) ) {
      return A1DMONITOR_INC . '/templates/monitor-page.php';
    } 
  }
}

/*
 * Register archive template for monitors
 *
 * @uses is_post_type_archive()
 *
 * @returns template for monitor archive
 */

add_filter( 'archive_template', __NAMESPACE__ . '\a1dmonitor_register_custom_archive_template' ); 

function a1dmonitor_register_custom_archive_template() {

  if ( is_post_type_archive( 'monitor' ) ) {
    if ( file_exists ( A1DMONITOR_INC . '/templates/monitor-archive.php' ) ) {
      return A1DMONITOR_INC . '/templates/monitor-archive.php';
    }
  }
}

/*
 * Enqueue JS/CSS for monitor pages and archive only
 *
 * @returns void
 */

function a1dmonitor_page_archive_styles() {

  global $post;
  if ( is_post_type_archive( 'monitor' ) || "monitor" == $post->post_type ) {
    wp_enqueue_style( 'a1dmonitor-custom-styles-reset', A1DMONITOR_URL . '/assets/css/reset.css' );
    wp_enqueue_style( 'a1dmonitor-custom-styles-bootstrap', A1DMONITOR_URL . '/assets/css/bootstrap.min.css', array( 'a1dmonitor-custom-styles-reset' ) );
    wp_enqueue_style( 'a1dmonitor_custom_styles', A1DMONITOR_URL . '/assets/css/ad-monitoring-and-management-custom-page.css', array( 'a1dmonitor-custom-styles-bootstrap' ) );
    wp_enqueue_script( 'a1dmonitor_custom_scripts_jquery', A1DMONITOR_URL . '/assets/js/jquery.min.js' );
    wp_enqueue_script( 'a1dmonitor_custom_scripts_bootstrap', A1DMONITOR_URL . '/assets/js/bootstrap.min.js', array( 'a1dmonitor_custom_scripts_jquery' ) );
  }
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\a1dmonitor_page_archive_styles', 100 );
