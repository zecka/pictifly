<?php

// define the delete_attachment callback
function pf_on_delete_attachement( $post_id ) {
		$array_files = get_post_meta( $post_id, 'pf_files', true);
		if(is_array($array_files)){
			foreach($array_files as $file){
				if(file_exists($file)){
					wp_delete_file($file);
				}
				if(file_exists($file.'.webp')){
					wp_delete_file($file.'.webp');
				}
			}

		}
};
// add the action
add_action( 'delete_attachment', 'pf_on_delete_attachement', 10, 1 );
