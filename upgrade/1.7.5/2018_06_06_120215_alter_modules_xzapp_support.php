<?php

namespace We7\V175;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1528257735
 * @version 1.7.5
 */

class AlterModulesXzappSupport {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('modules', 'xzapp_support')) {
			$table_name = tablename('modules');
			$sql = "ALTER TABLE {$table_name} ADD xzapp_support tinyint(1) DEFAULT 1 NOT NULL COMMENT '是否支持熊掌号应用 1 不支持 2 支持' ;";
			pdo_run($sql);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		