- [Presentation](#presentation)
  - [Features](#features)
- [Requirement](#requirement)
- [Getting started](#getting-started)
  - [1. Install plugin](#1-install-plugin)
  - [2. Register image sizes](#2-register-image-sizes)
  - [2. usage in template](#2-usage-in-template)
  - [3. Set breakpoints](#3-set-breakpoints)
- [Next Step](#next-step)

# Presentation

A plugin to generate different image sizes and return them in a `<picture>` tag with different breakpoints and screen resolution.
The plugin checks the existence of an image size, if it does not exist, generates it and saves it.

## Features

- Keypoint resize
  ![keypoint](documentation/keypoint.gif)
- On the fly resize
- Webp
- Retina
- Responsive picture tag
- …

# Requirement

- PHP 7
- **Imagick**
- WordPress > 4.9

# Getting started

## 1. Install plugin

- Download zip file of this repo
- Unzip file in wp-content/plugins/
- Go to wp-content/plugins/pictifly on terminal
- type `composer install`

## 2. Register image sizes

Pictifly use two type of image size :

- **simple** : Only one size, same for all breakpoints. With 2x retina support
- **responsive** : Size for multiple breakpoints support retina

### Example

```php
// register a responsive size
$args = [
   'ratio' => [350, 190],
   'breakpoints' => [
       'xs' => 350,
       'md' => 650,
   ]
];
$attach = [
  'post_type' => ['recipe'],
];
pf_register('recipe', $args, $attach);


// register a simple size
$attach = [
    'post_type' => ['recipe']
];
pf_register_simple('recipe-simple', 100, 100, true, $attach);


```

## 2. usage in template

```php
// get picture tag
$image = pf_img($image_id, 'recipe-simple', false);
// display picture tag
pf_img($image_id, 'recipe-simple', true);

// get only url (Work only with simple size)
$url = pf_simple_url($image_id, 'recipe-simple');
```

## 3. Set breakpoints

```php
add_filter('pf_configs', 'pf_configs_filters');
function pf_configs_filters($default){
  $default['breakpoints']=array(
    'xs'  => 0,
    'sm'  => 180,
    'md'  => 640,
    'lg'  => 1024,
    'xl'  => 1200,
    'xxl' => 1440
  );
  return $default;
}
```

# Next Step

[View full doc here (in french)](documentation/readme.md)
