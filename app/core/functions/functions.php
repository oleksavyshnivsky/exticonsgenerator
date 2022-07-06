<?php

// ————————————————————————————————————————————————————————————————————————————————
// ФУНКЦІЇ
// ————————————————————————————————————————————————————————————————————————————————

// ————————————————————————————————————————————————————————————————————————————————
// Переклади з урахуванням контексту
// http://www.php.net/manual/de/book.gettext.php#89975
// ————————————————————————————————————————————————————————————————————————————————
function __($string, $context) {
	$contextString = "{$context}\004{$string}";
	$translation = gettext($contextString);
	return ($translation === $contextString) ? $string : $translation;
}

// ————————————————————————————————————————————————————————————————————————————————
// ————————————————————————————————————————————————————————————————————————————————
function sessionStart() {
	session_start(['cookie_samesite'=>'Lax']);

	if (!isset($_SESSION['CREATED'])) {
		$_SESSION['CREATED'] = time();
	} elseif (time() - $_SESSION['CREATED'] > 1800) {
		// session started more than 30 minutes ago
		session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
		$_SESSION['CREATED'] = time();  // update creation time
	}
}

// ————————————————————————————————————————————————————————————————————————————————
// Адреса сайту з протоколом
// ————————————————————————————————————————————————————————————————————————————————
function siteURL() {
	// if (PHP_SAPI === 'cli') return false;
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
	$domainName = $_SERVER['HTTP_HOST'];
	return $protocol.$domainName;
}

// ————————————————————————————————————————————————————————————————————————————————
// Safe file names
// ————————————————————————————————————————————————————————————————————————————————
function getSafeFileName($file) {
	// Remove anything which isn't a word, whitespace, number
	// or any of the following caracters -_~,;:[]().
	$file = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $file);
	// Remove any runs of periods (thanks falstro!)
	$file = preg_replace("([\.]{2,})", '', $file);

	return $file;
}

// ————————————————————————————————————————————————————————————————————————————————
// 
// ————————————————————————————————————————————————————————————————————————————————
function writeSerializedVars($file, $data) {
	$fp = fopen(DIR_APP."/../system/cache/{$file}.php", 'wb+');
	fwrite($fp, serialize($data));
	fclose($fp);
	@chmod(DIR_APP."/../system/cache/{$file}.php", 0666);
}

function readSerializedVars($file) {
	return unserialize(@file_get_contents(DIR_APP."/../system/cache/{$file}.php"));
}

// ————————————————————————————————————————————————————————————————————————————————
// Виставлення коржиків
// ————————————————————————————————————————————————————————————————————————————————
if (PHP_SAPI !== 'cli') {
	$domain_cookie = explode ('.', $_SERVER['HTTP_HOST']);
	$domain_cookie_count = count($domain_cookie);
	$domain_allow_count = -2;

	if ($domain_cookie_count > 2) {
		if (in_array($domain_cookie[$domain_cookie_count-2], array('com', 'net', 'org'))) $domain_allow_count = -3;
		if ($domain_cookie[$domain_cookie_count-1] == 'ua') $domain_allow_count = -3;
		$domain_cookie = array_slice($domain_cookie, $domain_allow_count);
	}

	$domain_cookie = '.' . implode ('.', $domain_cookie);
	// $domain_cookie = implode ('.', $domain_cookie);

	if (ip2long($_SERVER['HTTP_HOST']) != -1 AND ip2long($_SERVER['HTTP_HOST']) !== FALSE) define( 'DOMAIN', null );
	else define('DOMAIN', $domain_cookie);
}

function set_cookie($name, $value, $expires = 365) {
	$expires = $expires ? time() + ($expires * 86400) : false;
	if (PHP_VERSION < 5.2)
		setcookie($name, $value, $expires, '/', DOMAIN . '; HttpOnly');
	elseif (PHP_VERSION < 7.3)
		setcookie($name, $value, $expires, '/', DOMAIN, FALSE, TRUE);
	else
		setcookie($name, $value, [
			'expires'	=>	$expires,
			'path'		=>	'/',
			'domain'	=>	DOMAIN,
			'secure'	=>	false,
			'httponly'	=>	true,
			'samesite'	=>	'Lax',
		]);
}

