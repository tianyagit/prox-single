<?php

namespace We7\V173;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1521508233
 * @version 1.7.3
 */

class CreateModulesLocal {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_tableexists('modules_local')){
			$table_name = tablename('modules_local');
			$sql = <<<EOT
CREATE TABLE IF NOT EXISTS $table_name (
`mid` int(11) NOT NULL,
`name` varchar(100) NOT NULL,
`title` varchar(100) NOT NULL,
`thumb` varchar(500) NOT NULL COMMENT '模块logo',
`main_module` varchar(100) NOT NULL COMMENT '主模块',
`version` varchar(15) NOT NULL,
`has_new_branch` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0为没有新分支，1为有',
`is_upgrade` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0为无升级，1为有升级',
`wxapp_support` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1为不支持，2为支持，其他support一样',
`app_support` tinyint(1) NOT NULL DEFAULT '1',
`welcome_support` tinyint(1) NOT NULL DEFAULT '1',
`webapp_support` tinyint(1) NOT NULL DEFAULT '1',
`phoneapp_support` tinyint(1) NOT NULL DEFAULT '1',
`status` varchar(50) NOT NULL,
`upgrade_support` int(11) NOT NULL DEFAULT '1',
`upgrade_branch` int(11) NOT NULL COMMENT '是否可升級应用',
`from` varchar(50) NOT NULL COMMENT '数据来源cloud'
) DEFAULT CHARSET=utf8;
EOT;

			pdo_query($sql);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		