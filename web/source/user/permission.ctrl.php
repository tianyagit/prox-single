<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

$_W['page']['title'] = '查看用户权限 - 用户管理';

load()->model('setting');
load()->model('module');

$do = $_GPC['do'];
$dos = array('deny', 'module');
$do = in_array($do, $dos) ? $do: 'deny';

$uid = intval($_GPC['uid']);
$user = user_single($uid);
if (empty($user)) {
	message('访问错误, 未找到指定操作用户.');
}

$founders = explode(',', $_W['config']['setting']['founder']);
$isfounder = in_array($user['uid'], $founders);
if ($isfounder) {
	message('访问错误, 无法编辑站长.');
}

//禁止/开启用户
if ($do == 'deny') {
	if ($_W['ispost'] && $_W['isajax']) {
		$founders = explode(',', $_W['config']['setting']['founder']);
		if (in_array($uid, $founders)) {
			exit('管理员用户不能禁用.');
		}
		$somebody = array();
		$somebody['uid'] = $uid;
		
		if (intval($user['status']) == 2) {
			$somebody['status'] = 1;
		} else {
			$somebody['status'] = 2;
		}
		if (user_update($somebody)) {
			exit('success');
		}
	}
}

if($do == 'module') {
	if($_W['isajax']) {
		$m = trim($_GPC['m']);
		$uniacid = intval($_GPC['uniacid']);
		$uid = intval($_GPC['uid']);
		$module = pdo_fetch('SELECT * FROM ' . tablename('modules') . ' WHERE name = :m', array(':m' => $m));
		//获取模块权限
		$purview = pdo_fetch('SELECT * FROM ' . tablename('users_permission') . ' WHERE uniacid = :aid AND uid = :uid AND type = :type', array(':aid' => $uniacid, ':uid' => $uid, ':type' => $m));
		if(!empty($purview['permission'])) {
			$purview['permission'] = explode('|', $purview['permission']);
		} else {
			$purview['permission'] = array();
		}

		$mineurl = array();
		$all = 0;
		if(!empty($mods)) {
			foreach($mods as $mod) {
				if($mod['url'] == 'all') {
					$all = 1;
					break;
				} else {
					$mineurl[] = $mod['url'];
				}
			}
		}
		$data = array();
		if($module['settings']) {
			$data[] = array('title' => '参数设置', 'permission' => $m.'_settings');
		}
		if($module['isrulefields']) {
			$data[] = array('title' => '回复规则列表', 'permission' => $m.'_rule');
		}
		$entries = module_entries($m);
		if(!empty($entries['home'])) {
			$data[] = array('title' => '微站首页导航', 'permission' => $m.'_home');
		}
		if(!empty($entries['profile'])) {
			$data[] = array('title' => '个人中心导航', 'permission' => $m.'_profile');
		}
		if(!empty($entries['shortcut'])) {
			$data[] = array('title' => '快捷菜单', 'permission' => $m.'_shortcut');
		}
		if(!empty($entries['cover'])) {
			foreach($entries['cover'] as $cover) {
				$data[] = array('title' => $cover['title'], 'permission' => $m.'_cover_'.$cover['do']);
			}
		}
		if(!empty($entries['menu'])) {
			foreach($entries['menu'] as $menu) {
				$data[] = array('title' => $menu['title'], 'permission' => $m.'_menu_'.$menu['do']);
			}
		}
		unset($entries);
		if(!empty($module['permissions'])) {
			$module['permissions'] = (array)iunserializer($module['permissions']);
			$data = array_merge($data, $module['permissions']);
		}
		foreach($data as &$da) {
			$da['checked'] = 0;
			if(in_array($da['permission'], $purview['permission']) || in_array('all', $purview['permission'])) {
				$da['checked'] = 1;
			}
		}
		unset($da);
		$out['errno'] = 0;
		$out['errmsg'] = '';
		if(empty($data)) {
			$out['errno'] = 1;
		} else {
			$out['errmsg'] = $data;
		}
		exit(json_encode($out));
	}
}