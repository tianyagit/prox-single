<?php

namespace We7\V181;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1534849355
 * @version 1.8.1
 */

class DeleteAccountClassFile {

	/**
	 *  执行更新
	 */
	public function up() {
		$files = array(
			'account.class.php',
			'aliapp.account.class.php',
			'phoneapp.account.class.php',
			'webapp.account.class.php',
			'weixin.account.class.php',
			'weixin.platform.class.php',
			'wxapp.account.class.php',
			'wxapp.platform.class.php',
			'wxapp.work.class.php',
			'xzapp.account.class.php',
			'xzapp.platform.class.php',
		);
		foreach ($files as $file) {
			$file = IA_ROOT . '/framework/class/' . $file;
			if (file_exists($file)) {
				unlink($file);
			}
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		