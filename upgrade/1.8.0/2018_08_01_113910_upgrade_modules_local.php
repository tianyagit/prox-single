<?php

namespace We7\V180;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1533094750
 * @version 1.8.0
 */

class UpgradeModulesLocal {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('modules_local', 'aliapp_support')) {
			pdo_query("ALTER TABLE " . tablename('modules_local') . " ADD `aliapp_support` tinyint(1) DEFAULT 1 NOT NULL;");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		