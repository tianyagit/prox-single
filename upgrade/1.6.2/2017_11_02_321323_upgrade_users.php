<?php
namespace We7\V162;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1506147730
 * @version 1.6.2
 */


class UpgradeUsers {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('users', 'login_type')) {
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `login_type` TINYINT(3) NOT NULL DEFAULT 0 COMMENT '用户来源类型：0网站注册，1qq';");
		}
		if (!pdo_fieldexists('users', 'access_token')) {
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `access_token` varchar(50) NOT NULL DEFAULT 0 COMMENT '用户qq：access_token';");
		}
		if (!pdo_fieldexists('users', 'refresh_token')) {
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `refresh_token` varchar(50) NOT NULL DEFAULT 0 COMMENT '用户qq：refresh_token';");
		}
		if (!pdo_fieldexists('users', 'expires_time')) {
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `expires_time` int(10) NOT NULL DEFAULT 0 COMMENT 'qq的access_token过期时间';");
		}
		if (!pdo_fieldexists('users', 'openid')) {
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `openid` varchar(50) NOT NULL DEFAULT 0 COMMENT 'qq的openid';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		