// ————————————————————————————————————————————————————————————————————————————————
// Інформаційне повідомлення
// На вивід піде глобальна змінна GLOBALS['msgbox']
// ————————————————————————————————————————————————————————————————————————————————
function msgbox($title, $message, $style = 'danger') {
	// $GLOBALS['msgbox'][] = ['options'=>['title'=>$title, 'message'=>$message], 'settings'=>['type'=>$style]];
	$function = $style === 'danger' ? 'error' : $style;
	$GLOBALS['msgbox'][] = ['title' => $title, 'text' => $message, 'style' => $style, 'function' => $function];
}

function error($message) { msgbox(_('Error'), $message, 'danger'); }
function notify($message) { msgbox(_('Notification'), $message, 'info'); }
function success($message) { msgbox(_('Success'), $message, 'success'); }
function warning($message) { msgbox(_('Warning'), $message, 'warning'); }

// ————————————————————————————————————————————————————————————————————————————————
// Опції на вибір з масиву Ключ => Значення
// ————————————————————————————————————————————————————————————————————————————————
function select_options($array, $selected_key = false, $is_numeric = false) {
	$options = '';
	if ($is_numeric and !is_numeric($selected_key)) {
		foreach ($array as $key => $value) {
			$options .= "<option value=\"{$key}\">{$value}</option>";
		}
	} else {
		foreach ($array as $key => $value) {
			$selected = $key == $selected_key ? 'selected': '';
			$options .= "<option value=\"{$key}\" {$selected}>{$value}</option>";
		}
	}
	return $options;
}

// ————————————————————————————————————————————————————————————————————————————————
// Перенаправлення на [Адреса сайту][HTTPROOT]$path
// ————————————————————————————————————————————————————————————————————————————————
function redirect(string $path = '/') {
	$location = filter_var($path, FILTER_VALIDATE_URL);
	if (!$location) {
		if (strpos($path.'/', HTTPROOT.'/') !== 0) $path = HTTPROOT . $path;
		$location = $path;
	}

	// Збереження інформаційних повідомлень у сесії
	if (!isset($_SESSION['msgbox'])) $_SESSION['msgbox'] = [];
	$_SESSION['msgbox'] = array_merge((array)$_SESSION['msgbox'], (array)$GLOBALS['msgbox']);

	header("location: {$location}");
	exit;
}

// ————————————————————————————————————————————————————————————————————————————————
// Перенеправлення на URL, вказаний у GET['returnurl'] (або на URL за умовчанням)
// ————————————————————————————————————————————————————————————————————————————————
function redirect2returnurl(string $default = '/') {
	// Перевірка, чи це наш URL
	if (isset($_REQUEST['returnurl']) and $_REQUEST['returnurl']) {
		$location = $_REQUEST['returnurl'];
		$host = parse_url($location, PHP_URL_HOST);
		if ($host and $host !== $_SERVER['HTTP_HOST']) $location = $default;
	} else {
		$location = $default;
	}

	redirect($location);
}

// ————————————————————————————————————————————————————————————————————————————————
// Перенаправлення на сторінку входу
// ————————————————————————————————————————————————————————————————————————————————
function redirect2login() {
	header("HTTP/1.1 401 Unauthorized");
	msgbox(_('Note'), _('You must be logged in to see this page.'), 'warning');
	redirect('/signin/?returnurl=' . rawurlencode($_SERVER['REQUEST_URI']));
}

function redirectOnError($errorMsg = '', $path = '/error404') {
	msgbox(_('Error'), $errorMsg ?: _('Something went wrong.'));
	redirect($path);
}

