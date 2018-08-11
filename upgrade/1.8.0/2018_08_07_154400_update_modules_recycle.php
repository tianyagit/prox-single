<?php

namespace We7\V180;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1533627840
 * @version 1.8.0
 */

class UpdateModulesRecycle {

	/**
	 *  执行更新
	 */
	public function up() {
		if (pdo_fieldexists('modules_recycle', 'modulename')) {
			pdo_query("DELETE FROM " . tablename('modules_recycle') . " WHERE `modulename` <> '' AND (`name` = '' OR `name` IS NULL);");
		}
		pdo_query(
			"DELETE r FROM " . tablename('modules_recycle') .
			" r LEFT JOIN " . tablename('modules') .
			" m ON r.name = m.name WHERE r.type = 1 AND m.mid IS NULL;"
		);
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		