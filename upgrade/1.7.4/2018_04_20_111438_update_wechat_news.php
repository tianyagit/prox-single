<?php

namespace We7\V174;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1524194078
 * @version 1.7.4
 */

class UpdateWechatNews {

	/**
	 *  执行更新
	 */
	public function up() {
		if (pdo_fieldexists('wechat_news', 'content')) {
			pdo_run("ALTER TABLE  `ims_wechat_news` CHANGE  `content`  `content` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
		}
	}

	/**
	 *  回滚更新
	 */
	public function down() {


	}
}
