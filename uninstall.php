<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

/*
 * Remove plugin options 
 *
 * @uses delete_option()
 *
 * @returns void
 */

function a1dmonitor_remove_plugin() {
 
  delete_option( 'a1dmonitor_monitoring_options' ); 
}

a1dmonitor_remove_plugin();
