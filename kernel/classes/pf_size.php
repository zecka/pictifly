<?php
use Intervention\Image\ImageManagerStatic as Image;
$driver = 'imagick';
if (!extension_loaded('imagick'))
    // imagick not installed
    $driver = 'GD';

Image::configure(array('driver' => $driver));
use WebPConvert\WebPConvert;

class PF_Size{
    public $breakpoint; // Object PF_Breakpoint
    public $image; // Object PF_Image
    public $img; // Object Image (intevention image)
    public $retina_x; // int, retina multiplicator
    public $width; // int Final width of generate file
    public $height; // int Final width of generate file
    public $filename; // string
    private $configs;

    public function __construct(PF_Image $image, PF_Breakpoint $breakpoint, $retina_x = 1){
        $this->configs = pf_configs();
        $this->breakpoint = $breakpoint;
        $this->image = $image;
        $this->retina_x = $retina_x;
        $this->define_dimensions();
        $this->define_file_name();
    }
    
    private function define_dimensions(){
        

        if($this->breakpoint->based_on=='width'){
            $this->width = (int) ( $this->breakpoint->width * $this->retina_x);
            $this->height= (int) ( $this->width * $this->breakpoint->ratio );            
        }elseif($this->breakpoint->based_on=="height"){
            $this->height = (int) ( $this->breakpoint->height * $this->retina_x );
            $this->width = (int) ( $this->height / $this->breakpoint->ratio );
        }else{
            $this->width = (int) ( $this->breakpoint->width * $this->retina_x);
            $this->height = (int) ( $this->breakpoint->height * $this->retina_x );
        }  
        
        

        // prevent upscale
        if($this->width > $this->image->width){
            $this->width = $this->image->width;
            $this->height= (int) ( $this->width * $this->breakpoint->ratio );  
        }
        if($this->height > $this->image->height){
            $this->height = $this->image->height;
            $this->width = (int) ( $this->height / $this->breakpoint->ratio );  
        }



    }

    
    private function define_file_name(){
        $file_name  = $this->image->pathinfo['filename'];
        $img_size   = '-'.$this->width.'x'.$this->height;
        $position   = $this->breakpoint->position_name;
        $quality    = '-'.$this->image->args['quality'];
        $extension  ='.'.$this->image->pathinfo['extension'];
	    $this->filename = $file_name.$img_size.$position.$quality.$extension;
    }
    public function get(){

        // Check if folder exist
        if (!is_dir($this->image->resize_date_folder)){
            mkdir($this->image->resize_date_folder, 0777, true);
        }
        // Check if default file existe (jpg, png, …)
        if(!file_exists($this->image->resize_date_folder.$this->filename )){
            $this->generate_img();
            $this->save_img();
        }
        // Convert to webp if enable
        if(!file_exists($this->image->resize_date_folder.$this->filename.'.webp') && $this->image->args['webp']){
            $this->convert_webp();
            
        }elseif($this->image->args['webp']){
            $this->image->have_webp = true;
        }

        if($this->image->have_webp && $this->image->browser_support_webp()){
            $this->filename = $this->filename . '.webp';
            $this->image->mime_type = "image/webp";
        }

        return $this->filename;    
    }
    
    private function generate_img(){
        set_time_limit(0);
        $this->img = Image::make($this->image->source_file);
        if($this->image->args['crop']){
            if($this->image->keypoint && ( $this->breakpoint->crop || $this->image->args['ratio'])){
                $this->crop_from_keypoint();
            }else{
                $this->fit_to_format();
            }
        }else{
            $this->fit_to_format();
        }
    }

    private function crop_from_keypoint(){
		$this->img->crop(
            $this->breakpoint->width_crop,
            $this->breakpoint->height_crop,
            $this->breakpoint->x_crop, 
            $this->breakpoint->y_crop
        );
        $this->img->resize($this->width, $this->height, function ($constraint) {
            $constraint->upsize();
        });

    }
    private function fit_to_format(){
        $this->img->fit($this->width, $this->height, function ($constraint) {
            $constraint->upsize();
        }, $this->breakpoint->position);
    }
    private function resize(){
        $img->resize($this->width, $this->height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
    private function save_img(){

    	$this->img->sharpen(3);
        $this->img->save(
            $this->image->resize_date_folder.$this->filename, 
            $this->image->args['quality']
        );       
    }
    private function convert_webp(){
        if($this->image->browser_support_webp()){
            $source =  $this->image->resize_date_folder.$this->filename;
            $destination = $source.'.webp';
            $success = WebPConvert::convert($source, $destination, [
                // It is not required that you set any options - all have sensible defaults.
                // We set some, for the sake of the example.
                'quality' => 'auto',
                'max-quality' => 90,
                'converters' => [
                    'cwebp', 
                    'imagick',
                    [
                        'converter' => 'gd',
                        'options' => [            
                            'skip-pngs' => false,
                        ],
                    ], 
                ],  // Specify conversion methods to use, and their order
                // more options available! - see the api
            ]);

            $this->image->have_webp = $success;
        }else{
            $success = false;
        }

        
    }

}