<?php

namespace We7\V179;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1532681797
 * @version 1.7.9
 */

class AlterMessageNoticeLog {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('message_notice_log', 'url')) {
			pdo_query("ALTER TABLE " . tablename('message_notice_log') . " ADD `url` VARCHAR(255) DEFAULT '' NOT NULL;");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		