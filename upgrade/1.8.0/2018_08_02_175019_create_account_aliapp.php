<?php

namespace We7\V180;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1533203419
 * @version 1.8.0
 */

class CreateAccountAliapp {

	/**
	 *  执行更新
	 */
	public function up( {
		if (!pdo_tableexists('account_aliapp')) {
			pdo_run("CREATE TABLE IF NOT EXISTS `ims_account_aliapp` (
`acid` int(10) unsigned NOT NULL,
`uniacid` int(10) unsigned NOT NULL,
`level` tinyint(4) unsigned NOT NULL DEFAULT '0',
`name` varchar(30) NOT NULL,
`description` varchar(255) NOT NULL,
`key` varchar(16) NOT NULL,
PRIMARY KEY (`acid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		}
	}

	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		