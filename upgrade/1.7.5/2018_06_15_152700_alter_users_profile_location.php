<?php

namespace We7\V175;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1529047620
 * @version 1.7.5
 */

class AlterUsersProfileLocation {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('users_profile', array('location'))) {
			$table_name = tablename('users_profile');
			$sql = <<<EOT
			ALTER TABLE {$table_name} ADD location varchar(100) NOT NULL ;
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
		