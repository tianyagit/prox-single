<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 构建管理员或操作员权限数据
 * @return boolean
 */
function permission_build() {
	global $_W;
	$we7_file_permission = require IA_ROOT . '/web/common/permission.inc.php';
	$permission_frames = require IA_ROOT . '/web/common/frames.inc.php';
	if (!in_array($_W['role'], array(ACCOUNT_MANAGE_NAME_OPERATOR, ACCOUNT_MANAGE_NAME_MANAGER)) || empty($_W['uniacid'])) {
		return $we7_file_permission;
	}

	$cachekey = cache_system_key("permission:{$_W['uniacid']}:{$_W['uid']}");
	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		return $cache;
	}
	$permission_exist = uni_user_permission_exist($_W['uid'], $_W['uniacid']);
	if (empty($permission_exist)) {
		$we7_file_permission['platform'][$_W['role']] = array('platform*');
		$we7_file_permission['site'][$_W['role']] = array('site*');
		$we7_file_permission['mc'][$_W['role']] = array('mc*');
		$we7_file_permission['profile'][$_W['role']] = array('profile*');
		$we7_file_permission['module'][$_W['role']] = array('manage-account', 'display');
		$we7_file_permission['wxapp'][$_W['role']] = array('display', 'payment', 'post', 'version');
		cache_write($cachekey, $we7_file_permission);
		return $we7_file_permission;
	}
	$user_account_permission = uni_user_menu_permission($_W['uid'], $_W['uniacid'], PERMISSION_ACCOUNT);
	$user_wxapp_permission = uni_user_menu_permission($_W['uid'], $_W['uniacid'], PERMISSION_WXAPP);
	$user_permission = array_merge($user_account_permission, $user_wxapp_permission);

	$permission_contain = array('account', 'wxapp', 'system');
	$section = array();
	$permission_result = array();
	foreach ($permission_frames as $key => $frames) {
		if (!in_array($key, $permission_contain) || empty($frames['section'])) {
			continue;
		}
		foreach ($frames['section'] as $frame_key => $frame) {
			if (empty($frame['menu'])) {
				continue;
			}
			$section[$key][$frame_key] = $frame['menu'];
		}
	}
	$account = permission_get_nameandurl($section[$permission_contain[0]]);
	$wxapp = permission_get_nameandurl($section[$permission_contain[1]]);
	$system = permission_get_nameandurl($section[$permission_contain[2]]);
	$permission_result = array_merge($account, $wxapp, $system);

	foreach ($permission_result as $permission_val) {
		if (in_array($permission_val['permission_name'], $user_permission)) {
			$we7_file_permission[$permission_val['controller']][$_W['role']][] = $permission_val['action'];
		}
	}
	cache_write($cachekey, $we7_file_permission);
	return $we7_file_permission;
}

/**
 * @return array()
 */
function permission_get_nameandurl($permission) {
	$result = array();
	if (empty($permission)) {
		return $result;
	}
	foreach ($permission as $menu) {
		if (empty($menu)) {
			continue;
		}
		foreach ($menu as $permission_name) {
			$url_query_array = url_params($permission_name['url']);
			$result[] = array(
				'url' => $permission_name['url'],
				'controller' => $url_query_array['c'],
				'action' => $url_query_array['a'],
				'permission_name' => $permission_name['permission_name']
			);
		}
	}
	return $result;
}

/*
 * 添加公众号时执行数量判断权限
 * @param int $uid 操作用户
 * @param int $type 公众号类型 (1. 主公众号; 2. 子公众号; 4. 小程序)
 * @return array|boolean 错误原因或成功
 */
