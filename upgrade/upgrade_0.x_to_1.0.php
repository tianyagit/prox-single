<?php
/**
 * 升级微擎1.0脚本
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

define('IN_SYS', true);
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
require IA_ROOT . '/web/common/common.func.php';
require IA_ROOT . '/framework/library/pinyin/pinyin.php';
$pinyin = new Pinyin_Pinyin();


//转移模块快捷菜单数据到uni_account_modules表，废弃之前存在uni_settings中的shortcuts
//uni_settings表中存放于字段中，不能存放太多数据，也不方便修改
if (!pdo_fieldexists('uni_account_modules', 'shortcut')) {
	pdo_query("ALTER TABLE ".tablename('uni_account_modules')." ADD `display` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('uni_account_modules', 'displayorder')) {
	pdo_query("ALTER TABLE ".tablename('uni_account_modules')." ADD `displayorder` INT UNSIGNED NOT NULL DEFAULT '0';");
}
if (pdo_fieldexists('uni_settings', 'shortcuts')) {
	//pdo_query("ALTER TABLE ".tablename('uni_settings')." DROP `shortcuts`;");
}
//修改用户avater字段长度
if(pdo_fieldexists('users_profile', 'avatar')) {
	pdo_query("ALTER TABLE ".tablename('users_profile')." CHANGE `avatar` `avatar` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';");
}
$shortcuts = pdo_getall('uni_settings', array(), array('shortcuts', 'uniacid'));
if (!empty($shortcuts)) {
	foreach ($shortcuts as $row) {
		if (!empty($row['shortcuts'])) {
			$row['shortcuts'] = iunserializer($row['shortcuts']);
			if (!empty($row['shortcuts'])) {
				foreach ($row['shortcuts'] as $module) {
					$module_profile = pdo_get('uni_account_modules', array('module' => $module['name'], 'uniacid' => $row['uniacid']));
					if (!empty($module_profile)) {
						pdo_update('uni_account_modules', array('shortcut' => '1'), array('id' => $module_profile['id']));
					} else {
						$data = array(
							'uniacid' => $row['uniacid'],
							'module' => $module['name'],
							'enabled' => 1,
							'shortcut' => 1,
							'settings' => '',
						);
						pdo_insert('uni_account_modules', $data);
					}
				}
			}
		}
	}
}

//增加模块和公众号拼音索引
if (!pdo_fieldexists('modules', 'title_initial')) {
	pdo_query("ALTER TABLE `ims_modules` ADD `title_initial` VARCHAR(1) NOT NULL DEFAULT '';");
}
$modules = pdo_getall('modules', array(), array('name', 'mid', 'title'));
if (!empty($modules)) {
	foreach ($modules as $module) {
		$title = $pinyin->get_first_char($module['title']);
		pdo_update('modules', array('title_initial' => $title), array('mid' => $module['mid']));
	}
}