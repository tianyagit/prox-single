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
$check_uniam = pdo_fetchall("SELECT `id`, `title`, `type` FROM " . tablename('uni_account_menus') . "WHERE `title` IN (SELECT `title` FROM " . tablename('uni_account_menus') . "GROUP BY `title` having count(*) >1 )" );

if(!empty($check_uniam)){
	foreach ($check_uniam as $check_bval) {
		if (strexists($check_bval['title'], '默认菜单') || strexists($check_bval['title'], '个性化菜单') || strexists($check_bval['title'], '标题') || empty($check_bval['title'])) {
			if ($check_bval['type'] == '1') {
				$intitle = '默认菜单_' . $check_bval['id'];
			} else {
				$intitle = '标题_' . $check_bval['id'];
			}
		} else {
			$intitle = $check_bval['title'] . '_' . $check_bval['id'];
		}
		pdo_update('uni_account_menus', array('title' => $intitle), array('id' => $check_bval['id']));
	}
}