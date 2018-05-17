<?php

namespace We7\V172;

defined('IN_IA') or exit('Access Denied');

class AlterUnisetting {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_fieldexists('uni_settings', 'comment_status')){
			pdo_query('ALTER TABLE ' . tablename('uni_settings') . " ADD `comment_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否开启留言共功能';");
		}
		if(!pdo_fieldexists('uni_settings', 'reply_setting')){
			pdo_query('ALTER TABLE ' . tablename('uni_settings') . " ADD `reply_setting` TINYINT NOT NULL DEFAULT 0;");
		}
	}

	/**
	 *  回滚更新
	 */
	public function down() {


	}
}
