<?php
/**
 * 升级微擎1.0脚本
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/common.func.php';

if(!pdo_fieldexists('news_reply', 'media_id')) {
	pdo_query("ALTER TABLE ".tablename('news_reply')." ADD `media_id` int(10) NOT NULL DEFAULT '0';");
}

//查询自定义菜单名称是否有存在
pdo_query("UPDATE ". tablename('uni_account_menus') . " SET title = if(title = '',if(type=1, concat('默然菜单_',id),concat('个性化菜单_',id)),concat(title,'_',id)) WHERE  `title` IN (SELECT a.title FROM (SELECT `title` FROM " . tablename('uni_account_menus') . " GROUP BY `title` having count(*) >1 ) a)");