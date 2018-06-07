<?php

namespace We7\V175;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1528362050
 * @version 1.7.5
 */

class AlterUsersFounderGroupMaxxiongzhangapp {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_exists('users_founder_group', 'maxxiongzhangapp')) {
			$table_name = tablename('users_founder_group');
			$sql = "ALTER TABLE {$table_name} ADD maxxiongzhangapp int(10) DEFAULT 0 NOT NULL COMMENT '熊掌号最大创建数量'";
			pdo_run($sql);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		