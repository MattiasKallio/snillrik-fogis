<?php
/*
Plugin Name: Snillrik Fogis
Plugin URI: http://www.snillrik.se/
Description: Snillrik Fogis is a WordPress plugin for getting and displaying info from Fogis about Svensk Fotboll       
Version: 0.1.3
Author: Mattias Kallio
Author URI: http://www.snillrik.se
License: GPL2
 */

DEFINE("SNILLRIK_FOGIS_PLUGIN_URL", plugin_dir_url(__FILE__));
DEFINE("SNILLRIK_FOGIS_DIR", plugin_dir_path(__FILE__));
DEFINE("SNILLRIK_FOGIS_NAME", "snofogis");
DEFINE("SNILLRIK_FOGIS_API_URL", "https://www.svenskfotboll.se/api/");
DEFINE("SNILLRIK_FOGIS_TRANSIENT_TIME", 60*60);
DEFINE("SNILLRIK_FOGIS_USE_TRANSIENTS", true);

require_once SNILLRIK_FOGIS_DIR . 'classes/fogis_api.php';
require_once SNILLRIK_FOGIS_DIR . 'classes/settings.php';
require_once SNILLRIK_FOGIS_DIR . 'classes/shortcodes.php';

// enqueue scripts and styles
function snofo_addCSScripts(){
	wp_register_script 	( 'snillrik-fogis-script', SNILLRIK_FOGIS_PLUGIN_URL . 'js/front.js', ['jquery']);
    wp_enqueue_style 	( 'snillrik-fogis-main', SNILLRIK_FOGIS_PLUGIN_URL . 'css/front.css' );
}
 
add_action('wp_enqueue_scripts', 'snofo_addCSScripts');

// enqueue admin scripts and styles
function snofo_addAdminCSScripts(){
    wp_enqueue_script( 'snillrik-fogis-admin-script', SNILLRIK_FOGIS_PLUGIN_URL . 'js/admin.js',['jquery'] );
    wp_enqueue_style( 'snillrik-fogis-admin', SNILLRIK_FOGIS_PLUGIN_URL . 'css/admin.css' );
}
add_action('admin_enqueue_scripts', 'snofo_addAdminCSScripts');