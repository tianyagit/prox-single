<?php

namespace We7\V174;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1526003728
 * @version 1.7.4
 */

class AlterUsers {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('users', 'is_bind')) {
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `is_bind` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'QQ和微信登录后是否绑定';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		