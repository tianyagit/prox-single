<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: malina
 * Date: 2017/9/7
 * Time: 10:26.
 */
namespace We7\V155;

defined('IN_IA') or exit('Access Denied');
class UpdateUsers {
	public function up() {
		if (pdo_fieldexists('users', 'type')) {
			pdo_query("ALTER TABLE ".tablename('users')." CHANGE `type` `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT '用户身份.1.普通用户3代表是店员';");
			pdo_query("UPDATE ".tablename('users')." SET `type` = 1 WHERE `type` = 0;");
		}
	}
}
