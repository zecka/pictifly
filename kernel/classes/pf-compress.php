<?php
class PF_Compress{
    public function __construct(){
    }
    public function run(){
        $this->ajax();
    }
    public function ajax(){
        new PF_Ajax('pf_get_attachments', [$this, 'get_attachments']);
        new PF_Ajax('pf_upload_compressed_files', [$this, 'upload_files']);
    }
    
    public static function filepath_info_for_compression($fullpath, $info=false){
        $configs     = pf_configs();
        $upload_path = $configs['resize_path'];

        $dir      = explode('/', $fullpath);
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
                $attachment_id = pf_get_id_in_default_lang(get_the_ID());
                $array_files = pf_get_attachment_master_files($attachment_id);
              
                foreach($array_files as $file){
                    $url = $configs['resize_url'].$attachment_id.'/'.$file;                   
                    $fullpath = $configs['resize_path'].$attachment_id.'/'.$file;                   
                    $fileinfo = $this->filepath_info_for_compression($fullpath);
                    $dir      = $fileinfo['path'];
                    $fullpath_compression = $fileinfo['fullpath'];
                    // Prepare dir for compression
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    if(!file_exists($fullpath_compression) && file_exists($fullpath) ){
                        $attachments_data[$i]['files'][] = [
                            'file'  => $fullpath,
                            'url'   => $url,
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
        $attachments_ids = $_POST['attachments_ids'];
        $files = [];
        foreach($_FILES as $key => $file){
            $files[] = [
                'blob' => $file,
                'attachment_id' =>   $attachments_ids[$key],
                'path' => $files_path[$key],
            ];
        }
        
        foreach($files as $file){
            $fileinfo = $this->filepath_info_for_compression($file['path']);
            $dir = $fileinfo['path'];
            $fullpath = $fileinfo['fullpath'];

            if(file_exists($fullpath)){
                continue;
            }
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            move_uploaded_file($file['blob']['tmp_name'], $fullpath);
                        
        }
        

        wp_send_json_success(['post' => $_POST, 'files'=>$_FILES, 'myfiles' => $files]);
    }
}

$pf_compress = new PF_Compress();
$pf_compress->run();