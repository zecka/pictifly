<?php
class PF_Regenerate{
    public function __construct(){
        
    }
    public function run(){
        $this->ajax();
    }
    public function ajax(){
        new PF_Ajax('pf_ajax_regenerate_get_items', [$this, 'get_items']);
        new PF_Ajax('pf_ajax_item_image_regenerate', [$this, 'regenerate_images']);
    }
    public function get_items(){
        $items      = [];
        $post_types = $this->get_post_types();
        foreach ($post_types as $post_type) {
            $post_ids = get_posts(array(
                'numberposts' => -1,
                'fields'      => 'ids', // Only get post IDs
                'post_type'   => $post_type,
            ));
            foreach ($post_ids as $post_id) {
                $items[] = [
                    'id'        => $post_id,
                    'post_type' => $post_type,
                ];
            }
        }
        wp_send_json_success($items);
    }
    public function regenerate_images(){
        try {
            $post_type =  esc_html(esc_attr($_POST['post_type']));
            $id =  esc_html(esc_attr($_POST['id']));

            $sizes = pf_get_all_sizes();
            $_pf_regenerated_imgs = [];
            $sizes_name = [];
            foreach($sizes as $size_name => $size){
                if(in_array($post_type, $size['post_type'])){
                    $image_id = get_post_thumbnail_id($id);
                    $_pf_regenerated_imgs[] = pf_img($image_id, $size_name, false);
                    $sizes_name[] = $size_name;
                }
            }
            $image_before = $_pf_regenerated_imgs;
            do_action('pf_post_images_regenerate', $id, $post_type);
            global $_pf_regenerated_imgs;
            if(!$_pf_regenerated_imgs){
                $_pf_regenerated_imgs = $image_before;
            }
            wp_send_json_success(
                [
                    "images"=>$_pf_regenerated_imgs,
                    "sizes" => $sizes_name,
                    "post"	=> get_the_title($id).' ('.$post_type.')'
                ]
            );
        } catch (Exception $e) {
            $msg = 'Exception : '.  $e->getMessage();
            wp_send_json_error($msg);
        }
    }
    public static function get_post_types(){
        $post_types = array();
        $sizes      = pf_get_all_sizes();
        foreach ($sizes as $size) {
            $post_types = array_unique(array_merge($post_types, $size['post_type']));
        }
        return apply_filters('pf_post_type_regenerate', $post_types);
    }
}
