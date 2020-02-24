<?php

// define the delete_attachment callback
function pf_on_delete_attachement( $post_id ) {
	pf_delete_pictifly_files($post_id );
	// pf_delete_pictifly_files($post_id, 'pf_files_min' );
}
function pf_delete_pictifly_files($post_id){
	PF_Helper::pf_remove_attachment_dir($post_id);
}
// add the action
add_action( 'delete_attachment', 'pf_on_delete_attachement', 10, 1 );

