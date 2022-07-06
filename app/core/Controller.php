<?php

class Controller {
	protected string $title = '';	// Заголовок сторінки
	protected string $description = '';	// SEO: Опис сторінки
	protected string $keywords = '';	// SEO: Ключові слова

	protected bool $success = true;	// Чи запит виконаний успішно
	protected ?string $action;			// Дія, замовлена у POST-запиті
	protected ?string $returnurl;		// Посилання, на яке потрібно перейти після виконання POST-запиту (якщо задане)

	protected bool $userecaptcha;

	const PERPAGE = 20;

	// ————————————————————————————————————————————————————————————————————————————————
	// 
	// ————————————————————————————————————————————————————————————————————————————————
	public function __construct() {
		// Часті змінні
		$this->action = $_SERVER['REQUEST_METHOD'] === 'DELETE' ? 'delete' : filter_input(INPUT_POST, 'action');
		
		// Посилання, на яке потрібно перейти після виконання POST-запиту (якщо задане)
		$this->returnurl = filter_input(INPUT_GET, 'returnurl');

		// Якщо це POST-запит
		if ($_SERVER['REQUEST_METHOD'] !== 'GET') $this->success = false;

		// 
		$this->list = HTTPROOT.'/'.get_class($this);
		if ($tmp = filter_input(INPUT_GET, 'list')) $this->list .= '?' . (BTOALIST ? base64_decode($tmp) : $tmp);
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// Підключення моделі
	// ————————————————————————————————————————————————————————————————————————————————
	public function model($model, $params = false) {
		require_once DIR_APP.'/models/' . $model . '.php';
		$model = explode('/', $model);
		$model = end($model);
		return is_array($params) ? new $model(...$params) : new $model($params);
	}

	public static function coremodel($model, $params = false) {
		require_once DIR_COMMON.'/core/classes/' . $model . '.class.php';
		return is_array($params) ? new $model(...$params) : new $model($params);
	}

	
	// ————————————————————————————————————————————————————————————————————————————————
	// Сторінкування
	// ————————————————————————————————————————————————————————————————————————————————
	public function pagination($from, $where, $limit = 20, $url = '', $dataoa = 'data-oa data-oa-target="#list-wrapper;#main" data-oa-history data-oa-scroll') {
		require_once DIR_COMMON.'/core/classes/pagination.class.php';
		return new Pagination($from, $where, $limit, $url, $dataoa);
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// Вид
	// ————————————————————————————————————————————————————————————————————————————————
	public function subview($view, $data = []) {
		extract($data);
		include DIR_APP.'/views/' . $view . '.php';
	}


	public function render($view, $data = []) {
		ob_start();
		extract($data);
		include DIR_APP.'/views/' . $view . '.php';
		return ob_get_clean(); 
	}


	protected function view($mainview = 'home/index', $maindata = [], $title = false) {
		if ($title) $this->title = $title;

		// Alerts
		if (isset($_SESSION['msgbox']) and !empty($_SESSION['msgbox'])) {
			$GLOBALS['msgbox'] = array_merge($_SESSION['msgbox'], $GLOBALS['msgbox']);
			unset($_SESSION['msgbox']);
		}

		// Неочікуваний вивід
		$unplannedContent = ob_get_clean();
		// Очікуваний вивід
		$content = $unplannedContent.($mainview ? $this->render($mainview, $maindata): '');

		// Відповідь сервера
		if (defined('AJAX')) {
			header('Content-Type: application/json; charset=utf-8');
			exit(json_encode([
				'alerts'=>	$GLOBALS['msgbox'],
				'html'	=>	$this->success ? $content : '',
				'success'	=>	$this->success,
				'title'	=>	$this->success ? $this->title : '',
			]));
		} else {
			$this->subview('index', ['content' => $content]);
		}
		exit;
	}


	public function viewjson($data = []) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(array_merge([
			'alerts'	=>	$GLOBALS['msgbox'],
			'success'	=>	$this->success,
		], $data));
		exit;
	}


	public function viewerror($msg = '') {
		msgbox(_('Error'), $msg ?: _('Something went wrong'));
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode([
			'alerts'	=>	$GLOBALS['msgbox'],
			'success'	=>	$this->success,
		]);
		exit;
	}


	public function viewstatus($status, $msg = '') {
		if ($status === 200)
			msgbox(_('Information'), $msg ?: _('Action has been done'), 'success');
		elseif ($status === 404)
			msgbox(_('Error'), $msg ?: _('Page was not found'));
		else
			msgbox(_('Error'), $msg ?: _('Something went wrong'));

		// Alerts
		if (isset($_SESSION['msgbox']) and !empty($_SESSION['msgbox'])) {
			$GLOBALS['msgbox'] = array_merge($_SESSION['msgbox'], $GLOBALS['msgbox']);
			unset($_SESSION['msgbox']);
		}

		// Неочікуваний вивід
		$unplannedContent = ob_get_clean();
		// Відповідь сервера
		if (defined('AJAX')) {
			$response = [
				'html'	=>	'<div class="container">'.$unplannedContent.$this->render('templates/message').'</div>',
				'alerts'=>	$GLOBALS['msgbox'],
				'success'	=>	$this->success,
			];
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode($response);
		} else {
			http_response_code($status);
			$this->subview('index', ['content' => '<div class="container">'.$unplannedContent.$this->render('templates/message').'</div>']);
		}
		exit;
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// 
	// ————————————————————————————————————————————————————————————————————————————————
	public function userror($msg = 'Something went wrong') {
		echo json_encode(['error' => $msg]);
		exit();
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// $name = season | race | track_id
	// ————————————————————————————————————————————————————————————————————————————————
	private function getSelectedItem($name, $input, $default) {
		$this->$name = filter_input(INPUT_GET, $input, FILTER_VALIDATE_INT);
		if (!$this->$name and isset($_SESSION[$name])) $this->$name = (int)$_SESSION[$name];
		if (!$this->$name) $this->$name = (int)$default;
		$_SESSION[$name] = $this->$name;
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// 
	// ————————————————————————————————————————————————————————————————————————————————
	function checkpermit($maxRole4OwnData = 5, $maxRole4DataOfOthers = 5) {
		$this->user_id = filter_input(INPUT_GET, 'uid', FILTER_VALIDATE_INT) or $this->user_id = User::$id;
		if (User::$id === $this->user_id) {
			if (User::$role_id > $maxRole4OwnData) redirectOnError(_('Access denied'));
		} else { // User::$id !== $this->user_id
			if (User::$role_id > $maxRole4DataOfOthers) {
				if (isset($_GET['url'])) {
					$part1 = $_GET['url'];
					unset($_GET['url']);
				} else $part1 = '';
				
				unset($_GET['uid']);
				$part2 = http_build_query($_GET);
				redirectOnError(_('Access denied'), '/'.$part1.($part2?'?'.$part2:''));
			}
		}
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// 
	// ————————————————————————————————————————————————————————————————————————————————
	protected function url($a = [], $list = false, $b = []) {
		$a = (array)$a;
		if (static::class !== 'home') array_unshift($a, static::class);

		if ($list) {
			$b['list'] = filter_input(INPUT_GET, 'list');
			if (!$b['list']) {
				$b['list'] = $_GET;
				unset($b['list']['url']);
				if ($b['list']) {
					$b['list'] = http_build_query($b['list']);
					if (BTOALIST) $b['list'] = base64_encode($b['list']);
				} else unset($b['list']);
			}
		}

		return url($a, $b);
	}

	protected function listurl() {
		$location = filter_input(INPUT_GET, 'list');
		if ($location) {
			$parsed = parse_url($location);
			if (isset($parsed['host']) and $parsed['host'] !== $_SERVER['HTTP_HOST']) $location = HTTPROOT . '/' . static::class;
		} else $location = HTTPROOT . '/' . static::class;
		return $location;
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// Перенеправлення на URL, вказаний у GET['list']
	// ————————————————————————————————————————————————————————————————————————————————
	protected function redirect2list() {
		$search = filter_input(INPUT_GET, 'list');
		redirect(HTTPROOT.'/'.static::class.($search?'?'.(BTOALIST?base64_decode($search):$search):''));
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// Редактор
	// ————————————————————————————————————————————————————————————————————————————————
	public function _edit($id = false) {
		$item = $this->model(static::MODEL, $id);
		$item->read();
		if ($id and !$item->id) $this->viewstatus(404);

		$this->view(static::class.'/edit', [
			'item'	=>	$item,
		], _('Editor'));
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// Переклад
	// $template = show | edit
	// ————————————————————————————————————————————————————————————————————————————————
	public function _t($id, $lang_id, $template = 'show') {
		$item = $this->model(static::MODEL, $id);
		$item->read();
		if ($id and !$item->id) $this->viewstatus(404);

		$t = $this->model(static::MODEL.'T',[$id, $lang_id]);
		$t->read();

		switch ($this->action) {
			case 'save':
				if (!$t->save())
					$this->viewerror($t->error);
				else {
					success(_('Data have been saved'));
					$this->success = true;
					$template = 'show';
				}
				break;
		}

		$this->view('templates/t'.$template, [
			'item'	=>	$item,
			't'		=>	$t,
		], _('Translation'));
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// Переклад
	// ————————————————————————————————————————————————————————————————————————————————
	public function _tpublish($id, $lang_id) {
		$t = $this->model(static::MODEL.'T',[$id, $lang_id]);
		$t->read();

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (!$t->publish())
				$this->viewerror($t->error);
			else {
				success(_('Data have been saved'));
				$this->success = true;
			}
		}

		$this->view('templates/tpublish', [
			't'		=>	$t,
		], _('Translation'));
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// 
	// ————————————————————————————————————————————————————————————————————————————————
	public function _publish($id = false) {
		$item = $this->model(static::MODEL, $id);
		$item->read();
		if (!$item->id) $this->viewstatus(404);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (!$item->publish())
				$this->viewerror($item->error);
			else {
				success($item->msg);
				$this->success = true;
			}
		}

		$this->view('templates/publish', [
			'item'	=>	$item,
		], _('Editor'));
	}


	// ————————————————————————————————————————————————————————————————————————————————
	// 
	// ————————————————————————————————————————————————————————————————————————————————
	protected function _smallaction($id, $action) {
		$item = $this->model(static::MODEL, $id);
		$item->read();
		if (!$item->id) $this->viewstatus(404);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (!$item->{$action}())
				$this->viewerror($item->error);
			else {
				success($item->msg?:_('Action has been done'));
				$this->success = true;
			}
		}
		$this->view('templates/'.$action, [
			'item'	=>	$item,
		], '');
	}

	// ————————————————————————————————————————————————————————————————————————————————
	// $this->sps і SOS мають бути задані у класі-нащадку
	// ————————————————————————————————————————————————————————————————————————————————
	protected function checkSP($sp) {
		return array_key_exists($sp, $this->sps) ? $sp : array_keys($this->sps)[0];
	}

	// protected function checkSO($so) {
	// 	return array_key_exists($so, static::SOS) ? static::SOS[$so] : static::SOS[0];
	// }


	// ————————————————————————————————————————————————————————————————————————————————
	// Читання даних з масиву $data або GET-запиту 
	// ————————————————————————————————————————————————————————————————————————————————
	// function filter($filters, $data = false) {
	// 	$data = $data ? filter_var_array($data, $filters) : filter_input_array(INPUT_GET, $filters);
	// 	foreach ($data as $key => $value) $this->$key = $value;
	// }
}
