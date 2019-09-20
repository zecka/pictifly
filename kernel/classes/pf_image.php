<?php
class PF_Image{
    public $configs;
    public $source_file;
    public $width;
    public $height;
    public $ratio;
    public $target_ratio;
    public $pathinfo;
    public $title;
    public $alt;
    public $mime_type;
    public $id;
    public $args;
    public $retina;
    public $keypoint; // bool, array(x,y) in percent
    public $resize_date_folder;
    public $resize_date_url;
    public $have_webp;
    private $render_array;
	private $is_svg;
	public $extension;


    public function __construct($id, $args){
        if(!$id){
            return false;
        }
        $args_default=$this->default_args();
        $args=array_replace_recursive($args_default, $args);
        $this->configs = pf_configs();
        $this->args = $args;
        $this->id = $id;
        $this->source_file = get_attached_file( $id );
        $this->pathinfo = pathinfo($this->source_file);
		$this->date_folder = explode("/uploads/", $this->pathinfo['dirname'])[1].'/';
		if($this->date_folder==='/'){
			$this->date_folder = '';
		}
        $this->resize_date_folder = $this->configs['resize_path'].$this->date_folder;
        $this->resize_date_url = $this->configs['resize_url'].$this->date_folder;
		$this->have_webp = false;
		$this->extension = $this->pathinfo['extension'];

        if( ($this->extension === 'svg') ){
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
            'crop'	=> true,
            'breakpoints'=> array(
                'xs'	=> false,	// width on xs screen  (default: false)
                'sm'	=> false,	// width on sm screen  (default: false)
                'md'	=> false,	// width on md screen  (default: false)
                'lg'	=> false,	// width on lg screen  (default: false)
                'xl'	=> false,	// width on xl screen  (default: false)
                'xxl'	=> false,	// width on xxl screen (default: false)
            ),
            'ratio'	=> false,
            'webp'	=> true,
            'retina' => true,
            'lazyload'	=> true,
            'lazyload_transition' => false,
            'quality' => 100,
            'title' => true,
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
        if( strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false ||  strpos( $_SERVER['HTTP_USER_AGENT'], ' Chrome/' ) !== false ) {
            return true; // webp is supported!
        }
        return false;
    }

    public function get(){
        try {
            if( ($this->pathinfo['extension'] !== 'svg') ){

				foreach($this->args['breakpoints'] as $title=>$dimensions){
					if($dimensions!==false){
						$breakpoint = new PF_Breakpoint($this, $dimensions, $title);
                        $this->render_array['breakpoints'][$title] = $breakpoint->get();
                    }
                }
            }else{
                if(!file_exists( $this->resize_date_folder.$this->pathinfo['basename'])){
                    copy($this->source_file, $this->resize_date_folder.$this->pathinfo['basename']);
                }
                $this->render_array['breakpoints']['xs']['1x'] = $this->pathinfo['basename'];
            }

            $this->render_array['mime'] = $this->mime_type;
            $this->render_array['alt'] = $this->alt;
            $this->render_array['id'] = $this->id;
            $this->render_array['title'] = $this->title;
            $this->render_array['base_path'] = $this->resize_date_url;

            return $this->render_array;

        } catch (\Throwable $th) {
		   //throw $th;
            echo 'The id of image probably not exist'. $th->getMessage();
        }
    }
    public function get_simple(){
        $picture =  $this->get();
        return $this->resize_date_url.$picture['breakpoints']['xs']['1x'];
    }

    public function get_html(){
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
        foreach( array_reverse( $picture['breakpoints'] ) as $breakpoint=>$sizes){
            $srcset=array();
            foreach($sizes as $size=>$filename){
                $srcset[]=$this->resize_date_url.$filename.' '.$size;
            }

            $breakpoints[$breakpoint]['srcset'] = $srcset;
            $breakpoints[$breakpoint]['min-width'] = $this->configs['breakpoints'][$breakpoint];
        }

        // Prefix srcset with data- for lazyload
        $srcset_prefix = "";
        if($this->configs['lazyload'] && $this->args['lazyload']){
            $srcset_prefix = "data-";
        }

        ob_start();
        ?>

        <span class="<?php echo implode(" ", $figure_classes) ?>">

            <picture><?php

                foreach($breakpoints as $bp){
                    if(!$this->is_svg):
                        ?>
                        <source
                            <?php echo $srcset_prefix; ?>srcset="<?php echo implode(', ', $bp['srcset']); ?>"
                            media="(min-width: <?php echo $bp['min-width']; ?>px)"
                            type="<?php echo $picture['mime'];  ?>">
                        <?php
                    endif;
                }


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
                        src="<?php echo $this->resize_date_url.pf_get_smaller_bp($picture['breakpoints'])['1x']; ?>"
                        data-src="<?php echo $this->resize_date_url.pf_get_bigger_bp($picture['breakpoints'])['1x']; ?>"
                    <?php }else{ ?>
                        class="pf_picture_img<?php echo $this->args['class']; ?>"
                        src="<?php echo $this->resize_date_url.pf_get_bigger_bp($picture['breakpoints'])['1x']; ?>"
                    <?php } ?>
                >
            </picture>
        </span>

        <?php
        return ob_get_clean();
    }

    public function display(){
        echo $this->get_html();
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

    public function background(){
        ob_start();
        $this->args['class']="pf_background_img";
        // Simulate background cover
        ?>
        op-background style="background-image:url(<?php echo $this->get_simple(); ?>); background-size: cover; background-position:center center;">
        <div class="pf_background">
            <?php $this->display(); ?>
        </div
        <?php
        // We don't close the last tag.
        // Remember we will insert this function inside another tag
        // so there will be a ">" that basically closes our tag
        return ob_get_clean();
    }





}
