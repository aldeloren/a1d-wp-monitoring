<?php
namespace TenUp\A1D_Monitoring_and_Management\Core;

/*
 * Return all monitors
 *
 * @uses WP_Query 
 *
 * @returns array
 */

function a1dmonitor_monitors() {

  $args = array(
    'post_type' => 'monitor',
    'post_status' => 'publish',
    'orderby'=> 'title',
    'order' => 'ASC'
  );
  $monitors = new \WP_Query( $args );
  return $monitors;
}

/* 
 * Generate navigation of active monitors
 *
 * @uses
 *
 * @returns void
 */

function a1dmonitor_build_sidenav( $monitors ) {

  global $post;
  $current = $post->ID;
  $nav = "<ul class='nav nav-sadebar'>";
  while( $monitors->have_posts() ): $monitors->the_post();

    if( get_the_ID() == $current ) {
      $nav .= "<li class='a1dmonitor-nav-item active'><a href='" . get_the_permalink() . "'>" . get_the_title() . "</a></li>";
    } else {
      $nav .= "<li class='a1dmonitor-nav-item'><a href='" . get_the_permalink() . "'>" . get_the_title() . "</a></li>";
    }

  endwhile;
  wp_reset_query();

  $nav .= "</ul>";

  echo $nav;
}
