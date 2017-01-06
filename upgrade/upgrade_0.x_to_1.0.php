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

$change_permission_list = array(
	array(
		'original' => array('platform_reply_basic', 'platform_reply_news', 'platform_reply_music', 'platform_reply_images', 'platform_reply_voice', 'platform_reply_video', 'platform_reply_wxcard', 'platform_reply_userapi'),
		'new' => array('platform_reply')
	),
	array(
		'original' => array('platform_special'),
		'new' => array('platform_reply_special'),
	),
	array(
		'original' => array('platform_qr'),
		'new' => array('platform_qr', 'platform_url2qr'),
	),
	array(
		'original' => array('material_mass'),
		'new' => array('platform_mass_task'),
	),
	array(
		'original' => array('material_display', 'material_manage'),
		'new' => array('platform_material'),
	),
	array(
		'original' => array('mc_members_manage'),
		'new' => array('mc_member_page'),
	),
);

$users_permission = pdo_getall('users_permission', array('type' => 'system'));
if (!empty($users_permission)) {
	foreach ($users_permission as $user_permission) {
		$user_original_permission = $users_permission['permission'];
		$permission = explode('|', $user_permission['permission']);
		if (empty($permission) || !is_array($permission)) {
			$permission = array();
		}
		foreach ($change_permission_list as $change_permission) {
			//判断用户权限里是否有要改变的权限
			if (array_intersect($permission, $change_permission['original'])) {
				//去掉废弃的权限
				$permission = array_diff($permission, $change_permission['original']);
				//添加新的权限
				foreach ($change_permission['new'] as $new_permission) {
					$permission[] = $new_permission;
				}
			}
		}
		$permission = implode('|', $permission);
		if ($permission != $user_original_permission) {
			pdo_update('users_permission', array('permission' => $permission), array('id' => $user_permission['id']));
		}
	}
}

//添加图文素材的素材顺序字段
if (!pdo_fieldexists('wechat_news', 'displayorder')) {
	pdo_query('ALTER TABLE '. tablename('wechat_news')." ADD `displayorder` INT(2) NOT NULL DEFAULT '0';");
}

//转移模块快捷菜单数据到uni_account_modules表，废弃之前存在uni_settings中的shortcuts
//uni_settings表中存放于字段中，不能存放太多数据，也不方便修改
if (!pdo_fieldexists('uni_account_modules', 'shortcut')) {
	pdo_query("ALTER TABLE ".tablename('uni_account_modules')." ADD `shortcut` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';");
}
if (!pdo_fieldexists('uni_account_modules', 'displayorder')) {
	pdo_query("ALTER TABLE ".tablename('uni_account_modules')." ADD `displayorder` INT UNSIGNED NOT NULL DEFAULT '0';");
}
if (pdo_fieldexists('uni_settings', 'shortcuts')) {
	//pdo_query("ALTER TABLE ".tablename('uni_settings')." DROP `shortcuts`;");
}
//修改用户avater字段长度
if(pdo_fieldexists('users_profile', 'avatar')) {
	pdo_query("ALTER TABLE ".tablename('users_profile')." CHANGE `avatar` `avatar` VARCHAR(255) NOT NULL DEFAULT '';");
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
	pdo_query("ALTER TABLE ". tablename('modules') ." ADD `title_initial` VARCHAR(1) NOT NULL DEFAULT '';");
}
$modules = pdo_getall('modules', array(), array('name', 'mid', 'title'));
if (!empty($modules)) {
	foreach ($modules as $module) {
		$title = $pinyin->get_first_char($module['title']);
		pdo_update('modules', array('title_initial' => $title), array('mid' => $module['mid']));
	}
}

//uni_account是否存在letter字段，否则添加并更新（切换公众号拼音索引功能）
if(!pdo_fieldexists('uni_account', 'letter')) {
	$add_letter = pdo_query("ALTER TABLE ". tablename('uni_account') . " ADD `letter` VARCHAR(1) NOT NULL COMMENT 'title首字母' , ADD FULLTEXT (`letter`);");
	if($add_letter) {
		$sql = '';
		$all_account = pdo_fetchall("SELECT uniacid,name FROM ". tablename('uni_account'));
		foreach ($all_account as $all_value) {
			$letter = '';
			$letter = $pinyin->get_first_char($all_value['name']);
			$sql .= "UPDATE ". tablename('uni_account'). " SET `letter` = '". $letter . "' WHERE `uniacid` = {$all_value['uniacid']};";
		}
		$run = pdo_run($sql);
	}
}

//切换公众号置顶功能
if(!pdo_fieldexists('uni_account', 'rank')) {
	pdo_query("ALTER TABLE ". tablename('uni_account') ." CHANGE `rank` `rank` INT(10) NULL DEFAULT '0';");
}

if (!pdo_fieldexists('core_menu', 'group_name')) {
	pdo_query("ALTER TABLE ". tablename('core_menu'). " ADD `group_name` VARCHAR(30) NOT NULL DEFAULT '';");
}

if (!pdo_fieldexists('core_menu', 'icon')) {
	pdo_query("ALTER TABLE ". tablename('core_menu'). " ADD `icon` VARCHAR(10) NOT NULL DEFAULT '';");
}
//增大缓存表字段长度
pdo_query("ALTER TABLE ". tablename('core_cache'). " CHANGE `value` `value` LONGTEXT NOT NULL;");

//自动回复功能调整
if(!pdo_fieldexists('rule', 'containtype')) {
	pdo_query("ALTER TABLE ". tablename('rule') ." ADD `containtype` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '包含的回复类型（如文字、图文、语音等）';");
}
if(!pdo_fieldexists('rule', 'reply_type')) {
	pdo_query("ALTER TABLE ". tablename('rule') ." ADD `reply_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '匹配关键字类型：1、混合；2、单一';");
}

//登陆后默认进入上一次管理/操作的公众号
if(!pdo_fieldexists('users', 'lastuniacid')) {
	pdo_query("ALTER TABLE ". tablename('users') ." ADD `lastuniacid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后一次操作的公众号ID' AFTER `lastvisit`;");
}

//新增公众号按添加时间排序
if(!pdo_fieldexists('uni_account', 'createtime')) {
	pdo_query("ALTER TABLE ". tablename('uni_account') ." ADD `createtime` INT(10) NOT NULL COMMENT '添加时间';");
}
if(!pdo_fieldexists('uni_account_users', 'createtime')) {
	pdo_query("ALTER TABLE ". tablename('uni_account_users') ." ADD `createtime` INT(10) NOT NULL COMMENT '添加时间';");
}
if(!pdo_fieldexists('account_wechats', 'createtime')) {
	pdo_query("ALTER TABLE ". tablename('account_wechats') ." ADD `createtime` INT(10) NOT NULL COMMENT '添加时间' ;");
}