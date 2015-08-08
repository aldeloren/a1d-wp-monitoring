<?php
namespace TenUp\A1D_Monitoring_and_Management\Core;

$monitor_ids = array();

/*
 * Return all monitors
 *
 * @uses WP_Query 
 * if ids is true, returns api_ids 
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
 * @uses get_the_ID()
 * @uses the_post()
 * @uses get_the_permalink()
 * @uses get_the_title()
 *
 * @returns string html
 */

function a1dmonitor_build_sidenav( $monitors ) {

  global $post, $monitor_ids;
  $nav = "<ul class='nav nav-sadebar'>";
  while( $monitors->have_posts() ): $monitors->the_post();

    $meta = get_post_meta( $post->ID );
    $monitor_id = $meta['a1dmonitor_ur_id'];
    array_push( $monitor_ids, $monitor_id );
    $nav .= "<li class='a1dmonitor-nav-item'><a href='" . get_the_permalink() . "'>" . get_the_title() . "</a></li>";

  endwhile;

  wp_reset_query();
  $nav .= "</ul>";
  echo $nav;
}

/*
 * Generate the main body content
 * call UR API to retrieve status
 *
 * @returns string html,javascript
 */

function a1dmonitor_build_archive() {
  
  global $post, $monitor_ids;
  $meta = get_post_meta($post->ID);
  $options = get_option( 'a1dmonitor_monitoring_options' );
  $api_key = $options['api_key'];
  $info = array();

  if( array_key_exists( 'a1dmonitor_ur_id', $meta ) ) {
    $monitors_string = '';
    foreach ( $monitor_ids as $monitor_id ) {
      $monitors_string .= $monitor_id[0] . '-';
    };
    $monitors_string = trim( $monitors_string, '-' );
    $monitor_url = "https://api.uptimerobot.com/getMonitors?apiKey={$api_key}&monitors={$monitors_string}&responseTimes=1&responseTimesAverage=86400";
    $response = wp_remote_get( $monitor_url );
    $xml = simplexml_load_string( $response['body'] );
    $monitors = $xml->monitor;
    foreach ( $monitors as $monitor ) {
      $status = array(
        'text' => '',
        'class' => '',
        'image' => ''
      );

      $status_int = intval( $monitor->attributes()->status );
      if( 3 > $status_int ) { 
        $status['text'] = 'Up';
        $status['class'] = 'a1dmonitor-all-clear';
        $status['image'] = 'a1dmonitor-smile.png';
        
      } else {
        $status['text'] = 'Down / Unreachable';
        $status['class'] = 'a1dmonitor-all-bad';
        $status['image'] = 'a1dmonitor-frown.png';
      };
    
      $info = array(
        'status' => $status,
        'response_time' => $monitor->responsetime->attributes()->value,
        'uptime' => $monitor->attributes()->alltimeuptimeratio,
        'url' => $monitor->attributes()->url
      );
      $image = A1DMONITOR_URL . 'images/' . $info['status']['image'];

      $content = "<div class='col-sm-12 a1dmonitor-archive-monitor-item'><h2 class='a1dmonitor-site-archive-url'><a href='{$info['url']}' target='_blank'>{$info['url']}</a></h2>";
      $content .= "<div class='col-sm-6 a1dmonitor-status-container {$info['status']['class']}'><h3 class='text-center'>Status: {$info['status']['text']}</h3>";
      $content .= "<h4 class='text-center'>Uptime: {$info['uptime']}%</h4>";
      $content .= "<img class='a1dmonitor-status-image' src='{$image}'></div>";
      $content .= "<div class='col-sm-6 a1dmonitor-container'><h3 class='text-center'>Response Time</h3>";
      $content .= "<input data-angleoffset='-125' data-anglearc='250' data-fgcolor='#66EE66'data-min='0' data-max='1000' data-readOnly=true value='{$info['response_time']}' class='a1dmonitor-response-time'></div>";
      $content .= "</div>";
      echo $content;
    }
  }
}

/*
 * Attempt to determine remote WordPress Version
 *
 * TODO scape source for most recent version
 *
 * @uses wp_remote_get()
 * @uses is_wp_error()
 *
 * @returns string wordpress version
 */

function a1dmonitor_determine_wordpress_info( $site_url ){

  $feed_url = $site_url . "?feed=rss"; 
  $response = wp_remote_get( $feed_url );
  if( is_wp_error( $response ) ) {
    return;
  }
  $xml = simplexml_load_string( $response['body'] );
  if( is_wp_error( $xml ) ) {
    return;
  }
  $generator_string = '';
  if( $xml->channel->generator ){
    $generator_string = $xml->channel->generator;
  }
  $info = array(
    'version' => '',
    'description' => ''
  );
  $version = preg_match( '^(\d+\.)?(\d+\.)?(\*|\d+)$^', $generator_string, $matches );  
  $info['version'] = $matches[0];
  $info['description'] = $xml->channel->description; 
  return $info; 
}

