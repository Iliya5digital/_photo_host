<?php
require '../config.php';
require 'functions.php';

mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME);

$id = mysql_real_escape_string($_GET['id']);

if(strlen($id) != 13) die();

if(mysql_query('INSERT INTO `album` (`upload_id`) VALUES ("'.$id.'")'))
{
	echo alphaID(mysql_insert_id());
}