function redirectBack() {
	if ((isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))) {
		if (strtolower(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) == strtolower($_SERVER['HTTP_HOST'])) {
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
	redirect();
}

// ————————————————————————————————————————————————————————————————————————————————
// Очистка телефонного номеру
// ————————————————————————————————————————————————————————————————————————————————
function clearPhoneNumber($phone) {
	$phone = preg_replace('/[^\d+;,]/', '', $phone);
	if (!$phone) return '';

	if (mb_substr($phone, 0, 1) == '0') {
		$phone = '+38' . $phone;
	} elseif (mb_substr($phone, 0, 2) == '80') {
		$phone = '+3' . $phone;
	} elseif (mb_substr($phone, 0, 3) == '380') {
		$phone = '+' . $phone;
	} elseif (mb_substr($phone, 0, 1) != '+') {
		$phone = '+' . $phone;
	}

	$phone = str_replace(';', '; ', $phone);
	$phone = str_replace(',', ', ', $phone);

	return $phone;
}


// ————————————————————————————————————————————————————————————————————————————————
// 
// ————————————————————————————————————————————————————————————————————————————————
function arrayExclude($array, Array $excludeKeys){
	foreach($array as $key => $value){
		if(!in_array($key, $excludeKeys)){
			$return[$key] = $value;
		}
	}
	return $return;
}


// ————————————————————————————————————————————————————————————————————————————————
// 
// ————————————————————————————————————————————————————————————————————————————————
function own_method($class_name, $method_name) {
	if (method_exists($class_name, $method_name) and is_callable([$class_name, $method_name])) {
		$parent_class = get_parent_class($class_name);
		if ($parent_class !== false) return !method_exists($parent_class, $method_name);
		return true;
	}
	else return false;
}

// ————————————————————————————————————————————————————————————————————————————————
// Скорочення довгої текстівки
// ————————————————————————————————————————————————————————————————————————————————
function shorten(?string $in, int $len = 50) {
	if (!$in) return '';
	return mb_strlen($in) > $len ? mb_substr($in, 0, $len) . '...' : $in;
}

// ————————————————————————————————————————————————————————————————————————————————
// Перевірка каптчі
// ————————————————————————————————————————————————————————————————————————————————
function checkReCaptcha() {
	$recaptcha = new \ReCaptcha\ReCaptcha(RECAPTCHA_SECRET);
	$resp = $recaptcha->setExpectedHostname($_SERVER['HTTP_HOST'])
					  ->setExpectedAction('submit')
					  ->setScoreThreshold(0.5)
					  ->verify(filter_input(INPUT_POST, 'g-recaptcha-response'));
					  // ->verify(filter_input(INPUT_POST, 'g-recaptcha-response'), getUserIpAddr());

	// echo $resp->getScore();
	return $resp->isSuccess();
	// $errors = $resp->getErrorCodes();
}

// ————————————————————————————————————————————————————————————————————————————————
// Посилання на список
function makeListlink($sublink) {
	return filter_input(INPUT_GET, 'list', FILTER_DEFAULT, ['options'=>['default'=>base64_encode(HTTPROOT.$sublink)]]);
}

// ————————————————————————————————————————————————————————————————————————————————
// Інформація про IP-адресу
// ————————————————————————————————————————————————————————————————————————————————
function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
	$output = NULL;
	if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
		$ip = $_SERVER["REMOTE_ADDR"];
		if ($deep_detect) {
			if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
				$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
	}
	$purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
	$support    = array("country", "countrycode", "state", "region", "city", "location", "address");
	$continents = array(
		"AF" => "Africa",
		"AN" => "Antarctica",
		"AS" => "Asia",
		"EU" => "Europe",
		"OC" => "Australia (Oceania)",
		"NA" => "North America",
		"SA" => "South America"
	);
	if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
		$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
		if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
			switch ($purpose) {
				case "location":
					$output = array(
						"city"           => @$ipdat->geoplugin_city,
						"state"          => @$ipdat->geoplugin_regionName,
						"country"        => @$ipdat->geoplugin_countryName,
						"country_code"   => @$ipdat->geoplugin_countryCode,
						"continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
						"continent_code" => @$ipdat->geoplugin_continentCode
					);
					break;
				case "address":
					$address = array($ipdat->geoplugin_countryName);
					if (@strlen($ipdat->geoplugin_regionName) >= 1)
						$address[] = $ipdat->geoplugin_regionName;
					if (@strlen($ipdat->geoplugin_city) >= 1)
						$address[] = $ipdat->geoplugin_city;
					$output = implode(", ", array_reverse($address));
					break;
				case "city":
					$output = @$ipdat->geoplugin_city;
					break;
				case "state":
					$output = @$ipdat->geoplugin_regionName;
					break;
				case "region":
					$output = @$ipdat->geoplugin_regionName;
					break;
				case "country":
					$output = @$ipdat->geoplugin_countryName;
					break;
				case "countrycode":
					$output = @$ipdat->geoplugin_countryCode;
					break;
			}
		}
	}
	return $output;
}

