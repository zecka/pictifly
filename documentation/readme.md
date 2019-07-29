Un plugin permettant de générer différentes tailles d'images et les retourner dans une balise `<picture>` avec les différents breakpoints et résolution d'écran.
Le plugin vérifie l'existence d'une taille d'image, si celle-ci n'existe pas la génère et la sauvegarde.

<!-- TOC -->

- [1. Aperçu des fonctions](#1-aperçu-des-fonctions)
- [2. Exemple d'utilisations](#2-exemple-dutilisations)
- [3. Description des fonctions](#3-description-des-fonctions)
  - [3.1 pfregister()](#31-pfregister)
    - [3.1.1 Args](#311-args)
  - [3.2 pfregistersimple()](#32-pfregistersimple)
  - [3.3 pfimg()](#33-pfimg)
  - [3.4 pfsimpleurl()](#34-pfsimpleurl)
  - [3.5. pfgetsimple()](#35-pfgetsimple)
    - [3.5.1. Arguments](#351-arguments)
    - [3.5.2. Retour (string)](#352-retour-string)
  - [3.6. pfget()](#36-pfget)
    - [3.6.1. Arguments](#361-arguments)
    - [3.6.2. Retour (array)](#362-retour-array)
  - [3.7. pfdisplay()](#37-pfdisplay)
    - [3.7.1. Arguments](#371-arguments)
    - [3.7.2. Retour (string)](#372-retour-string)
- [4. Args](#4-args)
  - [4.1. Valeurs par default](#41-valeurs-par-default)
  - [4.2. `crop` (bool)](#42-crop-bool)
  - [4.3. `breakpoints` (array)](#43-breakpoints-array)
  - [4.4. `ratio` (array)](#44-ratio-array)
  - [4.5. `webp` (bool)](#45-webp-bool)
- [5. Filtres arguments](#5-filtres-arguments)
  - [5.1. Comment modifier les arguments par default en dehors du plugin](#51-comment-modifier-les-arguments-par-default-en-dehors-du-plugin)
- [6. Configuration par défault](#6-configuration-par-défault)
  - [6.1. Comment modifier une configuration par default](#61-comment-modifier-une-configuration-par-default)
- [7. LazyLoad](#7-lazyload)
  - [7.1. Comment Désactiver LazyLoad](#71-comment-désactiver-lazyload)
- [8. Regeneration d'image en backoffice](#8-regeneration-dimage-en-backoffice)
  - [8.1 Attacher des post types à la régénération](#81-attacher-des-post-types-à-la-régénération)
  - [8.2 Créer une méthode de regénération personnalisée](#82-créer-une-méthode-de-regénération-personnalisée)

<!-- /TOC -->

# 1. Aperçu des fonctions

```php
pf_get($image, $args) // Génère les image et retourne le tableau
pf_display($image, $args) // retourne une balise <picture> basé sur le tableau de pf_get
pf_get_simple($image, $width, $height, $crop) // retourn l'url d'une taille donnée
```

# 2. Exemple d'utilisations

```php
$args=array(
	'crop'	=> true,
	'breakpoints' => array(
		'xs'	=> array(400, 600),	// width on xs screen  (default: false)
		'sm'	=> array(600, null),	// width on sm screen  (default: false)
		'md'	=> array(800,800),
		'lg'	=> array(1200,1200),
		'xl'	=> array(100,100)
	),
	'webp'	=> false,
	'retina' => 2,
);
echo pf_display('path/to/image.jpg', $args);
// Sur WordPress uniquement
echo pf_display( get_post_thumbnail_id(), $args);

```

```php
$img_url = pf_get_simple('path/to/image.jpg', 800, 800, true);
echo '<img src="'.$img_url.'" />';

// Sur WordPress uniquement
$img_url = pf_get_simple($image_id, 800, 800, true);
echo '<img src="'.$img_url.'" />';


```

# 3. Description des fonctions

## 3.1 pf_register()

Enregistre une taille d'image responsive

A placer dans functions.php

```php
$args = [
   'ratio' => [350, 190],
   'breakpoints' => [
       'xs' => 350,
       'md' => 650,
   ]
];
$attach = [ 'post_type' => ['recipe'] ]
pf_register('recipe', $args, $attach);
```

### 3.1.1 Args

`$args (array)` Voir [Args](#4-args)

`$attach (array)` Attacher la taille d'image à un post type, l'image à la une sera regénérée à cette taille lors de la Régénération

## 3.2 pf_register_simple()

Enregistre une taille d'image simple

```php
$attach = [ 'post_type' => ['recipe'] ];
pf_register_simple('recipe-simple', 100, 100, true, $attach);
```

`$image (int)` ID de l'image dans wordpress  
`$width (int)` Largeur maximal de l'image en pixel  
`$height (int)` Hauteur maximal de l'image en pixel  
`$crop (bool)` Rogner l'image pour atteindre exactement la dimension renseignée (width & height)  
`$attach (array)` Attacher la taille d'image à un post type

## 3.3 pf_img()

```php
 pf_img($image_id, $size_name, $echo = false);
```

`$image_id (int)` ID de l'image dans wordpress  
`$size_name (string)` Nom de la taille d'image  
`$echo (bool)` Afficher le retour

## 3.4 pf_simple_url()

```php
pf_simple_url($image_id, $size_name, $echo = false)
```

`$image_id (int)` ID de l'image dans wordpress  
`$size_name (string)` Nom de la taille d'image  
`$echo (bool)` Afficher le retour

## 3.5. pf_get_simple()

Retourne uniquement l'url d'une taille précise d'image

`pf_get_simple($image, $width, $height=null, $crop=false)`

### 3.5.1. Arguments

`$image (int)` ID de l'image dans wordpress

`$width (int)` Largeur maximal de l'image en pixel

`$height (int)` Hauteur maximal de l'image en pixel

`$crop (bool)` Rogner l'image pour atteindre exactement la dimention renseigné (width & height)

### 3.5.2. Retour (string)

Retourne l'url de l'image

## 3.6. pf_get()

Génère les image et retourne le tableau

`pf_get($image, $args=array())`

### 3.6.1. Arguments

`$image (int)` ID de l'image dans wordpress

`$args (array)` Voir [section Args](#4-args)

### 3.6.2. Retour (array)

Retourne un tableau contenant les différente taille d'image demandé dans \$args

Example de retour:

```php
Array
(
  [breakpoints] => Array
  (
    [xs] => Array
    (
      [1x] => myimage-768x432-x81y78-85.jpg.webp
      [2x] => myimage-1536x864-x81y78-85.jpg.webp
    )

    [sm] => Array
    (
      [1x] => myimage-1024x576-x81y78-85.jpg.webp
      [2x] => myimage-2048x1152-x81y78-85.jpg.webp
    )

    [md] => Array
    (
      [1x] => myimage-1200x675-x81y78-85.jpg.webp
      [2x] => myimage-2400x1350-x81y78-85.jpg.webp
    )

    [lg] => Array
    (
      [1x] => myimage-1600x900-x81y78-85.jpg.webp
      [2x] => myimage-3200x1800-x81y78-85.jpg.webp
    )

    [xxl] => Array
    (
      [1x] => myimage-2600x1462-x81y78-85.jpg.webp
      [2x] => myimage-5200x2925-x81y78-85.jpg.webp
    )

  )

  [mime] => image/webp
)

```

## 3.7. pf_display()

`pf_display($image, $args=array())`

### 3.7.1. Arguments

`$image (int)` ID de l'image dans wordpress

`$args (array)` Voir [section Args](#args)

### 3.7.2. Retour (string)

retourne une balise `<picture>` basé sur le tableau de pf_get

Example de retour:

```html
<picture>
  <source
    data-srcset="https://website.com/wp-content/uploads/pictifly/2018/12/image-1600x900-x81y78-85.jpg.webp 1x, https://website.com/wp-content/uploads/pictifly/2018/12/image-3200x1800-x81y78-85.jpg.webp 2x"
    media="(min-width: 1200px)"
    type="image/webp"
    srcset="
      https://website.com/wp-content/uploads/pictifly/2018/12/image-1600x900-x81y78-85.jpg.webp  1x,
      https://website.com/wp-content/uploads/pictifly/2018/12/image-3200x1800-x81y78-85.jpg.webp 2x
    "
  />
  <source
    data-srcset="https://website.com/wp-content/uploads/pictifly/2018/12/image-1024x576-x81y78-85.jpg.webp 1x, https://website.com/wp-content/uploads/pictifly/2018/12/image-2048x1152-x81y78-85.jpg.webp 2x"
    media="(min-width: 768px)"
    type="image/webp"
    srcset="
      https://website.com/wp-content/uploads/pictifly/2018/12/image-1024x576-x81y78-85.jpg.webp  1x,
      https://website.com/wp-content/uploads/pictifly/2018/12/image-2048x1152-x81y78-85.jpg.webp 2x
    "
  />
  <source
    data-srcset="https://website.com/wp-content/uploads/pictifly/2018/12/image-768x432-x81y78-85.jpg.webp 1x, https://website.com/wp-content/uploads/pictifly/2018/12/image-1536x864-x81y78-85.jpg.webp 2x"
    media="(min-width: 0px)"
    type="image/webp"
    srcset="
      https://website.com/wp-content/uploads/pictifly/2018/12/image-768x432-x81y78-85.jpg.webp  1x,
      https://website.com/wp-content/uploads/pictifly/2018/12/image-1536x864-x81y78-85.jpg.webp 2x
    "
  />

  <img
    class="pf_background_img lazyloaded"
    src="https://website.com/wp-content/uploads/pictifly/2018/12/image-768x432-x81y78-85.jpg.webp"
    data-src="https://website.com/wp-content/uploads/pictifly/2018/12/image-768x432-x81y78-85.jpg.webp"
  />
</picture>
```

# 4. Args

Argument pour les fonction pf_get et pf_display

## 4.1. Valeurs par default

```php
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
```

## 4.2. `crop` (bool)

Est-ce que l'on autorise l'image à être rognée pour atteindre exactement la taille spécifiée
Default: `false`

## 4.3. `breakpoints` (array)

**PENSEZ MOBILE FIRST !!!**  
Comme les srcset sont défini en min-width définissez d'abord le breakpoint xs

`key`: xs | sm | md | lg | xl | xxl

**Valeurs possible pour chaque clefs**

`false` _(default)_

`(number)` La largeur de l'image en px (non retina)

`(array)` array($width,$height)

**Example:**

```php
'breakpoints' => array(
  'xs'	=> array(null, 400),
  'sm'	=> 600,
  'lg'	=> array(900, 900)
)
```

## 4.4. `ratio` (array)

`false` _(default)_

`(array)` array($width,$height)

Example:

`'ratio' => array(16,9)`

## 4.5. `webp` (bool)

Utilisation du format webp

`false`

`true`_(default)_

# 5. Filtres arguments

## 5.1. Comment modifier les arguments par default en dehors du plugin

```php
add_filter('pf_default_args', 'pf_default_args_filter');
function pf_default_args_filter($default){
    $default['quality']=10; // ou n'importe quel élément du tableau d'argument par default (voir plus haut)
    return $default;
}
```

# 6. Configuration par défault

```php
$configs=array(
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
)
```

## 6.1. Comment modifier une configuration par default

```php
add_filter('pf_configs', 'pf_configs_filters');
function pf_configs_filters($default){
    $default['lazyload']=false;
    return $default;
}
```

# 7. LazyLoad

Nous utilisons LazySize, plus d'infos: [https://github.com/aFarkas/lazysizes](https://github.com/aFarkas/lazysizes)

## 7.1. Comment Désactiver LazyLoad

Voir [comment modifier une configuration par default](#comment-modifier-une-configuration-par-default)

# 8. Regeneration d'image en backoffice

Par default pictifly regénère les images à la une des post type attaché à une taille d'image.  
Vous pouvez également ajouter d'autres image lors de cette régénération

## 8.1 Attacher des post types à la régénération

```php
add_filter('pf_post_type_regenerate', 'prefix_pf_post_type_regenerate', 1, 1);
function prefix_pf_post_type_regenerate($post_types){
    $post_types=['custom_type'];
    return $post_types;
}
```

## 8.2 Créer une méthode de regénération personnalisée

```php
add_action('pf_post_images_regenerate', 'prefix_pf_post_images_regenerate',10, 2);
function prefix_pf_post_images_regenerate($post_id, $post_type){
    global $_pf_regenerated_imgs;
    if($post_type=="custom_type"){
        $thumb_id = get_post_thumbnail_id($post_id);
        if($thumb_id){
            $_pf_regenerated_imgs[] = pf_get_simple($thumb_id, 200, 200, true);
        }
        $images = get_field('images', $post_id);
        if($images){
            foreach($images as $image){
                $_pf_regenerated_imgs[] = pf_img($image, "custom_size", false);
                $_pf_regenerated_imgs[] = pf_get_simple($image, 200, 200, true);
                $_pf_regenerated_imgs[] = pf_get_simple($image, 400, 400, true);
                $_pf_regenerated_imgs[] = pf_get_simple($image, 600, 600, true);
            }
        }
    }
}
```
