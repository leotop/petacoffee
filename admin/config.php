<?php
// HTTP
$blowfish_key ='3be60871f3b8897a8196825dc164ce8d';

define('HTTP_SERVER', 'http://'.$_SERVER['HTTP_HOST'].'/admin/');
define('HTTP_CATALOG', 'http://'.$_SERVER['HTTP_HOST'].'/');

// HTTPS
define('HTTPS_SERVER', 'http://'.$_SERVER['HTTP_HOST'].'/admin/');
define('HTTPS_CATALOG', 'http://'.$_SERVER['HTTP_HOST'].'/');

// DIR
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('DIR_APPLICATION', DOCUMENT_ROOT . '/admin/');
define('DIR_SYSTEM', DOCUMENT_ROOT . '/system/');
define('DIR_DATABASE', DOCUMENT_ROOT . '/system/database/');
define('DIR_LANGUAGE', DOCUMENT_ROOT . '/admin/language/');
define('DIR_TEMPLATE', DOCUMENT_ROOT . '/admin/view/template/');
define('DIR_CONFIG', DOCUMENT_ROOT . '/system/config/');
define('DIR_IMAGE', DOCUMENT_ROOT . '/image/');
define('DIR_CACHE', DOCUMENT_ROOT . '/system/cache/');
define('DIR_DOWNLOAD', DOCUMENT_ROOT . '/download/');
define('DIR_LOGS', DOCUMENT_ROOT . '/system/logs/');
define('DIR_CATALOG', DOCUMENT_ROOT . '/catalog/');

// DB
define('DB_DRIVER', 'mysql');
define('DB_PREFIX', '');
switch ($_SERVER['HTTP_HOST'])
{
	case 'petacoffee.local':
		define('DB_HOSTNAME', 'localhost');
		define('DB_USERNAME', 'petacoffee');
		define('DB_PASSWORD', 'petacoffee');
		define('DB_DATABASE', 'petacoffee_oc');
	break;
	default:
		define('DB_HOSTNAME', '10.5.16.12');
		define('DB_USERNAME', 'onlyopencart');
		define('DB_PASSWORD', 'MySQL0p3nT1m3');
		define('DB_DATABASE', 'test_petacoffee');
		
}
?>