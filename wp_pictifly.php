<?php
/*
Plugin Name: Pictifly
Plugin URI:
GitHub Plugin URI: zecka/pictifly
Description: A plugin to generate image size on the fly and create picture html tag
Author: Robin Ferrari (Octree)
Author URI: https://octree.ch
Text Domain: pictifly
Version: 0.4.5.5
*/

define( 'PF_SLUG', 'pictifly' );
define( 'PF_CAPABILITY', 'install_plugins');
define( 'PF_VERSION', '0.4.5.5' );
define( 'PF_URL', substr(plugin_dir_url( __FILE__ ), 0, -1) );
define( 'PF_PATH', substr(plugin_dir_path( __FILE__ ), 0, -1) );


require_once 'kernel/classes/class-pf-helper.php';
require_once 'class-pictifly.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wcss-activator.php
 */
function pf_activate_pictifly() {
    require_once PF_PATH.'/kernel/classes/class-pf-activator.php';
    PF_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wcss-deactivator.php
 */
function pf_deactivate_pictifly() {
    require_once PF_PATH.'/kernel/classes/class-pf-deactivator.php';
    PF_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'pf_activate_pictifly');
register_deactivation_hook(__FILE__, 'pf_deactivate_pictifly');



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.4.0
 */
function run_pictifly_plugin() {

    $plugin = new Pictifly();
    $plugin->run();

}
run_pictifly_plugin();
