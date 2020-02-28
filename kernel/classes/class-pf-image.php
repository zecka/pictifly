<?php
class PF_Image{
    public $configs;
    public $source_file;
    public $width;
    public $height;

    public $default_breakpoint;
    public $default_width;
    public $default_height;

    public $ratio;
    public $target_ratio;
    public $pathinfo;
    public $title;
    public $alt;
    public $mime_type;
    public $id;
    public $default_id; // id in default lang
    public $args;
    public $retina;
    public $keypoint; // bool, array(x,y) in percent
    public $resize_folder_path;
    public $resize_folder_url;
    public $have_webp;
    private $render_array;
	private $is_svg;
	public $extension;
    public $pf_files_min;
    public function __construct($id, $args){
        if(!$id){
            return false;
        }
        $this->default_breakpoint = false;
        $args_default=$this->default_args();
        $args=array_replace_recursive($args_default, $args);
        $this->configs = pf_configs();
        $this->args = $args;
        $this->id = $id;
        $this->default_id = pf_get_id_in_default_lang($id);
        $this->source_file = get_attached_file( $id );
        $this->pf_files_min = get_post_meta($id, 'pf_files_min', true);
        $this->pathinfo = pathinfo($this->source_file);

        $this->resize_folder_path = $this->configs['resize_path'].$this->default_id.'/';
        if($this->configs['imgix'] && $this->configs['imgix_url']){
            $this->resize_folder_url = "";
        }else{
            $this->resize_folder_url = $this->configs['resize_url'].$this->default_id.'/';
        }
		$this->have_webp = false;
		$this->extension = $this->pathinfo['extension'];

        if( $this->extension === 'svg' ){
            $this->is_svg = true;
        }else{
			$this->is_svg = false;
            $size = getimagesize($this->source_file);
            $this->width = $size[0];
            $this->height = $size[1];
            $this->mime_type = $size['mime'];
            $this->define_keypoint();
            $this->calcule_src_ratio();
            $this->calcule_target_ratio();
            $this->define_attribute();
			$this->define_retina();
		}

        $this->render_array = array();

    }

    private function default_args(){
        $default_args =  array(
            'crop'	=> true, // true, false or "scale"
            'ratio'	=> false,
            'width' => null, // Default width    Put in img tag (use if no breakpoin is set or if browser doesn't support picture tag)
            'height' => null, // Default height  ´´
            'breakpoints'=> array(
                'xs'	=> false,	// width on xs screen  (default: false)
                'sm'	=> false,	// width on sm screen  (default: false)
                'md'	=> false,	// width on md screen  (default: false)
                'lg'	=> false,	// width on lg screen  (default: false)
                'xl'	=> false,	// width on xl screen  (default: false)
                'xxl'	=> false,	// width on xxl screen (default: false)
            ),
            'webp'	=> true,
            'retina' => true,
            'lazyload'	=> true,
            'lazyload_transition' => false,
            'quality' => 100,
            'title' => true,
            'canvas_color' => "ffffff",
            'alt'	=> true,
            'class'	=> ''
        );

        if(function_exists('apply_filters')){
            return apply_filters( 'pf_default_args', $default_args );
        }else{
            return $default_args;
        }
    }
    private function calcule_src_ratio(){
        $this->ratio= floatval( ( $this->height / $this->width ) );
    }
    private function calcule_target_ratio(){
        // Calculate ratio then we can find height: width x ratio = height
        if($this->args['ratio']){
            $this->ratio = $this->args['ratio'][1] / $this->args['ratio'][0]; // height / width
        }else{
            $this->ratio= floatval( ( $this->height / $this->width ) );
        }
    }

    private function define_attribute(){
        // DEFINE ALT AND TITLE ATTRIBUTE
        $this->title = ($this->args['title']===true) ? get_the_title($this->id) : $this->args['title'];
        $this->alt = ($this->args['alt']===true) ? get_post_meta( $this->id, '_wp_attachment_image_alt', true ) : $this->args['alt'];
    }
    private function define_retina(){
        if(!$this->args['retina']){
            $this->retina=1;
        }elseif($this->args['retina']<=2){
            $this->retina=2;
        }else{
            $this->retina=intval($this->args['retina']);
        }
    }

