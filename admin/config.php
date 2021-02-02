<?php
/* Constante de configurare */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Bucharest');

define('IMAGE_MAX_WIDTH', '800');
define('IMAGE_MAX_HEIGHT', '800');

define('INTERNAL_PATH', 'disertatie/admin/');
define('UPLOADS_FOLDER_NAME', 'uploads/');
define('LOGS_FOLDER_NAME', 'logs/');
define('UPLOADS_FOLDER', INTERNAL_PATH.UPLOADS_FOLDER_NAME);
define('ROOT_URL', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/');
define('SITE_URL', ROOT_URL.INTERNAL_PATH);

define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'disertatie');

define('DEFAULT_USER_NAME', 'admin');
define('DEFAULT_USER_PASSWORD', 'admin1234');
