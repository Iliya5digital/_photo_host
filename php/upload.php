<?php
require '../config.php';

mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME);

$types = array('jpeg', 'png', 'gif', 'wbmp');
$extensions = array('image/jpeg'=>'jpg', 'image/png'=>'png', 'image/gif'=>'gif', 'image/wbmp'=>'wbmp');

// Upload
function upload($id, $mime, $size)
{
	global $extensions;
	
	if(mysql_num_rows($result = mysql_query('SELECT `id` FROM `album` WHERE `upload_id`="'.mysql_real_escape_string($id).'"')))
	{
		$album = mysql_fetch_row($result);
		
		mysql_query('INSERT INTO `image` (`album_id`, `extension`, `size`) VALUES ('.$album[0].', "'.$extensions[$mime].'", '.$size.')');
		
		return '../upload/original/'.mysql_insert_id().'.'.$extensions[$mime];
	}
	
	return false;
}

// File API
if(isset($_SERVER['HTTP_ID']))
{
	// Validate
	$data = @getimagesize('php://input');
	
	if(!$data) die();
	
	if(!isset($extensions[$data['mime']])) die();
	
	$contents = file_get_contents('php://input');
	
	$size = strlen($contents);
	
	if($size > MAX_FILE_SIZE) die();
	
	// Upload
	if($path = upload($_SERVER['HTTP_ID'], $data['mime'], $size))
	{
		file_put_contents($path, $contents);
	}
} else

// Web
if(isset($_POST['id']))
{
	sleep(5);
	
	// Validate
	$file = @fopen($_POST['url'], 'rb');
	
	if(!$file) die('0');
	
	$type = false;
	$size = false;
	
	$headers = stream_get_meta_data($file);
	
	foreach($headers['wrapper_data'] as $header)
	{
		if(!$type && substr($header, 0, 12) == 'Content-Type') $type = substr($header, 14);
		if(!$size && substr($header, 0, 14) == 'Content-Length') $size = (int) substr($header, 16);
		
		if($type && $size) break;
	}
	
	if(!$type || !$size) die('0');
	
	if(!isset($extensions[$type])) die('1');
	if($size > MAX_FILE_SIZE) die('2');
	
	// Upload
	if($path = upload($_POST['id'], $type, $size))
	{
		file_put_contents($path, stream_get_contents($file));
	} else
	{
		echo ' '.$size;
	}
} else

// Iframe
if(isset($_GET['id']))
{
	// Validate
	if(!isset($_FILES['image'])) die('2');
	
	$file = $_FILES['image'];
	
	if($file['error'] == 1 || $file['size'] > MAX_FILE_SIZE) die('2');
	if($file['error'] || !isset($extensions[$file['type']])) die('1');
	
	// Upload
	if($path = upload($_GET['id'], $file['type'], $file['size']))
	{
		move_uploaded_file($file['tmp_name'], $path);
	} else
	{
		echo ' '.$file['size'];
	}
}