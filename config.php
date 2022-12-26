<?php
// База данных
define('DB_HOST', 'sql112.hstn.me');	 # Обычно localhost
define('DB_USER', 'mseet_32244780');       # Ваше имя пользователя MySQL
define('DB_PASS', 'KsNZMjnwitRD');	     # MySQL пароль пользователя
define('DB_NAME', 'mseet_32244780_333');       # Имя базы данных MySQL

// Site
define('TITLE', 'ImageServe');	# Название сайта
define('LANG', 'ru');			# Язык сайта (файл должен быть в каталоге lang )

/**
 * default: true
 */
$config['auto_lang'] = true; // true|false

/*
	Google AdSense
	
	Для использования Google AdSense, создайте файл в каталоге view adsense.html (view / adsense.html) и вставьте
    средний прямоугольник (300x250) код объявления .
	
	
	Посмотрите на view/ _direct.html там инструкции о том, как добавить рекламу или другие элементы
    На странице, которая показывает изображение в полном размере.
*/

// Files
define('MAX_ALBUM_SIZE',	15);	# Максимальное количество изображений, которые могут быть загружены одновременно.
define('MAX_FILE_SIZE',	2097152);	# Максимальный размер файла в байтах (B), в настоящее время 2 МБ

// Пожалуйста, запустите install.php сразу же после завершения конфигурации, и перед посещением сайта удалите.