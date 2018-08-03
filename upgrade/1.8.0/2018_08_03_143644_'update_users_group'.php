<?php

namespace We7\V180;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1533278204
 * @version 1.8.0
 */

class 'updateUsersGroup' {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('users_group', 'maxaliapp')) {
			pdo_query("ALTER TABLE " . tablename('users_group') . " ADD `maxaliapp` int(10) DEFAULT 0 NOT NULL;");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		