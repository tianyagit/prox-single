<?php

namespace We7\V178;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1532006008
 * @version 1.7.8
 */

class AlterWxappVersions {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('wxapp_versions', 'last_modules')) {
			pdo_query("ALTER TABLE " . tablename('wxapp_versions') . " ADD `last_modules` VARCHAR(1000) DEFAULT '' NOT NULL COMMENT '最后上传的应用版本';");
			pdo_query("UPDATE " . tablename('wxapp_versions') . " SET `last_modules` = `modules`;");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		