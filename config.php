<?php
// ���� ������
define('DB_HOST', 'sql112.hstn.me');	 # ������ localhost
define('DB_USER', 'mseet_32244780');       # ���� ��� ������������ MySQL
define('DB_PASS', 'KsNZMjnwitRD');	     # MySQL ������ ������������
define('DB_NAME', 'mseet_32244780_333');       # ��� ���� ������ MySQL

// Site
define('TITLE', 'ImageServe');	# �������� �����
define('LANG', 'ru');			# ���� ����� (���� ������ ���� � �������� lang )

/**
 * default: true
 */
$config['auto_lang'] = true; // true|false

/*
	Google AdSense
	
	��� ������������� Google AdSense, �������� ���� � �������� view adsense.html (view / adsense.html) � ��������
    ������� ������������� (300x250) ��� ���������� .
	
	
	���������� �� view/ _direct.html ��� ���������� � ���, ��� �������� ������� ��� ������ ��������
    �� ��������, ������� ���������� ����������� � ������ �������.
*/

// Files
define('MAX_ALBUM_SIZE',	15);	# ������������ ���������� �����������, ������� ����� ���� ��������� ������������.
define('MAX_FILE_SIZE',	2097152);	# ������������ ������ ����� � ������ (B), � ��������� ����� 2 ��

// ����������, ��������� install.php ����� �� ����� ���������� ������������, � ����� ���������� ����� �������.