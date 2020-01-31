<?php

class PF_Settings {
    /**
     * @var PF_Setings
     * @access private
     * @static
     */
    private static $_instance = null;

    public $options;
    /**
     * Constructeur de la classe
     *
     * @param void
     * @return void
     */
    private function __construct() {
        $this->set_options();
        $this->register_ajax_action();
    }
    
    private function set_options() {
        $options = get_option('pictifly_settings');
        $options = (is_array($options)) ? $options : [];
        $default_options = PF_Helper::default_settings();
        // override default option with saved options
        $options = array_merge($default_options, $options);
        $this->options = apply_filters( 'pf_options', $options );
    }

    /**
     * Méthode qui crée l'unique instance de la classe
     * si elle n'existe pas encore puis la retourne.
     *
     * @param void
     * @return PF_Settings
     */
    public static function getInstance() {

        if (is_null(self::$_instance)) {
            self::$_instance = new PF_Settings();
        }

        return self::$_instance;
    }

    public function register_ajax_action() {
        new PF_Ajax('pf_save_plugin_settings', [$this, 'save_plugin_settings']);
    }
    public function save_plugin_settings() {
        $imgix_url = esc_url_raw($_POST['imgix_url']);
        if($imgix_url){
            $imgix = $_POST['imgix'] === "true";
        }else{
            $imgix = false;
        }
        $new_options = [
            'pictifly_quality' => intval($_POST['pictifly_quality']),
            'compression_quality' => intval($_POST['compression_quality']),
            'lazyload'         => $_POST['lazyload'] == "true",
            'imgix'            => $imgix,
            'imgix_url'        => $imgix_url,
        ];

        // Check if quality is possible
        if($new_options['pictifly_quality'] > 100 || $new_options['pictifly_quality'] < 0){
            $new_options['pictifly_quality'] = 100;
        }
        if($new_options['compression_quality'] > 100 || $new_options['compression_quality'] < 0){
            $new_options['compression_quality'] = 100;
        }

        // check if compression quality have change
        if($this->options['compression_quality'] !== $new_options['compression_quality']){
            PF_Tools::delete_compressed_images();
        }
        if($this->options['pictifly_quality'] !== $new_options['pictifly_quality']){
            PF_Tools::delete_all_pictifly_images();
        }


        update_option('pictifly_settings', $new_options);



        wp_send_json_success(['options'=>$new_options]);
    }

}
