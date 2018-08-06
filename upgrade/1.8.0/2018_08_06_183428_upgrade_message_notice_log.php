<?php

namespace We7\V180;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1533551668
 * @version 1.8.0
 */

class UpgradeMessageNoticeLog {

	/**
	 *  执行更新
	 */
	public function up() {
		pdo_query("DELETE FROM " . tablename('message_notice_log') . " WHERE `type` IN (10,11) ORDER BY id DESC LIMIT 40;");
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		