function permission_uni_create($uid, $type = ACCOUNT_TYPE_OFFCIAL_NORMAL) {
	$uid = intval($uid);
	if (empty($uid)) {
		return error(-1, '用户数据错误！');
	}
	$groupid = pdo_fetchcolumn('SELECT groupid FROM ' . tablename('users') . ' WHERE uid = :uid', array(':uid' => $uid));
	$groupdata = pdo_fetch('SELECT maxaccount, maxsubaccount, maxwxapp FROM ' . tablename('users_group') . ' WHERE id = :id', array(':id' => $groupid));
	$list = pdo_fetchall('SELECT d.type, count(*) AS count FROM (SELECT u.uniacid, a.default_acid FROM ' . tablename('uni_account_users') . ' as u RIGHT JOIN '. tablename('uni_account').' as a  ON a.uniacid = u.uniacid  WHERE u.uid = :uid AND u.role = :role ) AS c LEFT JOIN '.tablename('account').' as d ON c.default_acid = d.acid WHERE d.isdeleted = 0 GROUP BY d.type', array(':uid' => $uid, ':role' => 'owner'));
	foreach ($list as $item) {
		if ($item['type'] == ACCOUNT_TYPE_APP_NORMAL) {
			$wxapp_num = $item['count'];
		} else {
			$account_num = $item['count'];
		}
	}
	//添加主公号
	if ($type == ACCOUNT_TYPE_OFFCIAL_NORMAL || $type == ACCOUNT_TYPE_OFFCIAL_AUTH) {
		if ($account_num >= $groupdata['maxaccount']) {
			return error('-1', '您所在的用户组最多只能创建' . $groupdata['maxaccount'] . '个主公众号');
		}
	} elseif ($type == ACCOUNT_TYPE_APP_NORMAL) {
		if ($wxapp_num >= $groupdata['maxwxapp']) {
			return error('-1', '您所在的用户组最多只能创建' . $groupdata['maxwxapp'] . '个小程序');
		}
	}
	return true;
}

/**
 * 获取指定操作用户在指定的公众号所具有的操作权限
 * @param int $uid 操作用户
 * @param int $uniacid 指定统一公众号
 * @return string 操作用户的 role (manager|operator)
 */
function permission_uni_role($uid = 0, $uniacid = 0) {
	global $_W;
	load()->model('user');
	$role = '';
	$uid = empty($uid) ? $_W['uid'] : intval($uid);

	if (user_is_founder($uid) && !user_is_vice_founder($uid)) {
		return ACCOUNT_MANAGE_NAME_FOUNDER;
	}

	if (user_is_vice_founder($uid)) {
		return ACCOUNT_MANAGE_NAME_VICE_FOUNDER;
	}

	if (!empty($uniacid)) {
		$role = pdo_getcolumn('uni_account_users', array('uid' => $uid, 'uniacid' => $uniacid), 'role');
		if ($role == ACCOUNT_MANAGE_NAME_OWNER) {
			$role = ACCOUNT_MANAGE_NAME_OWNER;
		} elseif ($role == ACCOUNT_MANAGE_NAME_VICE_FOUNDER) {
			$role = ACCOUNT_MANAGE_NAME_VICE_FOUNDER;
		} elseif ($role == ACCOUNT_MANAGE_NAME_MANAGER) {
			$role = ACCOUNT_MANAGE_NAME_MANAGER;
		} elseif ($role == ACCOUNT_MANAGE_NAME_OPERATOR) {
			$role = ACCOUNT_MANAGE_NAME_OPERATOR;
		} elseif ($role == ACCOUNT_MANAGE_NAME_CLERK) {
			$role = ACCOUNT_MANAGE_NAME_CLERK;
		}
	} else {
		$roles = pdo_getall('uni_account_users', array('uid' => $uid), array('role'), 'role');
		$roles = array_keys($roles);
		if (in_array(ACCOUNT_MANAGE_NAME_VICE_FOUNDER, $roles)) {
			$role = ACCOUNT_MANAGE_NAME_VICE_FOUNDER;
		} elseif (in_array(ACCOUNT_MANAGE_NAME_OWNER, $roles)) {
			$role = ACCOUNT_MANAGE_NAME_OWNER;
		} elseif (in_array(ACCOUNT_MANAGE_NAME_MANAGER, $roles)) {
			$role = ACCOUNT_MANAGE_NAME_MANAGER;
		} elseif (in_array(ACCOUNT_MANAGE_NAME_OPERATOR, $roles)) {
			$role = ACCOUNT_MANAGE_NAME_OPERATOR;
		} elseif (in_array(ACCOUNT_MANAGE_NAME_CLERK, $roles)) {
			$role = ACCOUNT_MANAGE_NAME_CLERK;
		}
	}
	$role = empty($role) ? ACCOUNT_MANAGE_NAME_OPERATOR : $role;
	return $role;
}