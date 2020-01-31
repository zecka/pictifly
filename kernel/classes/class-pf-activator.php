<?php

/**
 * Fired during plugin activation
 *
 * @link       https://robinferrari.ch
 * @since      0.4.0
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.4.0
 * @author     Robin Ferrari <alert@robinferrari.ch>
 */
class PF_Activator {

    /**
     * @since    0.4.0
     */
    public static function activate() {
        if(!get_option('pictifly_settings')){
            update_option('pictifly_settings', PF_Helper::default_settings());
        }
    }

}
