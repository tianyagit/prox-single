<?php

namespace We7\V177;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1531119071
 * @version 1.7.7
 */

class AlterAccountXzappAddColumn {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('account_xzapp', array('token', 'encodingaeskey', 'xzapp_id', 'level', 'key', 'secret'))) {
			$table_name = tablename('account_xzapp');
			$sql = <<<EOT
				ALTER TABLE {$table_name} ADD `token` varchar(32) NOT NULL ;
				ALTER TABLE {$table_name} ADD `encodingaeskey` varchar(255) NOT NULL ;
				ALTER TABLE {$table_name} ADD `xzapp_id` varchar(30) NOT NULL COMMENT '熊掌号ID' ;
				ALTER TABLE {$table_name} ADD `level` tinyint(4) unsigned NOT NULL COMMENT '0-个人 1-媒体 2-企业 3-政府 4-其他组织' ;
				ALTER TABLE {$table_name} ADD `key` varchar(80) NOT NULL ;
				ALTER TABLE {$table_name} ADD `secret` varchar(80) NOT NULL ;
EOT;
			pdo_run($sql);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		