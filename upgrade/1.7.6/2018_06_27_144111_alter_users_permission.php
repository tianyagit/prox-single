<?php

namespace We7\V176;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1530081671
 * @version 1.7.6
 */

class AlterUsersPermission {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('users_permission', array('modules', 'templates'))) {
			$table_name = tablename('users_permission');
			$sql = <<<EOT
				ALTER TABLE {$table_name} ADD modules TEXT NOT NULL COMMENT '应用权限' ;
				ALTER TABLE {$table_name} ADD templates TEXT NOT NULL COMMENT '模板权限' ;
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
		