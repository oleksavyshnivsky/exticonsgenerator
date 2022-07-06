<?php

class App {
	protected $controller = 'home';
	protected $method = 'index';
	protected $params = [];

	public function __construct() {
		// URL 
		$url = $this->parseURL();
		$url_i = 0;

		// Контролер
		if (!isset($url[$url_i])) $url[$url_i] = '';
		if (file_exists(DIR_APP.'/controllers/' . $url[$url_i] . '.php')) {
			$this->controller = $url[$url_i];
			unset($url[$url_i]);
			$url_i++;
		}

		require_once DIR_APP.'/controllers/' . $this->controller . '.php';
		$this->controller = new $this->controller();

		// Метод
		if (isset($url[$url_i])) {
			if (own_method($this->controller, $url[$url_i])) {
				$this->method = $url[$url_i];
				unset($url[$url_i]);
			}
		}

		$this->params = $url ? array_values($url) : [];
		call_user_func_array([$this->controller, $this->method], $this->params);
	}

	protected function parseURL() {
		if (isset($_GET['url'])) {
			return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
		}
	}
}
