<?php

namespace We7\V180;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1533026287
 * @version 1.8.0
 */

class CreateArticleComment {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_tableexists('article_comment')) {
			$table_name = tablename('article_comment');
			$sql = <<<EOT
CREATE TABLE $table_name (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `articleid` int(10) unsigned NOT NULL COMMENT '公告id',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL,
  `content` varchar(500),
  `is_like` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否点赞记录：1是，2否',
  `is_reply` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1已回复，2未回复',
  `like_num` int(10) unsigned NOT NULL DEFAULT '0',
  `createtime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleid` (`articleid`)
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
		