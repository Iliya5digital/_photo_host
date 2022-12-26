<?php
require '../config.php';

mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME);

$id = mysql_real_escape_string($_GET['id']);

if(strlen($id) != 13) die();

if(mysql_num_rows($result = mysql_query('SELECT `id` FROM `album` WHERE `upload_id`="'.$id.'"')))
{
	$album = mysql_fetch_row($result);
	
	mysql_query('DELETE FROM `album` WHERE `id`='.$album[0]);
	
	$images = mysql_query('SELECT `id`, `extension` FROM `image` WHERE `album_id`='.$album[0]);
	
	while($image = mysql_fetch_row($images))
	{
		unlink('../upload/'.$image[0].'.'.$image[1]);
	}
	
	mysql_query('DELETE FROM `image` WHERE `album_id`='.$album[0]);
}