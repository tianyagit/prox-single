<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/10
 * Time: 16:24
 */
include './vendor/autoload.php';
include __DIR__.'/framework/bootstrap.inc.php';

$update = new Upgrade();
$update->getShaFiles();
class Upgrade {

	/**
	 *  获取当前版本
	 */
	private function getCurrentVersion() {
		return IMS_VERSION;
	}


	public function update($toVersion) {
		$result = version_compare($this->getCurrentVersion(), $toVersion,'>');
		if ($result) { //可以更新

		}
	}
	/**
	 *
	 */
	public function getShaFiles() {
		$directory = new \RecursiveDirectoryIterator(__DIR__.'/upgrade/');
		$iterator = new \RecursiveIteratorIterator($directory);
		foreach ($iterator as $object) {
			if($object->getFilename()== 'upgrade.php') {
				$sha = sha1_file($object->getRealPath());
			}
		}
	}

	/**
	 *  获取git sha_file
	 */
	private function getRemoteShaFile() {

	}


}



