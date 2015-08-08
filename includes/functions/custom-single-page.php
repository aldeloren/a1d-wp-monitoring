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
 * @uses get_the_ID()
 * @uses the_post()
 * @uses get_the_permalink()
 * @uses get_the_title()
 *
 * @returns string html
 */

function a1dmonitor_build_sidenav( $monitors ) {

  global $post;
  $current = $post->ID;
  $nav = "<ul class='nav nav-sadebar'>";
  while ( $monitors->have_posts() ): $monitors->the_post();
    if ( get_the_ID() == $current ) {
      $nav .= "<li class='a1dmonitor-nav-item active'><a href='" . get_the_permalink() . "'>" . get_the_title() . "</a></li>";
    } else {
      $nav .= "<li class='a1dmonitor-nav-item'><a href='" . get_the_permalink() . "'>" . get_the_title() . "</a></li>";
    }
  endwhile;

  wp_reset_query();
  $nav .= "</ul>";
  echo $nav;
}

/*
 * Generate the main body content
 * call UR API to retrieve status
 *
 * @uses wp_remote_get()
 * @uses get_post_meta()
 * @uses get_option()
 * @uses simplexml_load_string()
 * @uses get_post_type_object()
 * @uses get_post_type()
 * @uses get_home_url()
 *
 * @returns string html,javascript
 */

function a1dmonitor_build_main() {

  global $post;
  $meta = get_post_meta($post->ID);
  $options = get_option( 'a1dmonitor_monitoring_options' );
  $api_key = $options['api_key'];
  $info = array();

  if ( array_key_exists( 'a1dmonitor_ur_id', $meta ) ) {
    $monitor_id = $meta['a1dmonitor_ur_id'][0];
    $monitor_url = "https://api.uptimerobot.com/getMonitors?apiKey={$api_key}&monitors={$monitor_id}&responseTimes=1&responseTimesAverage=86400";
    $response = wp_remote_get( $monitor_url );
    $xml = simplexml_load_string( $response['body'] );
    $monitor = $xml->monitor;
    $post_type_data = get_post_type_object( get_post_type() );
    $post_slug = get_home_url()  . '/' . $post_type_data->rewrite['slug'] . '/';
    $status = array(
      'text' => '',
      'class' => '',
      'image' => ''
    );

    $status_int = intval( $monitor->attributes()->status );
    if ( 3 > $status_int ) {
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
      'response_time' => '0',
      'uptime' => $monitor->attributes()->alltimeuptimeratio,
      'url' => $meta['a1dmonitor_site_url'][0],
      'title' => get_the_title()
    );
    if ( isset( $monitor->responsetime->attributes()->value ) ) {
      $info['response_time'] = $monitor->responsetime->attributes()->value;
    }
    $image = A1DMONITOR_URL . 'images/' . $info['status']['image'];
    $remote_info = a1dmonitor_determine_wordpress_info( $info['url'] );

    $content = "<div class='col-sm-12'><h1>Site: {$info['title']} <span class='a1dmonitor-site-title-url'>({$info['url']})</span></h1></div>";
    $content .= "<div class='col-sm-6 a1dmonitor-status-container {$info['status']['class']}'><h3 class='text-center'>Status: {$info['status']['text']}</h3>";
    $content .= "<h4 class='text-center'>Uptime: {$info['uptime']}%</h4>";
    $content .= "<img class='a1dmonitor-status-image' src='{$image}'></div>";
    $content .= "<div class='col-sm-6 a1dmonitor-container'><h3 class='text-center'>Response Time</h3>";
    $content .= "<input data-angleoffset='-125' data-anglearc='250' data-fgcolor='#66EE66'data-min='0' data-max='1000' data-readOnly=true value='{$info['response_time']}' class='a1dmonitor-response-time'></div>";
    $content .= "<div class='col-sm-6'>";
    if ( $remote_info['version'] ) {
      $content .= "<p><strong>Wordpress version:</strong> {$remote_info['version']}</p>";
      $content .= "<p><strong>Description:</strong> {$remote_info['description']}</p>";
    }
    $content .= "<p><a href='{$post_slug}'><div class='a1dmonitor-button btn btn-primary'>View all monitors</div></a></p>";
    $content .= "<p><a href='{$info['url']}' target='_blank'><div class='a1dmonitor-button btn btn-primary'>Visit site</div></a></p>";
    $content .= "</div></div>";
    echo $content;
  }
}

/*
 * Attempt to determine remote WordPress Version
 *
 * TODO scape source for most recent version
 *
 * @uses wp_remote_get()
 * @uses wp_remote_retrieve_body()
 * @uses is_wp_error()
 * @uses simplexml_load_string()
 *
 * @returns string wordpress version
 */

function a1dmonitor_determine_wordpress_info( $site_url ){

  $feed_url = $site_url . "?feed=rss";
  $response = wp_remote_get( $feed_url );
  if ( is_wp_error( $response ) ) {
    return;
  }
  libxml_use_internal_errors(true);
  $xml = simplexml_load_string( wp_remote_retrieve_body( $response ) );

  if ( is_wp_error( $xml ) ) {
    return;
  }

  if ( $xml ) {
    $generator_string = '';
    if ( $xml->channel->generator ){
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
}
