<?php 
/**
 * Мовна конфігурація сайту
 */

// Мови сайту
const SITELANGS = [
	0 => [
		'name'		=>	'Українська',
		'locale'	=>	'uk_UA', // putenv
		'setlocale'	=>	'uk_UA.UTF8',
		'img'		=>	'ua',
		'code'		=>	'uk',
	],
	1 => [
		'name'		=>	'English',
		'locale'	=>	'en_US',
		'setlocale'	=>	'en_US.UTF8',
		'img'		=>	'gb',
		'code'		=>	'en',
	],
];

// Залежність "Мова браузера" — "Мова сайту"
const LANG_REDIRECTION = [
	'uk'        =>  'uk',
	'uk-UA'     =>  'uk',
	'ru'        =>  'uk',
	'ru-RU'     =>  'uk',
	'ru-MO'     =>  'uk',
	'en'        =>  'en',
	'en-GB'     =>  'en',
	'en-US'     =>  'en',
	'default'   =>  'uk',
];


function getLangCodeArray() {
	return array_filter(array_combine(array_keys(SITELANGS), array_column(SITELANGS, 'code')));
}

function checkLangCode($code) {
	return in_array($code, array_column(SITELANGS, 'code')) ? $code : '';
}

