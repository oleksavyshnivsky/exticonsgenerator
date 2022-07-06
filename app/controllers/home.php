<?php

class home extends Controller {
	function index() {
		$filenames = [];
		$zipfile = '';

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$folder = DIR_UPLOADS . date('Ymd/His/');

			// Завантажене зображення
			$image = $_FILES['image']['tmp_name'];
			$name = trim(filter_input(INPUT_POST, 'name'));
		
			resize(16, $folder.$name.'16', $image);
			resize(32, $folder.$name.'32', $image);
			resize(48, $folder.$name.'48', $image);
			resize(128, $folder.$name.'128', $image);

			$filenames = [
				$folder.$name.'16.png',
				$folder.$name.'32.png',
				$folder.$name.'48.png',
				$folder.$name.'128.png'
			];

			// Створення архіву
			$zipfile = $folder.'icons.zip';
			$zip = new ZipArchive();
			if (!$zip->open($zipfile, ZIPARCHIVE::CREATE)) exit(_('Error while creating ZIP-archive'));

			// Додавання дампу у zip-архів
			foreach ($filenames as $filename) {
				$zip->addFile($filename, basename($filename));
			}

			// Закриття архіву
			$zip->close();

			$this->success = true;
		}

		$this->view('home', [
			'filenames'	=>	$filenames,
			'zipfile'	=>	$zipfile
		]);
	}
}