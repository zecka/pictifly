<?php
/*
Plugin Name: Pictifly
Plugin URI:
GitHub Plugin URI: zecka/pictifly
Description: A plugin to generate image size on the fly and create picture html tag
Author: Robin Ferrari (Octree)
Author URI: https://octree.ch
Text Domain: pictifly
Version: 0.2
*/

define( 'PF_VERSION', '0.2' );
define( 'PF_URL', substr(plugin_dir_url( __FILE__ ), 0, -1) );
define( 'PF_PATH', substr(plugin_dir_path( __FILE__ ), 0, -1) );

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'pf_add_plugin_page_settings_link');
function pf_add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'options-general.php?page=pictifly' ) .
		'">' . __('Settings') . '</a>';
	return $links;
}


require_once('inc/plugin-activation.php');


require_once('vendor/autoload.php');

require('kernel/pf_sizes_store.php');
require('kernel/classes/pf_image.php');
require('kernel/classes/pf_breakpoint.php');
require('kernel/classes/pf_size.php');

require('kernel/helpers.php');
require('kernel/pf_configs.php');
require('kernel/pf_functions.php');
require('kernel/hooks/wp-attachment-hooks.php');

require('kernel/enqueue_scripts.php');
require('kernel/media-library/pf_keypoint_field.php');
require('kernel/media-library/wpml_support.php');

require('kernel/options/option-page.php');
require('kernel/options/option-page-regenerate.php');
