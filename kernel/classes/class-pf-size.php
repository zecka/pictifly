<?php

use Intervention\Image\ImageManagerStatic as Image;

$driver = 'imagick';
if (!extension_loaded('imagick'))
    // imagick not installed
    $driver = 'GD';

Image::configure(array('driver' => $driver));

use WebPConvert\WebPConvert;

class PF_Size {
    public $breakpoint; // Object PF_Breakpoint
    public $image; // Object PF_Image
    public $img; // Object Image (intevention image)
    public $retina_x; // int, retina multiplicator
    public $width; // int Final width of generate file
    public $height; // int Final width of generate file
    public $filename; // string
    private $configs;
    private $key; // Key in array custom field of image p.ex(md2) (md retina x2)
    private $scale;
    public function __construct(PF_Image $image, PF_Breakpoint $breakpoint, $retina_x = 1) {
        $this->configs = pf_configs();
        $this->breakpoint = $breakpoint;
        $this->image = $image;
        $this->retina_x = $retina_x;
        $this->define_dimensions();
        $this->define_scale();
        $this->define_file_name();
        $this->define_key();
    }
    private function define_dimensions() {
        if ($this->breakpoint->based_on == 'width') {
            $this->width = (int) ($this->breakpoint->width * $this->retina_x);
            $this->height = (int) ($this->width * $this->breakpoint->ratio);
        } elseif ($this->breakpoint->based_on == "height") {
            $this->height = (int) ($this->breakpoint->height * $this->retina_x);
            $this->width = (int) ($this->height / $this->breakpoint->ratio);
        } else {
            $this->width = (int) ($this->breakpoint->width * $this->retina_x);
            $this->height = (int) ($this->breakpoint->height * $this->retina_x);
        }

        // prevent upscale
        if ($this->width > $this->image->width) {
            $this->width = $this->image->width;
            $this->height = (int) ($this->width * $this->breakpoint->ratio);
        }
        if ($this->height > $this->image->height) {
            $this->height = $this->image->height;
            $this->width = (int) ($this->height / $this->breakpoint->ratio);
        }
    }

    private function define_scale() {
        $crops   = [];
        $crops[] = $this->image->args['crop'];
        $crops[] = $this->breakpoint->crop;
        $this->scale = in_array('scale', $crops, true);
    }

