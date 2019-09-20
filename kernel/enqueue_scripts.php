<?php
function pf_enqueue_scripts(){
    $configs = pf_configs();
    $mystyle='/assets/css/pf.css';

    wp_register_style( 'pf_style', PF_URL .$mystyle, '', filemtime(PF_PATH.$mystyle));
    wp_enqueue_style('pf_style');

    wp_register_script( 'lazysizes', PF_URL . '/assets/vendors/js/lazysizes.min.js' ,array( 'jquery'),'4.1.15');

    wp_register_script( 'pf_scripts', PF_URL . '/assets/js/op.js' ,array( 'jquery'),'4.1.15');

    wp_enqueue_script( 'pf_scripts' );
    if($configs['lazyload']){
        wp_enqueue_script( 'lazysizes' );
	}

}
add_action('wp_enqueue_scripts', 'pf_enqueue_scripts');
