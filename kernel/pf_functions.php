<?php

if(! function_exists('pf_display')){
    function pf_display($image_id, $args=array()){
        $picture=new PF_Image($image_id, $args);
        return $picture->get_html();
    }
}

if(! function_exists('pf_get')){
    function pf_get($image_id, $args=array()){
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
        $args = array_merge_recursive($args_default, $args);

        $picture = new PF_Image($image_id, $args);
        return $picture;
    }
}

if(! function_exists('pf_display_simple')){
    function pf_display_simple($image, $width, $height=null, $crop=false, $args=array()){
        $pf_img = pf_simple($image, $width, $height, $crop, $args);
        $img = $pf_img->get_simple();

        $configs=pf_configs();
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

if(! function_exists('pf_img')){
	function pf_img($id, $size_name, $echo = false, $args=array()){
        
        // Unset all args relative to size
        unset($args['breakpoints']);
        unset($args['crop']);
        unset($args['ratio']);
        unset($args['webp']);
        unset($args['retina']);
        unset($args['lazyload']);
        unset($args['lazyload_transition']);
        unset($args['quality']);


        $size = pf_get_size($size_name);
		if(!$size){
		    return "the following image size doesn't exist: ".$size_name;
		}

		ob_start();
		if($size['simple']){
			pf_display_simple($id, $size['data'][0], $size['data'][1], $size['data'][2], $args);
		}else{
			echo pf_display($id, array_merge($size['data'], $args));
		}
		$img = ob_get_clean();

		if($echo){
		    echo $img;
		}else{
			return $img;
		}

	}
}
if(! function_exists('pf_simple_url')){
	function pf_simple_url($id, $size_name, $echo = false){
		$size = pf_get_size($size_name);
		if(!$size){
		    return "the following image size doesn't exist: ".$size_name;
		}elseif(!$size['simple']){
			return "Can only get url of simple size ".$size_name;
		}
		$url = pf_get_simple($id, $size['data'][0], $size['data'][1], $size['data'][2]);
		if($echo){
			echo $url;
		}else{
			return $url;
		}
	}
}


if(! function_exists('pf_img_background')){
	function pf_img_background($id, $size_name, $args = array() ){

        $size = pf_get_size($size_name);
        if($size['simple']){
        }
		if(!$size){
		    return "the following image size doesn't exist: ".$size_name;
		}

	}
}

