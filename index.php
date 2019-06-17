<?php require('wp_octree_picture.php'); ?>
<?php require('demo/header.php'); ?>
<?php 
	if(!function_exists('wp_upload_dir')){
		echo '<h1 style="color:red;">Vous êtes en dehors de WordPress</h1>';
	}
?>
<?php 
	$args=array(
	'crop'	=> true,
	'breakpoints' => array(
		'xs'	=> array(100, 100),	// width on xs screen  (default: false)
		'md'	=> array(1800,1800),
		'lg'	=> array(1800,1800),
		'xl'	=> array(1800,1800)
	),
	'webp'	=> false,
	'retina' => 2,
);
echo pf_display('images/sources/01.jpg', $args);
?>

<?php 
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
echo pf_display('images/sources/01.jpg', $args);
?>
<?php 

$args=array(
	'crop'	=> true,
	'breakpoints' => array(
		'xs'	=> array(400, null),	// width on xs screen  (default: false)
		'sm'	=> array(600, null),	// width on sm screen  (default: false)
	),
	'webp'	=> true,
	'retina' => 3,
);
echo pf_display('images/sources/02.jpg', $args);
$args=array(
	'crop'	=> true,
	'breakpoints' => array(
		'lg'	=> array(900, 900, 'top'),	// width on xs screen  (default: false)
		'xs'	=> array(400, 400, 'top'),	// width on xs screen  (default: false)
		'sm'	=> array(600, 600, 'top'),	// width on sm screen  (default: false)
	),
	'webp'	=> true,
	'retina' => 3,
);
echo pf_display('images/sources/01.png', $args);


$args=array(
	'crop'	=> true,
	'position' => 'top',
	'breakpoints' => array(
		'xs'	=> 200,	// width on xs screen  (default: false)
		'sm'	=> 400,	// width on sm screen  (default: false)
		'md'	=> 600,	// width on md screen  (default: false)
		'lg'	=> 800,	// width on lg screen  (default: false)
		'xl'	=> 1000,	// width on xl screen  (default: false)
	),
	'ratio'	=> array(2, 1),
	'webp'	=> true,
	'retina' => 3,
);

echo '<div>'.pf_display('images/sources/02.png', $args).'</div>';



	$args=array(
	'crop'	=> true,
	'breakpoints' => array(
		'xs'	=> array(100, 100),	// width on xs screen  (default: false)
		'md'	=> array(1820,1820),
		'lg'	=> array(1820,1820),
	),
	'webp'	=> false,
	'retina' => 2,
);
echo pf_display('images/sources/01.jpg', $args);


require('demo/footer.php'); ?>
