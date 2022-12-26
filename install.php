<?php
define('CURRENT_VERSION', 1.2);

if(!is_writable('upload/')) die('Please set the permissions of the <b>upload</b> folder to 755.');

require 'config.php';

@mysql_connect(DB_HOST, DB_USER, DB_PASS) or die("Couldn't connect to MySQL server (connection error), please look at <b>config.php</b> file.");
@mysql_select_db(DB_NAME) or die("Couldn't connect to MySQL server (no such database), please look at <b>config.php</b> file.");

// Get current version
$version = (float) file_exists('upload/version.txt')?file_get_contents('upload/version.txt'):0;

if($version == CURRENT_VERSION) die(CURRENT_VERSION);

// Update to 1.0
if($version < 1)
{
	mysql_query('CREATE TABLE IF NOT EXISTS `album` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`upload_id` char(13) COLLATE utf8_bin DEFAULT NULL,
		`date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		UNIQUE KEY `upload_id_UNIQUE` (`upload_id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin');
	
	mysql_query("CREATE TABLE `image` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`album_id` int(10) unsigned DEFAULT NULL,
		`extension` varchar(4) COLLATE utf8_bin DEFAULT NULL,
		`size` int(11) DEFAULT NULL,
		`views` int(10) unsigned DEFAULT '0',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin");
}

// Update to 1.2
if($version < 1.2)
{
	mysql_query('ALTER TABLE `image` ADD COLUMN `date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `views`');
	mysql_query('UPDATE `image` SET `date`=(SELECT `date` FROM `album` WHERE `id`=`image`.`album_id`)');
}

// Finish
file_put_contents('upload/version.txt', CURRENT_VERSION);

echo $version?'Update successful!':'Install successful!';