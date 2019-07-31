<?php
$_pf_image_sizes = array();
function pf_register($name, $args, $attach = array())
{
	global $_pf_image_sizes;
	$attach = pf_default_attach($attach);
	$_pf_image_sizes[$name] = [
		'simple'    => false,
		'post_type' => $attach['post_type'],
		'taxonomy' => $attach['taxonomy'],
		'args' => $args,
	];
}
function pf_register_simple($name, $width, $height = null, $crop = false, $attach = array())
{
	global $_pf_image_sizes;
	$attach = pf_default_attach($attach);
	$_pf_image_sizes[$name] = [
		'simple'    => true,
		'post_type'    => $attach['post_type'],
		'args' => [$width, $height, $crop],
	];
}
function pf_get_all_sizes()
{
	global $_pf_image_sizes;
	return apply_filters('pf_sizes', $_pf_image_sizes);
}

function pf_get_size($name)
{
	$sizes = pf_get_all_sizes();
	return isset($sizes[$name]) ? $sizes[$name] : false;
}

function pf_get_size_args($name)
{
	$sizes = pf_get_all_sizes();
	return isset($sizes[$name]['args']) ? $sizes[$name]['args'] : false;
}
