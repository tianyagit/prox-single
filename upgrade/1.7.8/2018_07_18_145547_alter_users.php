<?php

namespace We7\V178;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1531896947
 * @version 1.7.8
 */

class AlterUsers {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_fieldexists('users', 'got_ads')) {
			pdo_query("ALTER TABLE " . tablename('users') . " ADD `got_ads` VARCHAR(50) DEFAULT '' NOT NULL COMMENT '关闭的推送广告';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		