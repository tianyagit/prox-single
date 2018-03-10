<?php

namespace We7\V172;

defined('IN_IA') or exit('Access Denied');

class AlterUsers {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_fieldexists('users', 'welcome_status')){
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `welcome_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否开启首页';");
		}
		if(!pdo_fieldexists('users', 'welcome_link')){
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `welcome_link` varchar(100) NOT NULL DEFAULT '' COMMENT '首页链接';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		