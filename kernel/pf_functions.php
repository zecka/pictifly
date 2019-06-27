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

if(! function_exists('pf_display_simple')){
    function pf_display_simple($image, $width, $height=null, $crop=false){
        $img = pf_get_simple($image, $width, $height, $crop);
        $configs=pf_configs();	
        // GENERATE SMALL IMAGE FOR LAZYLOAD
        if($configs['lazyload']){
            $ratio = floatval( ($height / $width) );
            $height = (int) ( 100 * $ratio );
            $small = pf_get_simple($image, 100, $height, $crop);
        ?>
        <figure class="pf_lazy">
            <img class="lazyload" src="<?php echo $small; ?>" data-src="<?php echo $img; ?>" />
        </figure>
        <?php }else{ ?>
            <img src="<?php echo $img; ?>" />
        <?php }
    }
}