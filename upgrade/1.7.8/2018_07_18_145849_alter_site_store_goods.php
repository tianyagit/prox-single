<?php

namespace We7\V178;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1531897129
 * @version 1.7.8
 */

class AlterSiteStoreGoods {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_fieldexists('site_store_goods', 'user_group_price')) {
			pdo_query("ALTER TABLE " . tablename('site_store_goods') . " ADD `user_group_price` TEXT COMMENT '用户组价格' AFTER price;");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		