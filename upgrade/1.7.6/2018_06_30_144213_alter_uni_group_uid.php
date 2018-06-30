<?php

namespace We7\V176;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1530340933
 * @version 1.7.6
 */

class AlterUniGroupUid {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('uni_group', array('uid'))) {
			$table_name = tablename('uni_group');
			$sql = <<<EOT
				ALTER TABLE {$table_name} ADD uid INT DEFAULT 0 NOT NULL COMMENT '用户专属权限（用户id）' ;
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
		