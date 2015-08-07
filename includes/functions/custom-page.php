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
    'hierarchical' => true,
    'menu_position' => 20,
    'supports' => array( 'title' )
  );
  register_post_type( 'Monitor', $args );
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