// ————————————————————————————————————————————————————————————————————————————————
// Чистка HTML-коду
// ————————————————————————————————————————————————————————————————————————————————
function purify(string $text): string {
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	return $purifier->purify($text);
}


// ————————————————————————————————————————————————————————————————————————————————
// User-inputted MD => Cleaned HTML
// ————————————————————————————————————————————————————————————————————————————————
function md2html(string $md): string {
	$Parsedown = new Parsedown();
	$body = $Parsedown->text($md);
	return purify($body);
}


// ————————————————————————————————————————————————————————————————————————————————
// IP-адреса користувача
// ————————————————————————————————————————————————————————————————————————————————
function getUserIpAddr(){
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		//ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		//ip pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

// ————————————————————————————————————————————————————————————————————————————————
// LANGUAGE
// ————————————————————————————————————————————————————————————————————————————————

// ————————————————————————————————————————————————————————————————————————————————
// Числовий ідентифікатор мови 
// ————————————————————————————————————————————————————————————————————————————————
function lang_id(?string $code = ''): int {
	if (!$code) return 0;
	foreach (SITELANGS as $lang_id => $params) if ($params['code'] === $code) return $lang_id;
	return $_SESSION['lang_id'] ?? 0;
}

// ————————————————————————————————————————————————————————————————————————————————
// 
// ————————————————————————————————————————————————————————————————————————————————
function setSessionLanguage() {
	// Мова береться з URL
	if (in_array($hl = filter_input(INPUT_GET, 'hl'), SITELANGCODES)) {
		$lang_id = lang_id($hl);
	} elseif (isset($_COOKIE['language']) and in_array($_COOKIE['language'], SITELANGCODES)) {
		$lang_id = lang_id($_COOKIE['language']);
	} elseif (isset($_SESSION['lang_id'])) {
		$lang_id = (int)$_SESSION['lang_id'];
	} elseif (isset(User::$lang_id) and is_numeric(User::$lang_id)) {
		$lang_id = (int)User::$lang_id;
	} else {
		$lang_id = getBestLanguageForUser();
	}

	if (!is_numeric($lang_id)) $lang_id = 0;

	$_SESSION['lang_id'] = $lang_id;
	set_cookie('language', SITELANGS[$lang_id]['code'], 365);
}

// ————————————————————————————————————————————————————————————————————————————————
// Визначення серед доступних на сайті мов найкращої для користувача
// ————————————————————————————————————————————————————————————————————————————————
function getBestLanguageForUser(): int {
	$langs = [];
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		// розбиття рядка на частини (мови і значення q-фактора)
		preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
		if (count($lang_parse[1])) {
			// створення списку на зразок 'en' => 0.8
			$langs = array_combine($lang_parse[1], $lang_parse[4]);
			// виставлення значення за замовчуванням 1 усім мовам, для яких не вказаний q-фактор
			foreach ($langs as $lang => $val) {
				if ($val === '') $langs[$lang] = 1;
			}
			// сортування масиву мов за значенням q-фактора 
			arsort($langs, SORT_NUMERIC);
		}
	}
	// перебір мов користувача, щоб знайти серед них ту, що доступна на сайті
	foreach ($langs as $lang => $val) {
		if (array_key_exists($lang, LANG_REDIRECTION) and LANG_REDIRECTION[$lang]) return lang_id(LANG_REDIRECTION[$lang]);
	}
	// Повернення мови за замовчуванням
	return 0;
}

