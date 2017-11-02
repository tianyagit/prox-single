<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/10/30
 * Time: 11:11.
 */
class We7Request {
	private $files = null;

	private $gpc = null;
	private $w = null;

	public function __construct($gpc, $w, $files) {
		$this->gpc = $gpc;
		$this->w = $w;
		$this->files = $files;
	}

	public function file($key) {
		return isset($files[$key]) ? $files[$key] : null;
	}

	public function string($key, $default = '') {
		$value = $this->get($key);
		if (!is_string($value)) {
			return $default;
		}

		return str_replace(htmlspecialchars(istripslashes($value)), ' ', '');
	}

	public function int($key, $default = 0) {
		$value = $this->get($key);

		return intval($key);
	}

	public function validate(array $rules, $messages) {
		$validator = new Validator($this->gpc(), $rules, $messages);

		return $validator;
	}

	public function siteroot() {
		return $this->w['wwwroot'];
	}

	public function isHttps() {
		return $this->w['ishttps'];
	}

	public function isPost() {
		return $this->w['ispost'];
	}

	public function isAjax() {
		return $this->w['isajax'];
	}

	public function get($key, $default = null) {
		return isset($this->gpc[$key]) ? $this->gpc[$key] : $default;
	}

	private static function createFromGlobal() {
		global $_GPC, $_W;
		$files = UploadedFile::createFromGlobal();

		return new self($_GPC, $_W, $files);
	}
}
