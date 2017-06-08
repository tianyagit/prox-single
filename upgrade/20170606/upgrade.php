<?php
/**
 * 小程序版本表添加字段last_use：是否上一次使用版本
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../../framework/bootstrap.inc.php';

//更新小程序相关表结构
if(!pdo_fieldexists('wxapp_versions', 'last_use')) {
	pdo_query("ALTER TABLE ". tablename('wxapp_versions') ." ADD `last_use` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '上一次使用：1、是；0、否';");
}
if (pdo_fieldexists('uni_account', 'title_initial')) {
	$accounts = pdo_getall('uni_account', array(), array('name', 'uniacid', 'default_acid', 'title_initial'));
	if (!empty($accounts)) {
		foreach ($accounts as $account) {
			if (empty($account['title_initial'])) {
				$first_char = get_first_char($account['name']);
				pdo_update('uni_account', array('title_initial' => $first_char), array('uniacid' => $account['uniacid'], 'default_acid' => $account['default_acid']));
			}
		}
	}
}