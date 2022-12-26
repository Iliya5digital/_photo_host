<?php
session_start();

require 'config.php';
require 'php/functions.php';

require 'lang/'.LANG.'.php';

@mysql_connect(DB_HOST, DB_USER, DB_PASS) or die('Please view the <b>config.php</b> file.');
@mysql_select_db(DB_NAME) or die('Please view the <b>config.php</b> file.');

define('WEB', ($_SERVER['SCRIPT_NAME'] == '/index.php')?'/':dirname($_SERVER['SCRIPT_NAME']).'/');
define('HTTP', 'http://'.$_SERVER['HTTP_HOST'].WEB);

// Album request
if(isset($_GET['a']))
{
	$album_id = alphaID($_GET['a'], true);
	
	if(mysql_num_rows($result = mysql_query('SELECT `date`, `upload_id` FROM `album` WHERE `id`='.$album_id)))
	{
		$row = mysql_fetch_row($result);
		
		$album_date = $row[0];
		
		if($row[1])
		{
			mysql_query('UPDATE `album` SET `upload_id`=NULL WHERE `id`='.$album_id);
		}
		
		$album = mysql_query('SELECT `id`, `extension`, `size`, `views` FROM `image` WHERE `album_id`='.$album_id.' ORDER BY `id` DESC');
		
		$image = mysql_fetch_assoc($album);
		
		if(mysql_num_rows($album) == 1)
		{
			unset($album);
		} else
		{
			mysql_data_seek($album, 0);
		}
	}
} else

// Image request
if(isset($_GET['i']))
{
	$image_id = alphaID($_GET['i'], true);
	
	if(mysql_num_rows($result = mysql_query('SELECT `id`, `album_id`, `extension`, `size`, `views` FROM `image` WHERE `id`='.$image_id)))
	{
		$image = mysql_fetch_assoc($result);
		
		$row = mysql_fetch_row(mysql_query('SELECT `date` FROM `album` WHERE `id`='.$image['album_id']));
		
		$album_date = $row[0];
		
		$album = mysql_query('SELECT `id`, `extension` FROM `image` WHERE `album_id`='.$image['album_id'].' ORDER BY `id` DESC');
		
		$album_id = $image['album_id'];
		
		if(mysql_num_rows($album) == 1) unset($album);
	}
}

if(isset($image))
{
	$alpha = alphaID($image['id']);
	
	mysql_query('UPDATE `image` SET `views`='.(++$image['views']).' WHERE `id`='.$image['id']);
	
	# Slides
	if(isset($album))
	{
		$slide = isset($_GET['slide'])?((int) $_GET['slide']):-1;
		
		$max_slide = ceil(mysql_num_rows($album)/6) - 1;
		
		if($slide < 0 || $slide > $max_slide)
		{
			$index = 0;
			
			while($tmp = mysql_fetch_assoc($album))
			{
				$index++;
				
				if($tmp['id'] == $image['id']) break;
			}
			
			$slide = ceil($index/6) - 1;
			
			mysql_data_seek($album, 0);
		}
	}
	
	require 'view/album.html';
} else
{
	$recent = mysql_query('SELECT `id` FROM `album` WHERE `upload_id` IS NULL ORDER BY `id` DESC LIMIT 0,12');
	
	$total_albums = mysql_fetch_row(mysql_query('SELECT COUNT(*) FROM album'));
	$total_images = mysql_fetch_row(mysql_query('SELECT COUNT(*) FROM image'));
	
	$total_amounts = sprintf($lang['total_amounts'],
		'<span>'.sprintf($lang['total_albums'], number_format($total_albums[0], 0, '.', $lang['thousand_separator'])).'</span>',
		'<span>'.sprintf($lang['total_images'], number_format($total_images[0], 0, '.', $lang['thousand_separator'])).'</span>'
	);
	
	require 'view/upload.html';
}