    private function define_keypoint(){

        $left_value = get_post_meta( $this->id, 'pf_keypoint_left', true );
        $left_value = $left_value ? $left_value : false;

		$top_value = get_post_meta( $this->id, 'pf_keypoint_top', true );
        $top_value = $top_value ? $top_value : false;

        if($left_value && $top_value){
            $this->keypoint = array($left_value, $top_value);
        }else{
            $this->keypoint = array(50,50);
        }

    }
    public function browser_support_webp(){
        if( isset($_SERVER['HTTP_ACCEPT']) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false ) {
            return true; // webp is supported!
		}
		if(isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_USER_AGENT'], ' Chrome/') !== false){
			return true;
		}
        return false;
    }

    public function get(){
        if(!$this->id){
            return null;
        }
        try {
            if( ($this->pathinfo['extension'] !== 'svg') ){
				if(isset($this->args['breakpoints'])){
					foreach($this->args['breakpoints'] as $title=>$dimensions){
						if($dimensions!==false){
							$breakpoint = new PF_Breakpoint($this, $dimensions, $title);
							$this->render_array['breakpoints'][$title] = $breakpoint->get();
                            $last_breakpoint = $breakpoint->get();
                        }
                    }
				}
            }else{
               $this->get_svg();
            }
            if($this->args['width'] || $this->args['height']){
                $default_img =  new PF_Breakpoint($this, [$this->args['width'], $this->args['height'], $this->args['crop']], 'default');
                $this->render_array['default_img'] = $default_img->get();
            }elseif($last_breakpoint){
                $this->render_array['default_img'] = $last_breakpoint;
            }
          

            $this->render_array['mime'] = $this->mime_type;
            $this->render_array['alt'] = $this->alt;
            $this->render_array['id'] = $this->id;
            $this->render_array['title'] = $this->title;
            $this->render_array['base_path'] = $this->resize_folder_url;
            return $this->render_array;

        } catch (\Throwable $th) {
		   //throw $th;
            error_log( $th->getMessage() );
        }
    }
    private function get_svg(){
       
        if($this->configs['imgix'] && $this->configs['imgix_url']){
            $img_path = str_replace(ABSPATH, '', $this->source_file);
            $imgix_url = $this->configs['imgix_url'];
            $img_path = $imgix_url."/".$img_path;
        }elseif(!file_exists( $this->resize_folder_path.$this->pathinfo['basename'])){
            // Check if folder exist
            if (!is_dir($this->resize_folder_path)) {
                mkdir($this->resize_folder_path, 0777, true);
            }
            copy($this->source_file, $this->resize_folder_path.$this->pathinfo['basename']);
        }else{
            $img_path = $this->pathinfo['basename'];
        }
        $this->render_array['breakpoints']['xs']['1x'] = $img_path;
    }
    public function get_simple(){
        if(!$this->id){
            return null;
        }
		$picture =  $this->get();
		if(!isset($picture['breakpoints'])){
			return null;
		}
        return $this->resize_folder_url.$picture['breakpoints']['xs']['1x'];
    }

