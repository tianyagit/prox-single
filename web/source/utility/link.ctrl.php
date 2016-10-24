<?php
/**
 * 链接选择器
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$callback = $_GPC['callback'];
load()->model('module');
load()->model('site');

$dos = array('entry');
$do = in_array($do, $dos) ? $do : 'entry';

$_W['page']['title'] = '';
$callback = $_GPC['callback'];

if ($do == 'entry') {
	$has_permission = array();
	if(uni_user_permission_exist()) {
		$has_permission = array(
			'system' => array(),
			'modules' => array()
		);
		$has_permission['system'] = uni_user_permission('system');
		//获取用户的模块权限
		$temp_module = pdo_fetchall('SELECT * FROM ' . tablename('users_permission') . ' WHERE uniacid = :uniacid AND uid = :uid AND type != :type', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['uid'], ':type' => 'system'), 'type');
		if(!empty($temp_module)) {
			$has_permission['modules'] = array_keys($temp_module);
			foreach($temp_module as $row) {
				if($row['permission'] == 'all') {
					$has_permission[$row['type']] = array('all');
				} else {
					$has_permission[$row['type']] = explode('|', $row['permission']);
				}
			}
		}
	}

	$modulemenus = array();
	$modules = uni_modules_app_binding();
	foreach($modules as $module) {
		$m = $module['name'];
		if(empty($has_permission) || (!empty($has_permission) && in_array($m, $has_permission['modules']))) {
			$entries = $module['entries'];
			if(!empty($has_permission[$m]) && $has_permission[$m][0] != 'all') {
				if(!in_array($m.'_home', $has_permission[$m])) {
					unset($entries['home']);
				}
				if(!in_array($m.'_profile', $has_permission[$m])) {
					unset($entries['profile']);
				}
				if(!in_array($m.'_shortcut', $has_permission[$m])) {
					unset($entries['shortcut']);
				}
				if(!empty($entries['cover'])) {
					foreach($entries['cover'] as $k => $row) {
						if(!in_array($m.'_cover_'.$row['do'], $has_permission[$m])) {
							unset($entries['cover'][$k]);
						}
					}
				}
			}

			$module['cover'] = $entries['cover'];
			$module['home'] = $entries['home'];
			$module['profile'] = $entries['profile'];
			$module['shortcut'] = $entries['shortcut'];
			$module['function'] = $entries['function'];
			$modulemenus[$module['type']][$module['name']] = $module;
		}
	}
	$modtypes = module_types();

	$sysmenus = array(
		array('title'=>'微站首页','url'=> murl('home')),
		array('title'=>'个人中心','url'=> murl('mc')),
	);

//会员卡链接
	if(empty($has_permission) || (!empty($has_permission) && in_array('mc_card', $has_permission['system']))) {
		$cardmenus = array(
			array('title'=>'我的会员卡','url'=> murl('mc/card/mycard')),
			array('title'=>'消息','url'=> murl('mc/card/notice')),
			array('title'=>'签到','url'=> murl('mc/card/sign_display')),
			array('title'=>'推荐','url'=> murl('mc/card/recommend')),
			array('title'=>'适用门店','url'=> murl('mc/store')),
			array('title'=>'完善会员资料','url'=> murl('mc/profile')),
		);
	}
//多微站链接处理
	if(empty($has_permission) || (!empty($has_permission) && in_array('site_multi_display', $has_permission['system']))) {
		$multi_list = pdo_getall('site_multi', array('uniacid' => $_W['uniacid'], 'status !=' => 0), array('id', 'title'));
		if(!empty($multi_list)) {
			foreach($multi_list as $multi) {
				$multimenus[] = array('title' => $multi['title'], 'url' => murl('home', array('t' => $multi['id'])));
			}
		}
	}
	$linktypes = array(
		'cover' => '封面链接',
		'home' => '微站首页导航',
		'profile'=>'微站个人中心导航',
		'shortcut' => '微站快捷功能导航',
		'function' => '微站独立功能',
	);
}
template('utility/link');