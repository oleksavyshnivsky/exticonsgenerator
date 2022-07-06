<?php

// ————————————————————————————————————————————————————————————————————————————————
// Security
// ————————————————————————————————————————————————————————————————————————————————

// ————————————————————————————————————————————————————————————————————————————————
// Random number
// ————————————————————————————————————————————————————————————————————————————————
function GetRandInt($max){
    if(function_exists('openssl_random_pseudo_bytes') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || substr(PHP_OS, 0, 3) !== 'WIN')) {
         do{
             $result = floor($max*(hexdec(bin2hex(openssl_random_pseudo_bytes(4)))/0xffffffff));
         }while($result == $max);
    } else {
        $result = mt_rand( 0, $max );
    }
    return (int)$result;
}

// ————————————————————————————————————————————————————————————————————————————————
// Random string
// ————————————————————————————————————————————————————————————————————————————————
function randomString($length = 11) {
    if(function_exists('openssl_random_pseudo_bytes') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || substr(PHP_OS, 0, 3) !== 'WIN')) {
        $stronghash = openssl_random_pseudo_bytes(15);
    } else $stronghash = hash("sha256", uniqid( mt_rand(), TRUE ));
    //$salt = str_shuffle("ABCHEFGHJKMNPQRSTUVWXYZabchefghjkmnpqrstuvwxyz0123456789".hash("sha256", $stronghash. microtime()));
    $salt = str_shuffle("abchefghjkmnpqrstuvwxyz0123456789".hash("sha256", $stronghash. microtime())."ABCDEFGHIJKLMNOPQRSTUVWXYZ");

    $randomString = "";
    for($i = 0; $i < $length; $i ++) {
        $randomString .= $salt[GetRandInt(72)];
    }
    return $randomString;
}

// ————————————————————————————————————————————————————————————————————————————————
// Генератор токену для підтвердження дії користувача
// Джерело: http://forums.devshed.com/php-faqs-and-stickies-167/the-6-worst-sins-of-security-938991.html
// important! this has to be a crytographically secure random generator 
// ————————————————————————————————————————————————————————————————————————————————
function generate_secure_token($length = 16) { 
    return bin2hex(openssl_random_pseudo_bytes($length));
} 

// ————————————————————————————————————————————————————————————————————————————————
// Очистка тексту перед HTML-виводом
// ————————————————————————————————————————————————————————————————————————————————
function e(?string $raw_input): string {
    if (!$raw_input) return '';
    if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
        return htmlspecialchars($raw_input, ENT_QUOTES | ENT_HTML401, 'UTF-8'); 
    } else {
        return htmlspecialchars($raw_input, ENT_QUOTES, 'UTF-8'); 
    }
}
