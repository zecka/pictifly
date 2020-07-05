<?php

if(!function_exists('pf_get')){
	function pf_get($id, $size_name, $args=array()){
		unset($args['breakpoints']);
        unset($args['crop']);
        unset($args['ratio']);
        unset($args['webp']);
        unset($args['retina']);
        unset($args['lazyload']);
        unset($args['lazyload_transition']);
		unset($args['quality']);
        $size = pf_get_size($size_name);
        if(!is_array($size)){
            $default_args = false;
        }else if($size['simple']){
			$default_args=array(
				'crop'	=> $size['args'][2],
				'breakpoints'=> array(
					'xs'	=> array($size['args'][0], $size['args'][1]),	// width on xxl screen (default: false)
				),
				'retina' => false
			);
		}else{
			$default_args = $size['args'];
        }
        if($default_args){
            $args = array_merge($default_args, $args);
        }

		$picture = new PF_Image($id, $args);

		return $picture;

	}
}

if(! function_exists('pf_img')){
	function pf_img($id, $size_name, $echo = false, $args=array()){
		$picture = pf_get($id, $size_name, $args);
		if($echo){
			echo $picture->get_html();
		}else{
			return $picture->get_html();
		}

	}
}

if(! function_exists('pf_simple_url')){
	function pf_simple_url($id, $size_name, $echo = false){
		$picture = pf_get($id, $size_name);
		if($echo){
			echo $picture->get_simple();
		}else{
			return $picture->get_simple();
		}
	}
}

if(! function_exists('pf_img_background')){
	function pf_img_background($id, $size_name, $args = array() ){
		$picture = pf_get($id, $size_name, $args);
		return $picture->background();
	}
}

/**
 *
 * ===========================================
 * 				UNAMED SIZE
 * ===========================================
 *
 */

if(! function_exists('pf_display')){
    function pf_display($image_id, $args=array()){
        $picture=new PF_Image($image_id, $args);
        return $picture->get_html();
    }
}

if(! function_exists('pf_get_array')){
    function pf_get_array($image_id, $args=array()){
        $picture = new PF_Image($image_id, $args);
        return $picture->get();
    }
}

if(! function_exists('pf_background')){
    function pf_background($image_id, $args=array()){
        $picture = new PF_Image($image_id, $args);
        return $picture->background();
    }
}

if(! function_exists('pf_get_simple')){
    function pf_get_simple($image_id, $width, $height=null, $crop=false){
        $args=array(
                'crop'	=> $crop,
                'breakpoints'=> array(
                    'xs'	=> array($width, $height),	// width on xxl screen (default: false)
                ),
                'retina' => false
        );
        $picture = new PF_Image($image_id, $args);
        return $picture->get_simple();
    }
}

if(! function_exists('pf_simple')){
    function pf_simple($image_id, $width, $height=null, $crop=false, $args=array()){
        $args_default=array(
                'crop'	=> $crop,
                'breakpoints'=> array(
                    'xs'	=> array($width, $height),	// width on xxl screen (default: false)
                ),
                'retina' => false
        );
        $args = array_merge($args_default, $args);

        $picture = new PF_Image($image_id, $args);
        return $picture;
    }
}

if(! function_exists('pf_display_simple')){
    function pf_display_simple($image, $width, $height=null, $crop=false, $args=array()){
        $pf_img = pf_simple($image, $width, $height, $crop, $args);
        $img = $pf_img->get_simple();
        $configs = pf_configs();
        // GENERATE SMALL IMAGE FOR LAZYLOAD
        if($configs['lazyload']){
            $ratio = floatval( ($height / $width) );
            $height = (int) ( 100 * $ratio );
            $small = pf_get_simple($image, 100, $height, $crop);
        ?>
        <figure class="pf_lazy">
            <img class="lazyload" src="<?php echo $small; ?>" data-src="<?php echo $img; ?>" <?php $pf_img->attributes() ?>/>
        </figure>
        <?php }else{ ?>
            <img src="<?php echo $img; ?>" <?php $pf_img->attributes() ?>/>
        <?php }
    }
}


if(! function_exists('pf_default_attach')){
	function pf_default_attach($attach){
		$attach_default=[
			'post_type' => array(),
			'taxonomy' => array()
		];
		return array_replace_recursive($attach_default, $attach);
	}
}
