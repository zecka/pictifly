<?php
class PF_Admin{
    public function __construct(){
    }
    public function run(){
        add_action('admin_menu', array($this, 'admin_menu'));
    }  
    public function admin_menu(){
       add_menu_page(
            __('Pictifly', PF_SLUG),
            __('Pictifly', PF_SLUG),
            PF_CAPABILITY,
            PF_SLUG,
             "", // Callback, leave empty
            'dashicons-format-gallery',
            71  // Position
        );
        $sub_menus = [
            [
                'page_title' => __('Settings', PF_SLUG),
                'menu_title' => __('Settings', PF_SLUG),
                'slug'       => false
            ],
            [
                'page_title' => __('Regenerate images', PF_SLUG),
                'menu_title' => __('Regenerate images', PF_SLUG),
                'slug'       => 'regenerate'
            ],
            [
                'page_title' => __('Sitemap Crawler', PF_SLUG),
                'menu_title' => __('Sitemap Crawler', PF_SLUG),
                'slug'       => 'crawler'
            ],
            [
                'page_title' => __('Compress images', PF_SLUG),
                'menu_title' => __('Compress images', PF_SLUG),
                'slug'       => 'compress'
            ],
            [
                'page_title' => __('Tools', PF_SLUG),
                'menu_title' => __('Tools', PF_SLUG),
                'slug'       => 'tools'
            ]
            
        ];
        $this->register_submenus($sub_menus);


    }
    function register_submenus($sub_menus){
        foreach($sub_menus as $sub){
            $slug = "";
            if($sub['slug']){
                $slug = '-'.$sub['slug'];
            }
            $hook = add_submenu_page(
                PF_SLUG, // PARENT
                $sub['page_title'], // PAGE TITLE
                $sub['menu_title'], // MENU TITLE
                PF_CAPABILITY, // CAPABILITIES
                PF_SLUG.$slug, // SLUG
                array($this, 'setting_app') // CALLBACK
            );
            add_action("load-$hook", array($this, 'setting_page_load'));
        }
    }
    function setting_page_load(){
        add_action('admin_footer', array($this, 'admin_footer'));
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
    }
    function setting_app(){
        echo '<div id="app"></div>';
    }
    public function admin_footer(){
        // Read JSON file
        $json = file_get_contents(PF_PATH . '/admin/dist/asset-manifest.json');
        //Decode JSON
        $assets   = json_decode($json, true);
        $dist_url = PF_URL . '/admin/dist/';

        echo '<script src=' . $dist_url . $assets['chunk-vendors.js'] . '></script>';
        echo '<script src=' . $dist_url . $assets['app.js'] . '></script>';
    }
    public function admin_enqueue(){
        // Read JSON file
        $json = file_get_contents(PF_PATH . '/admin/dist/asset-manifest.json');
        //Decode JSON
        $settings = PF_Settings::getInstance();


        $assets   = json_decode($json, true);
        $quality  = $settings->options['compression_quality'];
        $dist_url = PF_URL . '/admin/dist/';
        wp_register_script('pf_vue_admin', $dist_url . $assets['app.js'], '', '', true);
        wp_localize_script('pf_vue_admin', 'pictifly', array(
            'version'   => PF_VERSION,
            'post_types_regenerate' => PF_Regenerate::get_post_types(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pictifly_admin'),
            'distUrl' => $dist_url,
            'publicPath' => PF_URL . '/admin/public/',
            'quality'  => $quality,
            'max_file_uploads' => ini_get('max_file_uploads'),
            'options'   => $settings->options,
        ));
        
        wp_enqueue_script('pf_vue_admin');
    }
    public function admin_head(){
        $json = file_get_contents(PF_PATH.'/admin/dist/asset-manifest.json');
        //Decode JSON
        $assets = json_decode($json,true);
        $assets = array_reverse($assets);
        $dist_url =  PF_URL.'/admin/dist/';
        foreach($assets as $asset){
            $asset_part = explode('/', $asset);
            $extension = $asset_part[0];
            $file_name = $asset_part[1];
            $file_url = $dist_url.$asset;
            $name = explode('.',$file_name)[0];
            $preload = false;
            // example chunk-vendors.956c5594.js.map
            // Skip map file
            if(isset(explode('.',$file_name)[3])) {
                continue;
            }
            if( in_array($name, ['app','chunk-vendors']) ){
                $preload = true;
            }

            if($extension==='css'){
                if($preload){
                    echo '<link href='.$file_url.' rel=preload as=style>';
                }else{
                    echo '<link href='.$file_url.' rel=prefetch>';
                }
            }
            if($extension==='js'){
                if($preload){
                    echo '<link href='.$file_url.' rel=preload as=script>';
                }else{
                    echo '<link href='.$file_url.' rel=prefetch>';
                }
            }

        }
        if(isset( $assets['chunk-vendors.css'] )){
            echo '<link href='.$dist_url.$assets['chunk-vendors.css'].' rel=stylesheet>';
        }
        if(isset($assets['app.css'])){
            echo '<link href='.$dist_url.$assets['app.css'].' rel=stylesheet>';
        }
    }
    
}

