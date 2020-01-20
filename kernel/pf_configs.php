<?php 
function pf_configs(){
    // DEFINE BREAKPOINT
    
    $configs=array(
        'imgix'  => false,
        'imgix_url'  => false,
        'lazyload'  => false,
        'breakpoints' =>array(
            'xs'  => 0,
            'sm'  => 180,
            'md'  => 640,
            'lg'  => 1024,
            'xl'  => 1200,
            'xxl' => 1440
        ),
        'resize_path' => wp_upload_dir()['basedir'].'/pictifly/',
        'resize_url'  => wp_upload_dir()['baseurl'].'/pictifly/'
    );



    // CREAT PATH IF NOT EXIST
    if (!file_exists( $configs['resize_path'] )) {
        mkdir($configs['resize_path'], 0755, true);
    }


    if(function_exists('apply_filters')){
		return apply_filters( 'pf_configs', $configs );
	}else{
		return $configs;
	}
}

?>