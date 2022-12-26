<?php
// Variables
$alpha = $_GET['a'];
$method = $_GET['m'];
$extension = $_GET['e'];

// Configuration
$methods = array
(
	'original'	=>	true,
	'small'		=>	array(80, 80),
	'medium'	=>	array(612, 0),
	'square'	=>	array(186, 186)
);

$extensions = array
(
	'jpg'	=>	'jpeg',
	'png'	=>	'png',
	'gif'	=>	'gif',
	'wbmp'	=>	'wbmp'
);

// First validation
if(!isset($methods[$method])) exit();
if(!isset($extensions[$extension])) exit();

// ID
require 'functions.php';

$id = alphaID($alpha, true);

// Original
if($method == 'original' && file_exists('../view/direct.html'))
{
	$path = dirname(dirname($_SERVER['SCRIPT_NAME']));
	
	if($path === '\\') $path = '';
	
	$src = $path.'/upload/'.$method.'/'.$id.'.'.$extension;
	
	require '../view/direct.html';
	
	exit();
}

// Cache
if(file_exists($path = '../upload/'.$method.'/'.$id.'.'.$extension))
{
	header('Content-Type: image/'.$extensions[$extension]);
	
	readfile($path);
	
	exit();
}

// Second validation
$path = '../upload/original/'.$id.'.'.$extension;

if(!file_exists($path)) exit();

// Output
function output()
{
	global $id, $extensions, $extension, $method, $resized;
	
	$function = 'image'.$extensions[$extension];
	
	header('Content-Type: image/'.$extensions[$extension]);
	
	imagealphablending($resized, false);
	imagesavealpha($resized, true);
	
	$function($resized);
	
	imagealphablending($resized, false);
	imagesavealpha($resized, true);
	
	$function($resized, '../upload/'.$method.'/'.$id.'.'.$extension);
	
	exit();
}

// Initialize
$function = 'imagecreatefrom'.$extensions[$extension];

$image = $function($path);

$width = imagesx($image);
$height = imagesy($image);

$r_width = $methods[$method][0];
$r_height = $methods[$method][1];

// Don't embiggen the image
if((!$r_width || $width <= $r_width) && (!$r_height || $height <= $r_height))
{
	$resized = $image;
	
	output();
}

// Find resize position and dimensions
if(!$r_width)
{
	$r_width = $width*$r_height/$height;
} elseif(!$r_height)
{
	$r_height = $height*$r_width/$width;
}

if($height*$r_width/$width >= $r_height)
{
	$cut_width = $width;
	$cut_height = round($r_height*$width/$r_width);
	
	$src_x = 0;
	$src_y = floor(($height - $cut_height)/2);
} else
{
	$cut_width = round($r_width*$height/$r_height);
	$cut_height = $height;
	
	$src_x = floor(($width - $cut_width)/2);
	$src_y = 0;
}

// Create resized image
$resized = imagecreatetruecolor($r_width, $r_height);

imagecopyresampled($resized, $image, 0, 0, $src_x, $src_y, $r_width, $r_height, $cut_width, $cut_height);

output();