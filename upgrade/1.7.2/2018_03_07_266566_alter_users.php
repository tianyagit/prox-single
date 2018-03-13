<?php

namespace We7\V172;

defined('IN_IA') or exit('Access Denied');

class AlterUsers {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_fieldexists('users', 'welcome_link')){
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `welcome_link` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '登录后跳转的登陆页';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		