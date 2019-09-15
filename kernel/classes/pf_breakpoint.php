<?php
class PF_Breakpoint{
    public $image; // Object PF_Image
    public $breakpoint_title; // String (p.ex: md )
    public $dimensions; // String (width), array(width, height, position);
    public $width; // int
    public $height; // int
    public $ratio; // float  height/width
    public $based_on; // string
    public $position_name; // string - Position for filename
    public $position; // string
    public $width_crop; // height for crop (before resize)
    public $height_crop; // width for crop (brefore resize)
    public $crop; // boolean
    public $x_crop; // int X axis of top left corner for crop (from original image size)
    public $y_crop; // int Y axis of top left corner for crop (from original image size)
    private $size_array; // array

    public function __construct($image, $dimensions){
		$this->image = $image;
        $this->dimensions = $dimensions;
        $this->define_ratio();
        if($this->image->args['ratio']){
            $this->define_dimension_with_ratio();
        }else{
            $this->define_dimension();
        }
        $this->define_crop_position();
        if($this->image->keypoint){
            $this->define_crop_dimensions();
            $this->define_crop_coordinate();
		}

        $this->generate();
    }

    private function define_dimension_with_ratio(){
        if(is_array($this->dimensions)){
            $this->width  = $this->dimensions[0];
			$this->height = $this->dimensions[1];
            if($this->height == null){
                $this->height = $this->width * $this->ratio;
            }elseif($this->width == null){
                $this->width = $this->height / $this->ratio;
            }else{
                if($this->ratio > $this->image->ratio){
                    $this->height = $this->dimensions[1];
                    $this->width = $this->height / $this->ratio;
                }else{
                    $this->width = $this->dimensions[0];
                    $this->height = $this->width * $this->ratio;
                }
            }
        }else{
            $this->width  = $this->dimensions;
            $this->height = $this->width * $this->ratio;
		}

    }
    private function define_dimension(){
        // here define width, height and based on

        // first check if dimenstion is set with an array or not
        if(is_array($this->dimensions)){


            $this->crop = (isset($this->dimensions[2])) ? $this->dimensions[2] : $this->image->args['crop'];



            if(!$this->crop){
                if($this->ratio > $this->image->ratio){
                    $this->height = $this->dimensions[1];
                    $this->width = $this->height / $this->image->tratio;
                }else{
                    $this->width = $this->dimensions[0];
                    $this->height = $this->width * $this->image->ratio;
                }

            }else{
                $this->width  = $this->dimensions[0];
                $this->height = $this->dimensions[1];
            }

            if($this->height === null){
                $this->based_on="width";
            }elseif($this->width == null){
                $this->based_on="height";
            }else{
                $this->based_on="both";
            }

        }else{
            $this->width = $this->dimensions;
            $this->height = $this->width * $this->ratio;
            $this->based_on="width";
            $this->crop = false;
        }


    }

    /**
     * Define base crop position
     * If
     */
    private function define_crop_position(){
        if(!$this->image->keypoint){
             $this->position_name= '';
        }else{
            $x = (int) $this->image->keypoint[0];
            $y = (int) $this->image->keypoint[1];
            $this->position_name = '-x'.$x.'y'.$y;
        }

    }
    private function define_ratio(){
		// Calculate ratio then we can find height: width x ratio = height
		if(
			is_array($this->dimensions) &&
			$this->dimensions[0] !== null && // width is not null
			$this->dimensions[1] !== null && // height is not null
			isset($this->dimensions[2]) && // Crop arg is define for given breakpoint
			$this->dimensions[2] // Crop arg is true
		){
			$this->ratio = floatval( ($this->dimensions[1] / $this->dimensions[0]) );
		}
        elseif($this->image->args['ratio']){
            $this->ratio = $this->image->args['ratio'][1] / $this->image->args['ratio'][0]; // height / width
        }elseif(is_array($this->dimensions) && $this->image->args['crop']){
            if($this->dimensions[0] == null || $this->dimensions[1] == null){
                $this->ratio = $this->image->ratio;
            }else{
                $this->ratio = floatval( ($this->dimensions[1] / $this->dimensions[0]) );
            }
        }else{
            $this->ratio = floatval( ($this->image->height / $this->image->width) );
        }
    }
    private function define_crop_dimensions(){
        $crop_ratio = $this->height / $this->width;
        if($this->based_on=='width'){
            $this->width_crop = $this->image->width;
            $this->height_crop= ( $this->image->width * $crop_ratio);
        }else if($this->based_on=="height"){
            $this->height_crop = $this->image->height;
            $this->width_crop = ( $this->height_crop / $crop_ratio );
        }else{
            $this->width_crop = $this->image->width;
            $this->height_crop = $this->image->width * $crop_ratio;
        }

        // DECREASE HEIGHT CROP IF BIGGER THANT IMAGE
        if($this->height_crop > $this->image->height){

            $decrease = $this->height_crop - $this->image->height;
            $percent_decrease = $decrease /$this->height_crop * 100;

            // define height crop as height image and adapte width
            $this->height_crop =  $this->image->height;

            $decrease_width = ($this->width_crop / 100) * $percent_decrease;
            $this->width_crop = $this->width_crop - $decrease_width;

        }

        // DECREASE WIDTH CROP IF BIGGER THAN IMAGE
        if($this->width_crop > $this->image->width){
            $decrease = $this->width_crop - $this->image->width;
            $percent_decrease = $decrease / $this->width_crop * 100;

            // define height crop as height image and adapte width
            $this->width_crop =  $this->image->width;

            $decrease_height = ($this->height_crop / 100) * $percent_decrease;
            $this->height_crop = $this->height_crop - $decrease_height;
        }


        $this->width_crop = (int) ($this->width_crop);
        $this->height_crop = (int) ($this->height_crop);


    }
    /**
     * We need to defined crop coordinate from the keypoint of image
     * Crop coordinate is the top left corner
     * Our keypoint is the center of the crop
     * from crop center we need to find crop top left corner
     */
    private function define_crop_coordinate(){

        $x_percent = $this->image->keypoint[0];
        $y_percent = $this->image->keypoint[1];
        $x_center = $this->image->width / 100 * $x_percent;
        $y_center = $this->image->height / 100 * $y_percent;

        $x = $x_center - ($this->width_crop / 2);
        $y = $y_center - ($this->height_crop / 2);


        if($x < 0){
            $x = 0;
        }else if($x + $this->width_crop > $this->image->width){
            $x =  $this->image->width - $this->width_crop;
        }

        if($y < 0){
            $y = 0;
        }else if($y + $this->height_crop > $this->image->height){
            $y =  $this->image->height - $this->height_crop;
        }

        $this->x_crop = (int) $x;
        $this->y_crop = (int) $y;
    }
    private function generate(){
        $size_array = array();
        for($retina_x=1; $retina_x <= $this->image->retina; $retina_x++){
            $size = new PF_Size($this->image, $this, $retina_x);
            $size_array[$retina_x.'x']= $size->get();
        }
        $this->size_array = $size_array;
    }
    public function get(){
        return $this->size_array;
    }

}
