<?php

namespace We7\V174;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1522140201
 * @version 1.7.4
 */

class AlterModulesRecycle {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_fieldexists('modules_recycle', 'type')) {
			pdo_query("ALTER TABLE " . tablename('modules_recycle') . " ADD `type` INT(11) NOT NULL DEFAULT 0 COMMENT '1 为未安装的应用 0 为已安装的应用';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		