<?php

namespace We7\V164;

defined('IN_IA') or exit('Access Denied');

class CreateMessageNotice {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_tableexists('message_notice_log')) {
			$sql = "CREATE TABLE IF NOT EXISTS " . tablename('message_notice_log') . " (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `message` varchar(255) NOT NULL DEFAULT '' COMMENT '消息内容',
				  `is_read` char(1) NOT NULL DEFAULT '1' COMMENT '1未读，2已读',
				  `sign` varchar(22) NOT NULL DEFAULT '' COMMENT '订单id,到期uid,工单的id,注册uid',
				  `type` tinyint(3) NOT NULL DEFAULT 0 COMMENT '1订单，2到期，3工单，4注册',
				  `status` tinyint(3) DEFAULT '0' COMMENT '是否需要审核 1、注册审核；2、正常',
				  `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '提交时间',
				  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			pdo_run($sql);
		}

		if (!pdo_fieldexists('users', 'expire_is_record')) {
			pdo_query('ALTER TABLE ' . tablename('users') . " ADD `expire_is_record` tinyint(3) NOT NULL DEFAULT 0 COMMENT '是否记录到期用户，0没有，1记录';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
	}
}
		