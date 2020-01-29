<?php 
function pf_get_translateds_ids($media_id){

    $ids=array();
    // first check if wpml exist
    if ( function_exists('icl_object_id') ) {
        // https://wpml.org/wpml-hook/wpml_get_element_translations/
        $trid =apply_filters( 'wpml_element_trid', null, $media_id , 'post_attachment');
        $translations = apply_filters( 'wpml_get_element_translations', NULL, $trid, 'post_attachment' );
        foreach($translations as $t){
            $ids[] = $t->element_id;
        }
    }else{
        $ids[]=$media_id;
    }

    return $ids;
}

function pf_get_id_in_default_lang($id){
    if(function_exists('icl_object_id')){
        global $sitepress;
        $id = icl_object_id($id, 'attachment', false, $sitepress->get_default_language());
    }
    return $id;
}
?>