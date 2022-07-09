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

			if(!file_exists($_FILES['image']['tmp_name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
			    error('Файл не завантажений');
			} elseif (!$name) {
			    error('Назва не задана');
			} else {
				$SIZES = [16, 32, 48, 128];
				foreach($SIZES as $i => $size) {
					$IMGFILES[] = resize($size, $folder.$name.$size, $image);
					$IMGFILENAMES[] = $name.$size.'.png';
				}

				// Створення архіву
				// $zipfile = $folder.'icons.zip';
				$zipfilename_tech = tempnam(sys_get_temp_dir(), "FOO");
				$zipfilename_human = $name.'.zip';


				$zip = new ZipArchive();
				if (!$zip->open($zipfilename_tech, ZIPARCHIVE::OVERWRITE)) exit(_('Error while creating ZIP-archive'));

				// Додавання зображень у zip-архів
				foreach ($IMGFILENAMES as $i => $filename) {
					// $zip->addFile($filename, basename($filename));
					$zip->addFromString(basename($filename), $IMGFILES[$i]);
				}

				// Закриття архіву
				$zip->close();

				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$type = finfo_file($finfo, $zipfilename_tech);
				header('Content-Type: '.$type.'; charset=utf-8');
				header('Content-Disposition: inline; filename="'.$zipfilename_human.'"');
				echo file_get_contents($zipfilename_tech);
				exit;
			}


			// $this->success = true;
		}

		$this->view('home', [
			// 'filenames'	=>	$filenames,
			'zipfile'	=>	$zipfile
		]);
	}
}