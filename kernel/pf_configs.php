<?php 
function pf_configs(){

    $settings = PF_Settings::getInstance();
    $configs=array(
        'imgix'  => $settings->options['imgix'],
        'imgix_url'  => $settings->options['imgix_url'],
        'lazyload'  => $settings->options['lazyload'],
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

    if(function_exists('apply_filters')){
        $configs = apply_filters( 'pf_configs', $configs );
    }
    // CREAT PATH IF NOT EXIST
    if (!file_exists( $configs['resize_path'] )) {
        mkdir($configs['resize_path'], 0755, true);
    }

	return $configs;
	
}

?>
