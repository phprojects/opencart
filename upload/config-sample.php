<?php
define('SITE_HOST',$_SERVER['HTTP_HOST']);
define('DIR_PUBLIC',__DIR__);
define('DIR_ROOT',dirname(__DIR__));

// HTTP
define('HTTP_SERVER', 'http://'.SITE_HOST.'/');

// HTTPS
define('HTTPS_SERVER', 'http://'.SITE_HOST.'/');

// DIR
define('DIR_APPLICATION', DIR_PUBLIC.'/catalog/');
define('DIR_SYSTEM', DIR_PUBLIC.'/system/');
define('DIR_IMAGE', DIR_PUBLIC.'/image/');
define('DIR_STORAGE', DIR_ROOT.'/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'pdo');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'opencart');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');