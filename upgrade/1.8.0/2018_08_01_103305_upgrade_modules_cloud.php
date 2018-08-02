<?php

namespace We7\V180;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1533090785
 * @version 1.8.0
 */

class UpgradeModulesCloud {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('modules_cloud', 'aliapp_support')) {
			pdo_query("ALTER TABLE " . tablename('modules_cloud') . " ADD `aliapp_support` tinyint(1) DEFAULT 1 NOT NULL;");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		