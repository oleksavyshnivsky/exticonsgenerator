<?php

// Часовий пояс
date_default_timezone_set('Europe/Kiev');

include DIR_APP.'/core/functions/functions.php';
include DIR_APP.'/core/functions/secure.php';
include DIR_APP.'/core/functions/special.php';

// Перевірка на перехід в адмінку
if (PHP_SAPI !== 'cli') define('SITE_URL', siteURL());

$GLOBALS['msgbox'] = [];

require_once DIR_CONFIG.'/lang.php';
require_once DIR_CONFIG.'/config.php';
require_once DIR_APP.'/core/App.php';
require_once DIR_APP.'/core/Controller.php';
