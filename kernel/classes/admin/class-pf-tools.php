<?php
class PF_Tools{
    public function __construct(){
        
    }
    public function run(){
        $this->ajax();
    }
    public function ajax(){
        new PF_Ajax('pf_ajax_delete_all_image', [$this, 'delete_all_pictifly_images_ajax']);
        new PF_Ajax('pf_ajax_delete_all_compressed', [$this, 'delete_compressed_images_ajax']);
        new PF_Ajax('pf_ajax_delete_all_past_data', [$this, 'delete_old_pictifly_data']);
    }
    public static function delete_all_pictifly_images(){
        $configs = pf_configs();
        PF_Helper::pf_remove_dir_with_content($configs['resize_path']);
    }
    public function delete_all_pictifly_images_ajax(){
        self::delete_all_pictifly_images();
        wp_send_json_success();
        wp_die();
    }
    public static function delete_compressed_images(){
        $configs = pf_configs();
        $i = 0;
        $folders = PF_Helper::pf_get_folders_in_dir($configs['resize_path']);
        foreach ($folders as $folder) {
            $min_folder = $folder . '/min';
            if (is_dir($min_folder)) {
                PF_Helper::pf_remove_dir_with_content($min_folder);
                $i++;
            }
        }
        return $i;
    }
    public function delete_compressed_images_ajax(){
        $i = self::delete_compressed_images();
        wp_send_json_success(['count' => $i]);
        wp_die();
    }
    public function delete_old_pictifly_data(){

        $args = [
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
        ];

        $the_query     = new WP_Query($args);

        if ($the_query->have_posts()):
            while ($the_query->have_posts()): $the_query->the_post();
                delete_post_meta(get_the_ID(), 'pf_files');
                delete_post_meta(get_the_ID(), 'pf_files_min');
            endwhile;
        endif;
        wp_reset_query();
        wp_send_json_success();
        wp_die();
    }
  
}