    public function get_html(){
        if(!$this->id){
            return null;
        }
        $picture = $this->get();

        // define picture classes
        $figure_classes = array();
        $figure_classes[] = "pf_picture";
        if($this->configs['lazyload'] && $this->args['lazyload']){
            $figure_classes[] = "pf_lazy";
            if($this->args['lazyload_transition']){
                $figure_classes[] = "pf_lazy--transition";
            }
        }

        // define breakpoint source
        $breakpoints  = array();
        
        
        if(is_array($picture['breakpoints'])){
            foreach( array_reverse( $picture['breakpoints'] ) as $breakpoint=>$sizes){
                $srcset=array();
                foreach($sizes as $size=>$filename){
                    $srcset[]=$this->resize_folder_url.$filename.' '.$size;
                }
                if(isset($this->configs['breakpoints'][$breakpoint])){
                    $breakpoints[$breakpoint]['srcset'] = $srcset;
                    $breakpoints[$breakpoint]['min-width'] = $this->configs['breakpoints'][$breakpoint];
                }
            }
            $bigger_bp  = PF_Helper::pf_get_bigger_bp($picture['breakpoints']);
            $smaller_bp = PF_Helper::pf_get_smaller_bp($picture['breakpoints']);
        }else{
            if(!$picture['default_img']){
                return null;
            }
            $bigger_bp  = $picture['default_img'];
            $smaller_bp = $picture['default_img'];
        }


        // Prefix srcset with data- for lazyload
        $srcset_prefix = "";
        if($this->configs['lazyload'] && $this->args['lazyload']){
            $srcset_prefix = "data-";
        }
        $nb_breakpoints = count($breakpoints);
        ob_start();
        ?>

        <span class="<?php echo implode(" ", $figure_classes) ?>">

            <?php if($nb_breakpoints > 1): ?>
                <picture>
            <?php endif; ?>
            <?php

                if(!$this->is_svg && $nb_breakpoints > 1):
                    foreach($breakpoints as $bp){
                        ?>
                        <source
                            <?php echo $srcset_prefix; ?>srcset="<?php echo implode(', ', $bp['srcset']); ?>"
                            media="(min-width: <?php echo $bp['min-width']; ?>px)"
                            type="<?php echo $picture['mime'];  ?>">
                        <?php
                    }
                endif;
            ?>
                <img
                    <?php

                    if(isset($this->args['class']) && $this->args['class']!==''){
                        $this->args['class']= ' '.$this->args['class'];
                    }
                    if($this->alt){
                        echo 'alt="'.$this->alt.'" ';
                    }else{
                        echo 'alt="'.$this->title.'" ';
                    }
                    if(isset($this->title)){
                        echo 'title="'.$this->title.'" ';
                    }
                    
                    if($this->configs['lazyload'] && $this->args['lazyload']){
                        ?>
                        class="lazyload<?php echo $this->args['class']; ?>"
                        src="<?php echo $this->resize_folder_url.$smaller_bp['1x']; ?>"
                        data-src="<?php echo $this->resize_folder_url.$bigger_bp['1x']; ?>"
                    <?php }else{ ?>
                        class="pf_picture_img<?php echo $this->args['class']; ?>"
                        src="<?php echo $this->resize_folder_url.$bigger_bp['1x']; ?>"
                    <?php } ?>

                    <?php if(isset($bigger_bp['2x'])): ?>
                        <?php echo $srcset_prefix; ?>srcset="<?php echo $this->resize_folder_url.$bigger_bp['1x'].' 1x,'. $this->resize_folder_url.$bigger_bp['2x'].' 2x';?>"
                    <?php endif; ?>
                >
            <?php if ($nb_breakpoints > 1): ?>
                </picture>
            <?php endif;?>
        </span>

        <?php
        return PF_Helper::pf_sanitize_output(ob_get_clean());
    }

    public function display(){
        if(!$this->id){
            return null;
        }else{
            echo $this->get_html();
        }
    }

    public function attributes(){
        $attributes = "";
        if($this->title){
            $attributes.=' title="'.$this->title.'"';
        }

        if($this->alt){
            $attributes.=' alt="'.$this->alt.'"';
        }
        return $attributes;
    }

    public function background_in_img(){
        if (!$this->id) {
            return null;
        }

        ob_start();
        $this->args['class']="pf_background_img";
        // Simulate background cover
        ?>
        op-background-image style="background-image:url(<?php echo $this->get_simple(); ?>); background-size: cover; background-position:center center;">
        <div class="pf_background">
            <?php $this->display(); ?>
        </div
        <?php
        // We don't close the last tag.
        // Remember we will insert this function inside another tag
        // so there will be a ">" that basically closes our tag
        return ob_get_clean();
    }

    public function background(){
        if(!$this->id){
            return null;
        }
        ob_start();
        // Encode array picture data to json (for use in js)
        $background_data = wp_json_encode($this->get());
        $background_data = function_exists('wc_esc_json') ? wc_esc_json($background_data) : _wp_specialchars($background_data, ENT_QUOTES, 'UTF-8', true);
        ?>
        op-background data-background="<?php echo $background_data ?>" style="background-image:url(<?php echo $this->get_simple(); ?>); background-size: cover; background-position:center center;" 
        <?php
        return ob_get_clean();
    }


}
