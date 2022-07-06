<?php

chdir(__DIR__);

session_start(['cookie_samesite'=>'Lax']);
ob_start();

if (file_exists('test.txt') or strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define('TEST', true);
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') define('AJAX', true);

if (defined('TEST')) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

// define('HTTPROOT', '/exticonsgenerator');

define('DIR_APP', 'app');
define('DIR_CONFIG', 'config');
define('DIR_COMMON', 'app');
require_once DIR_APP.'/init.php';

$app = new App;
