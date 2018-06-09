<?php

namespace We7\V175;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1528257262
 * @version 1.7.5
 */

class CreateAccountXzapp {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_tableexists('account_xzapp')) {
			$table_name = tablename('account_xzapp');
			$sql = <<<EOF
CREATE TABLE `ims_account_xzapp` (
`acid` int(11) NOT NULL DEFAULT '0',
`uniacid` int(11) DEFAULT NULL,
`name` varchar(255) DEFAULT '',
PRIMARY KEY (`acid`),
KEY `uniacid` (`uniacid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOF;
			pdo_query($sql);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		