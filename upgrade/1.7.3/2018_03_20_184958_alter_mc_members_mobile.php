<?php

namespace We7\V173;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1521542998
 * @version 1.7.3
 */

class AlterMcMembersMobile {

	/**
	 *  执行更新
	 */
	public function up() {
		if (pdo_fieldexists('mc_members','mobile')) {
			pdo_query("ALTER TABLE " . tablename('mc_members') . " MODIFY COLUMN  `mobile` VARCHAR(18) NOT NULL;");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		