    private function define_file_name() {
        $file_name  = $this->image->pathinfo['filename'];
        $img_size   = '-' . $this->width . 'x' . $this->height;
        $position   = $this->breakpoint->position_name;

        $scale_name     = ($this->scale) ? '-scale' : '';


        $quality    = '-' . $this->image->args['quality'];
        $extension  = '.' . $this->image->pathinfo['extension'];
        $this->filename = $file_name . $img_size . $position . $scale_name . $quality . $extension;
    }
    private function define_key() {
        $key_string = 'w' . $this->width . 'h' . $this->height;
        $this->key = $key_string;
    }
    public function get() {
        if ($this->image->configs['imgix'] && $this->image->configs['imgix_url']) {
            return $this->get_imgix_url();
        }

        // Check if default file existe (jpg, png, …)
        if (!file_exists($this->image->resize_folder_path . $this->filename)) {
            // Check if folder exist
            if (!is_dir($this->image->resize_folder_path)) {
                mkdir($this->image->resize_folder_path, 0777, true);
            }
            $this->generate_img();
            $this->save_img();
        }
        // Convert to webp if enable
        if (!file_exists($this->image->resize_folder_path . $this->filename . '.webp') && $this->image->args['webp']) {
            $this->convert_webp();
        } elseif ($this->image->args['webp']) {
            $this->image->have_webp = true;
        }
        $webp = $this->image->have_webp && $this->image->browser_support_webp();

        if ($webp) {
            $this->filename = $this->filename . '.webp';
            $this->image->mime_type = "image/webp";
            $webp = true;
        }
        $min_fileinfo = PF_Compress::filepath_info_for_compression($this->image->resize_folder_path . $this->filename);
        if (file_exists($min_fileinfo['fullpath'])) {
            return 'min/' . $min_fileinfo['filename'];
        }
        return $this->filename;
    }
    private function get_imgix_url() {
        $img_path = str_replace(ABSPATH, '', $this->image->source_file);
        $imgix_url = $this->image->configs['imgix_url'];
        // $image_url = "test-1.jpg";
        $imgix_url = $imgix_url . "/" . $img_path . "?";
        $params = [];
        $params[] = 'w=' . $this->width;
        $params[] = 'h=' . $this->height;
        if ($this->image->browser_support_webp()) {
            $params[] = 'fm=webp';
        }
        if ($this->image->args['crop']) {
            if ($this->scale) {
                $params[] = 'fit=fill';
                $params[] = 'fill=solid';
                $params[] = 'fill-color=ffffff';
            } else {
                $params[] = 'rect=' . $this->breakpoint->x_crop . ',' . $this->breakpoint->y_crop . ',' . $this->breakpoint->width_crop . ',' . $this->breakpoint->height_crop;
            }
        }
        $params = apply_filters('pf_imgix_params', $params, $this->image, $this->breakpoint, $this->retina_x);
        $final_url =  $imgix_url . implode("&", $params);
        return apply_filters('pf_imgix_final_url', $final_url, $this->image, $this->breakpoint, $this->retina_x);
    }
    private function generate_img() {
        set_time_limit(0);
        if ($this->image->extension == 'gif') {
            $this->img = new Imagick($this->image->source_file); // $image_path is the path to the image location
            if ($this->image->keypoint && ($this->breakpoint->crop || $this->image->args['ratio'])) {
                $this->img = $this->img->coalesceImages();
                foreach ($this->img as $frame) {
                    $frame->cropImage(
                        $this->breakpoint->width_crop,
                        $this->breakpoint->height_crop,
                        $this->breakpoint->x_crop,
                        $this->breakpoint->y_crop
                    );
                    $frame->thumbnailImage($this->width, $this->height);
                    $frame->setImagePage($this->width, $this->height, 0, 0);
                }
                $this->img = $this->img->deconstructImages();
            } else {
                $this->img = $this->img->coalesceImages();
                $this->img->setGravity(\Imagick::GRAVITY_CENTER);
                $this->img->cropImage($this->width, $this->height, 0, 0);
                $this->img->setImagePage(0, 0, 0, 0);
            }
        } else {
            $this->img = Image::make($this->image->source_file);
            if ($this->image->args['crop']) {
                $this->generate_cropped_img();
            } else {
                $this->fit_to_format();
            }
        }
    }
    private function generate_cropped_img() {
        if ($this->scale) {
            $this->scale_to_fit_canvas();
        } elseif ($this->image->keypoint && ($this->breakpoint->crop || $this->image->args['ratio'])) {
            $this->crop_from_keypoint();
        } else {
            $this->fit_to_format();
        }
    }
    private function crop_from_keypoint() {

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
    /**
     * Scale image to fit canvas instead crop it
     * So we have to add border in top/bottom or left/right to fit format
     * @see http://image.intervention.io/api/resizeCanvas
     * @return void
     */
    private function scale_to_fit_canvas() {
        $this->img->resize($this->width, $this->height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $this->img->resizeCanvas(
            $this->width,
            $this->height,
            'center',
            false,
            $this->image->args['canvas_color']
        );
    }
    private function fit_to_format() {
        $this->img->fit($this->width, $this->height, function ($constraint) {
            $constraint->upsize();
        }, $this->breakpoint->position);
    }
    private function resize() {
        $this->img->resize($this->width, $this->height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
    private function clean_old_file() {
        pf_on_delete_attachement($this->image->id);
    }
    private function save_img() {
        if ($this->image->extension == 'gif') {
            $this->img->writeImages($this->image->resize_folder_path . $this->filename, true);
        } else {
            $this->img->sharpen(3);
            $this->img->save(
                $this->image->resize_folder_path . $this->filename,
                $this->image->args['quality']
            );
        }
    }
    private function convert_webp() {
        if ($this->image->browser_support_webp() && $this->image->extension !== 'gif') {
            $source =  $this->image->resize_folder_path . $this->filename;
            $destination = $source . '.webp';
            $success = WebPConvert::convert($source, $destination, [
                // It is not required that you set any options - all have sensible defaults.
                // We set some, for the sake of the example.
                'quality' => 'auto',
                'max-quality' => $this->image->args['quality'],
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
        } else {
            $success = false;
        }
    }
}
