<?php

namespace We7\V180;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1534226346
 * @version 1.8.0
 */

class AlterAccountEndtime {

	/**
	 *  执行更新
	 */
	public function up() {
		if (pdo_fieldexists('account', 'endtime')) {
			pdo_query("ALTER TABLE " . tablename('account') . " MODIFY COLUMN `endtime` int(10) NOT NULL DEFAULT 0; ");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		