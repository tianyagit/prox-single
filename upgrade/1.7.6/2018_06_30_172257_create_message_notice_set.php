<?php

namespace We7\V176;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1530350577
 * @version 1.7.6
 */

class CreateMessageNoticeSet {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_tableexists('message_notice_set')) {
			$table_name = tablename('message_notice_set');
			$sql = <<<EOF
CREATE TABLE IF NOT EXISTS $table_name (
`id` int(11) NOT NULL AUTO_INCREMENT,
`property` varchar(24) NOT NULL COMMENT '类型',
`type` int(4) NOT NULL COMMENT '设置类型',
`status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态1开2关',
`time` int(11) NOT NULL COMMENT '添加时间',
PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8
EOF;
			pdo_query($sql);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		