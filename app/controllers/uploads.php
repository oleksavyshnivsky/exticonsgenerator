<?php

class uploads extends Controller {
	function index($ymd = '', $his = '', $basename = '') {
		$filename = DIR_UPLOADS.$ymd.'/'.$his.'/'.$basename;

		if (!file_exists($filename)) {
			header('HTTP/1.1 404 Not Found');
			exit();
		}

		$content = file_get_contents($filename);

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type = finfo_file($finfo, $filename);
		header('Content-Type: '.$type.'; charset=utf-8');
		header('Content-Disposition: inline;');
		echo $content;
	}
}