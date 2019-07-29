<?php

/**
 * Adding a custom field to Attachment Edit Fields
 * @param  array $form_fields
 * @param  WP_POST $post
 * @return array
 */
function pf_add_media_custom_field( $form_fields, $post ) {

    ob_start();

    $left_value = get_post_meta( $post->ID, 'pf_keypoint_left', true );
    $left_value = $left_value ? $left_value : '50';

    $top_value = get_post_meta( $post->ID, 'pf_keypoint_top', true );
    $top_value = $top_value ? $top_value : '50';

    $img = wp_get_attachment_image_src( $post->ID, 'large')[0];

    ?>
    <div class="pf_keypoint_wrapper">
        <div class="pf_keypoint_figure">
            <span class="pf_keypoint" style="top:<?php echo $top_value; ?>%; left: <?php echo $left_value; ?>%;"></span>
            <img src="<?php echo $img; ?>" />
        </div>
        <label>percent left:</label>
        <input type="text" class="pf_keypoint_left" name="attachments[<?php echo $post->ID; ?>][pf_keypoint_left]" value="<?php echo $left_value; ?>" />
        <label>Percent top:</label>
        <input type="text" class="pf_keypoint_top" name="attachments[<?php echo $post->ID; ?>][pf_keypoint_top]" value="<?php echo $top_value; ?>" />


    </div>
    <?php
    $keypoint_html = ob_get_clean();

    // Adding the tag field
    $form_fields['pf_keypoint'] = array(
        'label' => __( 'Keypoint:' ),
        'input'  => 'html',
        'html' => $keypoint_html
    );

    return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'pf_add_media_custom_field', null, 2 );


/**
 * Saving the attachment data
 * @param  integer $attachment_id
 * @return void
 */
function pf_save_attachment( $attachment_id ) {

    $ids = pf_get_translateds_ids($attachment_id);

    if ( isset( $_REQUEST['attachments'][ $attachment_id ]['pf_keypoint_left'] ) ) {
        $keypoint = $_REQUEST['attachments'][ $attachment_id ]['pf_keypoint_left'];
        if(intval($keypoint) > 0 && intval($keypoint) < 101){
            foreach($ids as $id){
                update_post_meta( $id, 'pf_keypoint_left', $keypoint );
            }
        }
    }
    if ( isset( $_REQUEST['attachments'][ $attachment_id ]['pf_keypoint_top'] ) ) {
        $keypoint = $_REQUEST['attachments'][ $attachment_id ]['pf_keypoint_top'];
        if(intval($keypoint) > 0 && intval($keypoint) < 101){
            foreach($ids as $id){
                update_post_meta( $id, 'pf_keypoint_top', $keypoint );
            }
        }
    }
}
add_action( 'edit_attachment', 'pf_save_attachment' );

add_action( 'wp_ajax_save-attachment-compat', 'wpse256463_media_fields', 0, 1 );
function wpse256463_media_fields() {
    $post_id = $_POST['id'];
    pf_save_attachment($post_id);

    clean_post_cache( $post_id );
}


function load_custom_wp_admin_style() {
    wp_register_style( 'pf_admin_css', PF_URL . '/assets/css/pf-admin.css', false, '1.0.0' );
    wp_enqueue_style( 'pf_admin_css' );
	wp_register_script( 'pf_admin_js', PF_URL . '/assets/js/pf-admin.js', array('jquery'), '1.0.0', true );
	wp_localize_script('pf_admin_js', 'myAjax', array(
		'ajaxurl' => admin_url('admin-ajax.php')
	));
    wp_enqueue_script( 'pf_admin_js' );
}
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );
