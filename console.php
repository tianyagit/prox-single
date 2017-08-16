<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/10
 * Time: 16:24
 */
include './vendor/autoload.php';
include __DIR__.'/framework/bootstrap.inc.php';
include __DIR__.'/framework/bootstrap.inc.php';
$update = new Upgrade();
dump($update->getShaFiles());
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
		return Sha1Util::getUpgradeSha1FileInfo();
	}

	/**
	 *  获取git sha_file
	 */
	private function getRemoteShaFile() {

	}
}

class Sha1Util {
	/**
	 *  获取当前 upgrade目录的 文件指纹
	 *  return array('目录名'=>'sha1')
	 */
	public static function getUpgradeSha1FileInfo() {
		$directory = new \RecursiveDirectoryIterator(__DIR__."\\upgrade");
		$iterator = new \RecursiveIteratorIterator($directory);
		$files  = array();
		foreach ($iterator as $splfileinfo) {
			if($splfileinfo->getFilename()== 'upgrade.php') {
				$path = $splfileinfo->getPath();
				$sha1 = sha1_file($splfileinfo->getRealPath());
				$dirname = pathinfo($path, PATHINFO_BASENAME);
				$files[$dirname] = $sha1;
			}
		}
		return $files;
	}

	/**
	 *  获取指定版本的 数据库文件指纹
	 * @param $version
	 */
	public function getRemoteSha1FileInfo($version) {

	}
}



