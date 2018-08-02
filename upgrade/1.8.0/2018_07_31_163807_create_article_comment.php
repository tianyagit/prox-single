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
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级回复id',
  `uid` int(10) unsigned NOT NULL COMMENT '后台用户uid',
  `content` text COMMENT '回复内容',
  `is_read` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1未读，2已读',
  `iscomment` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1未回复，2已回复',
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
		