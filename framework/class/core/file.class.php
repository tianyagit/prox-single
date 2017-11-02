<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/11/1
 * Time: 13:33.
 */
class FileSystem {
	private $driverName;

	public function __construct($driverName = 'disk') {
		$this->$driverName = in_array($driverName, array('cos3', 'cos4', 'qiniu', 'disk')) ? $driver : 'disk';
	}

	public function put($path, $contennt) {
		file_put_contents($path, $contennt);
	}

	public function putFile($path, $filename) {
		$content = file_read($filename);
		$this->put($path, $content);
	}

	public function get($path) {
		return file_get_contents($path);
	}

	public function delete($path) {
		unlink($path);
	}

	public function createDriver() {
	}
}
class Cos4Stream {
	public function stream_open($path, $mode, $options, &$opened_path) {
		$this->path = $this->clearPrefix($path);

		return true;
	}

	public function unlink($path) {
		$this->api()->unlink($this->clearPrefix($path));
	}

	public function stream_write() {
	}
}
