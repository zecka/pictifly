<?php
function pf_register_options_page(){
	add_options_page('Regenerate images', 'Pictifly', 'manage_options', 'pictifly', 'pf_options_page');
}
add_action('admin_menu', 'pf_register_options_page');

function pf_options_page(){

	$post_types = pf_get_post_types_regenerate();

	?>
	<style>
		.pf_option_page{
			padding: 1px 12px;
		}
		.pf_progress{
			margin-top: 10px;
			width: 100%;
			height: 30px;
			border-radius: 3px;
			overflow: hidden;
			border: 1px solid #333;
			position: relative;
			background: black;
		}
		.pf_progress_statut{
			height: 30px;
			background: #0073aa;
			width: 0px;
		}
		.pf_progress_percent{
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			color: #fff;
		}
		.pf_progress_count{
			margin: 30px 0 0;
			font-weight: bold;
		}
	</style>
	<div class="pf_option_page">
		<h1>Pictifly regenerate images</h1>
		<?php if(empty($post_types)){ ?>
			<div class="notice-error notice">
				You need to define your regenerate function first
			</div>
		<?php }else{ ?>
			<button class="button" data-nonce="<?php echo wp_create_nonce('pf_regenerate'); ?>">Regenerate</button>
			<div class="pf_progress_count">
				<div class="pf_progress_nbpost"><span class="value">0</span> posts regenerate</div>
				<div class="pf_progress_nbimage"><span class="value">0</span> image sizes regenerate</div>
			</div>
			<div class="pf_progress">
				<div class="pf_progress_statut"></div>
				<div class="pf_progress_percent">Not started</div>
			</div>
		<?php } ?>
	</div>
	<?php
}

add_action('wp_ajax_pf_ajax_item_image_regenerate', 'pf_ajax_item_image_regenerate');
function pf_ajax_item_image_regenerate(){

	if(!pf_valid_ajax() || !wp_verify_nonce( $_POST['nonce'], 'pf_regenerate' )) {
		esc_html_e( 'Error, please contact site owner', "berceau" );
		die();
	}

	try {
		$post_type =  esc_html(esc_attr($_POST['post_type']));
		$id =  esc_html(esc_attr($_POST['id']));

		$sizes = pf_get_all_sizes();
		$_pf_regenerated_imgs = [];
		foreach($sizes as $size_name => $size){
			if(in_array($post_type, $size['post_type'])){
				$image_id = get_post_thumbnail_id($id);
				$_pf_regenerated_imgs[] = pf_img($image_id, $size_name, false);
			}
		}
		do_action('pf_post_images_regenerate', $id, $post_type);
		global $_pf_regenerated_imgs;
		wp_send_json_success(["images"=>$_pf_regenerated_imgs]);
	} catch (Exception $e) {
		$msg = 'Exception : '.  $e->getMessage();
		wp_send_json_error($msg);
	}

	die();
}

add_action('wp_ajax_pf_ajax_regenerate_get_items', 'pf_ajax_regenerate_get_items');
function pf_ajax_regenerate_get_items(){

	if(!pf_valid_ajax() || !wp_verify_nonce( $_POST['nonce'], 'pf_regenerate' )) {
		esc_html_e( 'Error, please contact site owner', "berceau" );
		die();
	}

	$items = [];
	$post_types = pf_get_post_types_regenerate();

	foreach($post_types as $post_type){
		$post_ids = get_posts(array(
			'numberposts' 	=> -1,
			'fields'        => 'ids', // Only get post IDs
			'post_type'		=> $post_type
		));
		foreach($post_ids as $post_id){
			$items[] = [
				'id' 		=> $post_id,
				'post_type'	=> $post_type
			];
		}
	}

	wp_send_json_success($items);
}


function pf_get_post_types_regenerate(){
	$post_types = array();
	$sizes = pf_get_all_sizes();
	foreach($sizes as $size){
		$post_types = array_unique (array_merge($post_types, $size['post_type']));
	}
	$post_types = apply_filters('pf_post_type_regenerate', $post_types);
	return $post_types;
}

function pf_valid_ajax(){
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
