<?php

namespace We7\V177;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1531057896
 * @version 1.7.7
 */

class UpdateUniSetting {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_exists('uni_settings', 'default_module')) {
			pdo_run("ALTER TABLE " . tablename('uni_settings') . " ADD default_module varchar(100) NOT NULL COMMENT 'PC默认进入模块名'");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		