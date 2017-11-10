<?php

namespace We7\V163;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1510292752
 * @version 1.6.3
 */

class CreateAccountPc {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_tableexists('account_pc')){
			$sql = <<<EOT
				CREATE TABLE `ims_account_pc` (
  `acid` int(11) DEFAULT NULL,
  `uniacid` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT;

			pdo_query($sql);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		