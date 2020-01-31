<?php 
class Pictifly{
/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.4.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.4.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.4.0
	 */
	public function __construct() {
		if ( defined( 'PF_VERSION' ) ) {
			$this->version = PF_VERSION;
		} else {
			$this->version = '0.4.0';
		}
		$this->plugin_name = PF_SLUG;

		$this->load_dependencies();
    }
    
    private function load_dependencies() {
        require_once 'inc/plugin-activation.php';
        require_once 'vendor/autoload.php';
        require_once 'kernel/pf_sizes_store.php';
        /**
         * Image related class
         */
        require_once 'kernel/classes/class-pf-image.php';
        require_once 'kernel/classes/class-pf-breakpoint.php';
        require_once 'kernel/classes/class-pf-size.php';
        /**
         * Admin related class
         */
        require_once 'kernel/classes/admin/class-pf-ajax.php';
        require_once 'kernel/classes/admin/class-pf-settings.php';
        require_once 'kernel/classes/admin/class-pf-admin.php';
        require_once 'kernel/classes/admin/class-pf-compress.php';
        require_once 'kernel/classes/admin/class-pf-regenerate.php';
        require_once 'kernel/classes/admin/class-pf-tools.php';
        
        // NEED TO REFRACTO FILE BELOW
        require_once 'kernel/pf_configs.php';
        require_once 'kernel/pf_functions.php';
        require_once 'kernel/hooks/wp-attachment-hooks.php';
        require_once 'kernel/enqueue_scripts.php';
        require_once 'kernel/media-library/pf_keypoint_field.php';
        require_once 'kernel/media-library/wpml_support.php';

    }

    public function run(){
        $pf_settings = PF_Settings::getInstance();
        $pf_settings->register_ajax_action();

        $my_admin = new PF_Admin();
        $my_admin->run();

        $pf_tools = new PF_Tools();
        $pf_tools->run();

        $pf_regenerate = new PF_Regenerate();
        $pf_regenerate->run();

        $pf_compress = new PF_Compress();
        $pf_compress->run();

        
    }

}
