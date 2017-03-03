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

//自定义菜单为重名title添加后缀（如：title为‘默认菜单’的有两个重名，其id分别为1、2，执行该语句后title分别为：‘默认菜单_1’、‘默认菜单_2’）
//title不为空时
pdo_query("UPDATE ". tablename('uni_account_menus') . " SET `title` = concat(title,'_',id) WHERE  `title` IN (SELECT a.`title` FROM (SELECT `title` FROM " . tablename('uni_account_menus') . "WHERE title <> '' GROUP BY `title` having count(*) >1 ) a)");
//title为空时
pdo_query("UPDATE ". tablename('uni_account_menus') . " SET `title` = concat('默认菜单','_',id) WHERE  `title` IN (SELECT a.`title` FROM (SELECT `title` FROM " . tablename('uni_account_menus') . "WHERE title = '' GROUP BY `title` having count(*) >1 ) a)");