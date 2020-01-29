<?php
class PF_Admin{
    public function __construct(){
    }
    public function run(){
        add_action('admin_menu', array($this, 'admin_menu'));
        $this->ajax();
    }  
    public function admin_menu(){
        add_menu_page(
            __('Pictifly', PF_SLUG),
            __('Pictifly', PF_SLUG),
            PF_CAPABILITY,
            PF_SLUG,
            '', // Callback, leave empty
            'dashicons-store',
            2// Position
        );
        $hook = add_submenu_page(
             PF_SLUG, // parent page
              __('Compress images', PF_SLUG), // page title
             __('Compress images', PF_SLUG), // menu title
             PF_CAPABILITY, // Capability
             PF_SLUG.'-compress',  // menu slug
             array($this, 'compress_image') // callback
        );
        add_action("load-$hook", array($this, 'compress_page_load'));
    }
    function compress_page_load(){
        add_action('admin_footer', array($this, 'admin_footer'));
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
    }
    function compress_image(){
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
        $assets   = json_decode($json, true);
        $quality  = pf_compress_quality();
        $dist_url = PF_URL . '/admin/dist/';
        wp_register_script('pf_vue_admin', $dist_url . $assets['app.js'], '', '', true);
        
        wp_localize_script('pf_vue_admin', 'pictifly', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pictifly_admin'),
            'distUrl' => $dist_url,
            'publicPath' => PF_URL . '/admin/public/',
            'quality'  => $quality,
            'max_file_uploads' => ini_get('max_file_uploads')
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
    public function ajax(){
        new PF_Ajax('pf_get_attachments', [$this, 'get_attachments']);
        new PF_Ajax('pf_upload_compressed_files', [$this, 'upload_files']);
    }
    
    private function filepath_info_for_compression($fname, $info=false){
        $configs     = pf_configs();
        $upload_path = $configs['resize_path'];

        $dir      = explode('/', $fname);
        $filename = array_pop($dir);
        $dir      = implode('/', $dir);
        $dir      = str_replace($configs['resize_path'], "", $dir);
        $dir      = $upload_path . $dir . '/min/';
        $fname    = $filename;

        $quality = pf_compress_quality();
        $pathinfo = pathinfo($fname);
        if (isset($pathinfo['extension'])) {
            $fname = str_replace(
                '.' . $pathinfo['extension'], // search extension p.ex '.jpg'
                'min' . $quality . '.' . $pathinfo['extension'], // replace by min+quality+extenstion p.ex(min80.jpg)
                $fname // in filename
            );
        }

        $infos =  [
            'path'=>$dir, 
            'filename'=>$fname,
            'fullpath' => $dir.$fname
        ];
        if($info){
            return $infos[$info];
        }else{
            return $infos;
        }
    }
    public function get_attachments(){

        $configs        = pf_configs();
        $current_item          = $_POST['current_item'];
        $attachments_data = [];
        // https://gist.github.com/luetkemj/2023628
        $args = [
            'post_type'         => 'attachment',
            'post_mime_type'    => 'image',
            'post_status'       => 'inherit', 
            'posts_per_page'    => -1,
            'orderby'           => 'ID',
            'order'             => 'ASC',
        ];
     
        $the_query     = new WP_Query($args);
        $post_count = $the_query->post_count;
        
        $i = 0; $j=0;
        if ($the_query->have_posts()):
            while ($the_query->have_posts()): $the_query->the_post();
                $j++;
                if($current_item >= $j){
                    continue;
                }
                $current_item++;
                error_log('process: '. $current_item);
                $attachment_id = get_the_ID();

                $array_files = get_post_meta($attachment_id, 'pf_files', true);
                if(!is_array($array_files)){
                    continue;
                }
                foreach($array_files as $key=>$file){
                    $url = $file;
                    $url = str_replace($configs['resize_path'], $configs['resize_url'], $url);
                    
                    $fullpath_compression = $this->filepath_info_for_compression($file, 'fullpath');
                    if(!file_exists($fullpath_compression) && file_exists($file) ){
                        $attachments_data[$i]['files'][] = [
                            'file'  => $file,
                            'url'   => $url,
                            'key'   => $key,
                            'id'    => $attachment_id            
                        ];
                    }
                }
                $i++;

                if (count($attachments_data) === 0 && $current_item < $post_count) {
                    // No attachmnent need to be compressed
                    // So we can switch to next item before send callback to jquery
                    // To avoid creat useless request between back and front
                    $i=0;
                    continue;
                } else{
                    wp_send_json_success([
                        'attachments'  => $attachments_data,
                        'post_count'   => $post_count,
                        'current_item' => (int) $current_item,
                    ]);
                }   
      
                die();
            endwhile;
        endif;
        wp_reset_query();

        wp_send_json_success([
            'attachments'  => [],
            'post_count'   => $post_count,
            'current_item' => $current_item,
        ]);
        
    }
    public function upload_files(){
        
        $files_path   = $_POST['files_path'];
        $files_iswebp = $_POST['files_iswebp'];
        $files_keys = $_POST['files_keys'];
        $attachments_ids = $_POST['attachments_ids'];
        $files = [];
        foreach($_FILES as $key => $file){
            $files[] = [
                'blob' => $file,
                'attachment_id' =>   $attachments_ids[$key],
                'path' => $files_path[$key],
                'key'  => $files_keys[$key],
                'webp' => ($files_iswebp[$key]=="true") ? true :  false,
            ];
        }
        
        foreach($files as $file){
            $attachment_id = $file['attachment_id'];
            $is_webp = $file['webp'];
            $array_files = get_post_meta($attachment_id, 'pf_files_min', true);
            if (!is_array($array_files)) {
                $array_files = [];
            }
            
            $fileinfo = $this->filepath_info_for_compression($file['path']);
            $dir = $fileinfo['path'];
            $fname = $fileinfo['fullpath'];

            $file_key = ($is_webp) ? $file['key'].'webp' : $file['key'];
            // add quality to filename
            $fname = ($is_webp) ? $fname . '.webp' : $fname;

            
            if(isset($array_files[$file_key])){
                // The file for given size is already minified
                // but maybe the quality level is not the same
                // So check in array_files if the file name has the same name of our new file
                if($array_files[$file_key] === $fname ){
                    // the file in our array file is the same as our new file
                    // So check we can just skip the upload for this file
                    if(file_exists($fname)){
                        continue;
                    }
                }else{
                    // We gonna create a new file for this size key
                    // We can therefore delete the file corresponding to this key
                    if (file_exists($array_files[$file_key])) {
                        wp_delete_file($array_files[$file_key]);
                    }
                }
            }
            

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
           
            move_uploaded_file($file['blob']['tmp_name'], $fname);
            
            $array_files[$file_key] = $fname;
            update_post_meta($attachment_id, 'pf_files_min', $array_files);
            
        }
        

        wp_send_json_success(['post' => $_POST, 'files'=>$_FILES, 'myfiles' => $files]);
    }
  
    
}
$my_admin = new PF_Admin();
$my_admin->run();
