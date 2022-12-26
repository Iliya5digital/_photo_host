<?php
session_start();

require 'config.php';
require 'php/functions.php';

require 'lang/'.LANG.'.php';

@mysql_connect(DB_HOST, DB_USER, DB_PASS) or die('Please view the <b>config.php</b> file.');
@mysql_select_db(DB_NAME) or die('Please view the <b>config.php</b> file.');

define('WEB', ($_SERVER['SCRIPT_NAME'] == '/gallery.php')?'/':dirname($_SERVER['SCRIPT_NAME']).'/');
define('HTTP', 'http://'.$_SERVER['HTTP_HOST'].WEB);

// Check
$month = mysql_fetch_row(mysql_query('SELECT COUNT(*) FROM `album` WHERE `upload_id` IS NULL AND `date` > NOW() - INTERVAL 1 MONTH'));

if(!$month[0])
{
	$week = array(0);
	$today = array(0);
} else
{
	$week = mysql_fetch_row(mysql_query('SELECT COUNT(*) FROM `album` WHERE `upload_id` IS NULL AND `date` > NOW() - INTERVAL 1 WEEK'));
	
	if(!$week[0])
	{
		$today = array(0);
	} else
	{
		$today = mysql_fetch_row(mysql_query('SELECT COUNT(*) FROM `album` WHERE `upload_id` IS NULL AND `date` > NOW() - INTERVAL 1 DAY'));
	}
}

// Gallery
$order = isset($_GET['order'])?$_GET['order']:'new';
$range = isset($_GET['range'])?$_GET['range']:'all';
$page = isset($_GET['page'])?((int) $_GET['page']):1;

$query = ' FROM `image` WHERE (SELECT `upload_id` FROM `album` WHERE `id`=`image`.`album_id`) IS NULL';

// Range
switch($range)
{
	default:
		$range = 'all';
		break;
	case 'month':
		if($month[0]) $query .= ' AND `date` > NOW() - INTERVAL 1 MONTH';
		break;
	case 'week':
		if($week[0]) $query .= ' AND `date` > NOW() - INTERVAL 1 WEEK';
		break;
	case 'today':
		if($today[0]) $query .= ' AND `date` > NOW() - INTERVAL 1 DAY';
		break;
}

if($range != 'all')
{
	$check = $$range;
	
	if(!$check[0]) $range = 'all';
}

// Page
$total = mysql_fetch_row(mysql_query('SELECT COUNT(*)'.$query));
$pages = ceil($total[0]/25);

if($page < 1) $page = 1;
if($page > $pages) $page = $pages;

$min = $page - 4;
$max = $page + 4;

if($min < 1)
{
	$max += 1 - $min;
	$min = 1;
}

if($max > $pages)
{
	$min += $pages - $max;
	
	if($min < 1) $min = 1;
	
	$max = $pages;
}

// Order
switch($order)
{
	default:
		$order = 'new';
		
		$query .= ' ORDER BY `date` DESC';
		break;
	case 'popular':
		
		$query .= ' ORDER BY `views` DESC';
		break;
}

$_SESSION['order'] = $order;
$_SESSION['range'] = $range;
$_SESSION['page'] = $page;

$images = mysql_query('SELECT `id`, `extension`'.$query.' LIMIT '.($page*25-25).',25');

require 'view/gallery.html';