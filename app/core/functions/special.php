<?php

function resize($newWidth, $targetFile, $originalFile) {

	$info = getimagesize($originalFile);
	$mime = $info['mime'];

	switch ($mime) {
			case 'image/jpeg':
					$image_create_func = 'imagecreatefromjpeg';
					$image_save_func = 'imagepng';
					$new_image_ext = 'png';
					break;

			case 'image/png':
					$image_create_func = 'imagecreatefrompng';
					$image_save_func = 'imagepng';
					$new_image_ext = 'png';
					break;

			case 'image/gif':
					$image_create_func = 'imagecreatefromgif';
					$image_save_func = 'imagepng';
					$new_image_ext = 'png';
					break;

			default: 
					throw new Exception('Unknown image type.');
	}

	$img = $image_create_func($originalFile);
	list($width, $height) = getimagesize($originalFile);

	$newHeight = ($height / $width) * $newWidth;
	$tmp = imagecreatetruecolor($newWidth, $newHeight);
	imagealphablending($tmp, false);
	imagesavealpha($tmp, true);
	imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

	if (file_exists($targetFile)) {
			unlink($targetFile);
	}

	if (!file_exists(dirname($targetFile))) {
		mkdir(dirname($targetFile), 0777, true);
	}

	$image_save_func($tmp, "$targetFile.$new_image_ext", 0, PNG_ALL_FILTERS);
}


// ————————————————————————————————————————————————————————————————————————————————
// 
// ————————————————————————————————————————————————————————————————————————————————
function url($a = [], $params = []) {
// function url($controller, $method = '', $id = null, $params = []) {
	if (isset($params['redirect'])) {
		$params['redirect'] = $_SERVER['REQUEST_URI'];
	}
	if (isset($params['returnurl'])) {
		$params['returnurl'] = $_SERVER['REQUEST_URI'];
	}

	// $url = HTTPROOT.'/'.implode('/', array_filter((array)$a));
	$url = HTTPROOT.'/'.implode('/', (array)$a);
	if ($params) $url .= '?' . http_build_query($params);
	// if (!$params and $list = filter_input(INPUT_GET, 'list')) $url .= '?list=' . rawurlencode($list);
	return $url;
}
