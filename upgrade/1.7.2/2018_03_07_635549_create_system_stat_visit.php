<?php

namespace We7\V172;

defined('IN_IA') or exit('Access Denied');

class CreateSystemStatVisit {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_tableexists('system_stat_visit')){
			$tablename = tablename('system_stat_visit');
			$sql = <<<EOT
				CREATE TABLE $tablename (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL COMMENT 'uniacid',
  `modulename` varchar(100) NOT NULL COMMENT '模块标识',
  `uid` int(10) unsigned NOT NULL COMMENT '后台用户uid',
  `displayorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `createtime` int(10) NOT NULL,
  `updatetime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniacid` (`uniacid`),
  KEY `uid` (`uid`)
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
		