// ————————————————————————————————————————————————————————————————————————————————
// Налаштування середовища — мова локалі
// ————————————————————————————————————————————————————————————————————————————————
function setLanguage($lang_id = false) {
	$lang_id = $_SESSION['lang_id'];

	putenv('LC_ALL=' . SITELANGS[$lang_id]['locale']);
	setlocale(LC_ALL, SITELANGS[$lang_id]['setlocale']);
	
	// Ім’я файлів з текстівками
	// $domain_name = 'messages';
	$domain_name = SITELANGS[$lang_id]['locale'];

	// Верхня директорія з перекладами
	bindtextdomain($domain_name, DIR_APP.'/../language');
	
	// domain
	textdomain($domain_name);
	bind_textdomain_codeset($domain_name, 'UTF-8');
}


// ————————————————————————————————————————————————————————————————————————————————
// 
// ————————————————————————————————————————————————————————————————————————————————
function render($view, $data = []) {
	ob_start();
	extract($data);
	include DIR_APP . '/views/' . $view . '.php';
	return ob_get_clean(); 
}


// ————————————————————————————————————————————————————————————————————————————————
function getDoc($link, $notify = true) {
	// Скачування сторінки
	$gamepage = file_get_contents($link);
	if ($notify) echo $link, PHP_EOL;

	// Читання DOM
	$doc = str_get_html($gamepage);
	if (!is_object($doc)) exit("Download Error\n");

	return $doc;
}


// ————————————————————————————————————————————————————————————————————————————————
// Форматування числа
// ————————————————————————————————————————————————————————————————————————————————
function fnum($number) {
	echo number_format($number, 0, ',', '&nbsp;');
}


// ————————————————————————————————————————————————————————————————————————————————
// Лист для підтвердження обліковки 
// ————————————————————————————————————————————————————————————————————————————————
function sendVerificationEmail(int $user_id, string $email, int $lang_id) {
	// Створення посилання на генерацію нового паролю
	$token = generate_secure_token();
	$link = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/emailverification/'.$user_id.'/'.$token;
	// Видалення старого токена
	DB::query("DELETE FROM users_tokens WHERE user_id = {$user_id} AND type_id = 'emailverification'");
	// Збереження нового токена
	DB::query("INSERT INTO users_tokens(user_id, type_id, token) VALUES({$user_id}, 'emailverification', ?)", 's', [$token]);
	// Надсилання листа з посиланням на генерацію паролю
	$mail = Controller::coremodel('mail');
	$mail->getTemplate('emailverification', $lang_id);
	$mail->setValue('website', $_SERVER['HTTP_HOST']);
	$mail->setValue('link', $link);
	$mail->addAddress($email);
	return $mail->send();
}

// ————————————————————————————————————————————————————————————————————————————————
// 
// ————————————————————————————————————————————————————————————————————————————————
function generateUrlSlug($string, $maxlen=0) {
	$string = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($string)), '-');
	if ($maxlen && strlen($string) > $maxlen) {
		$string = substr($string, 0, $maxlen);
		$pos = strrpos($string, '-');
		if ($pos > 0) {
			$string = substr($string, 0, $pos);
		}
	}
	return $string;
}