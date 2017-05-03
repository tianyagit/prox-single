<?php
/**
 * 设置模块启用停用，并显示模块到快捷菜单中
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('account');
load()->model('user');

$dos = array('display', 'setting', 'shortcut', 'enable', 'permissions');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

$modulelist = uni_modules(false);

if($do == 'display') {
	$_W['page']['title'] = '公众号 - 应用模块 - 更多应用';
	
	$pageindex = max(1, intval($_GPC['page']));
	$pagesize = 30;

	if (!empty($modulelist)) {
		foreach ($modulelist as $name => &$row) {
			if ($name == 'we7_coupon') {
				$row['issystem'] = 0;
			}
			if (!empty($row['issystem']) || !empty($row['main_module']) || (!empty($_GPC['keyword']) && !strexists ($row['title'], $_GPC['keyword'])) || (!empty($_GPC['letter']) && $row['title_initial'] != $_GPC['letter'])) {
				unset($modulelist[$name]);
				continue;
			}
		}
		$modules = array();
		if (!empty($modulelist)) {
			$module_profile = pdo_getall('uni_account_modules', array('module' => array_keys($modulelist), 'uniacid' => $_W['uniacid']), array ('module', 'enabled', 'shortcut'), 'module', array('displayorder DESC'));
			if (!empty($module_profile)) {
				foreach ($module_profile as $name => $row) {
					$modules[$name] = $modulelist[$name];
					$modules[$name]['enabled'] = $row['enabled'];
					$modules[$name]['shortcut'] = $row['shortcut'];
				}
			}
		}
		$total = count($modules);
		$modules = array_slice($modules, ($pageindex - 1) * $pagesize, $pagesize);
		$pager = pagination ($total, $pageindex, $pagesize);
	}
	template ('profile/module');
} elseif ($do == 'shortcut') {
	$status = intval($_GPC['shortcut']);
	$modulename = $_GPC['modulename'];
	$module = module_fetch($modulename);
	if(empty($module)) {
		itoast('抱歉，你操作的模块不能被访问！', '', '');
	}
	
	$module_status = pdo_get('uni_account_modules', array('module' => $modulename, 'uniacid' => $_W['uniacid']), array('id', 'shortcut'));
	if (empty($module_status)) {
		$data = array(
			'uniacid' => $_W['uniacid'],
			'module' => $modulename,
			'enabled' => STATUS_ON,
			'shortcut' => $status ? STATUS_ON : STATUS_OFF,
			'settings' => '',
		);
		pdo_insert('uni_account_modules', $data);
	} else {
		$data = array(
			'shortcut' => $status ? STATUS_ON : STATUS_OFF,
		);
		pdo_update('uni_account_modules', $data, array('id' => $module_status['id']));
	}
	if ($status) {
		itoast('添加模块快捷操作成功！', referer(), 'success');
	} else {
		itoast('取消模块快捷操作成功！', referer(), 'success');
	}
} elseif ($do == 'enable') {
	$modulename = $_GPC['modulename'];
	if(empty($modulelist[$modulename])) {
		itoast('抱歉，你操作的模块不能被访问！', '', '');
	}
	pdo_update('uni_account_modules', array(
		'enabled' => empty($_GPC['enabled']) ? STATUS_OFF : STATUS_ON,
	), array(
		'module' => $modulename,
		'uniacid' => $_W['uniacid']
	));
	cache_build_module_info($modulename);
	itoast('模块操作成功！', referer(), 'success');
} elseif ($do == 'top') {
	$modulename = $_GPC['modulename'];
	$module = $modulelist[$modulename];
	if(empty($module)) {
		itoast('抱歉，你操作的模块不能被访问！', '', '');
	}
	$max_displayorder = (int)pdo_getcolumn('uni_account_modules', array('uniacid' => $_W['uniacid']), 'MAX(displayorder)');
	
	$module_profile = pdo_get('uni_account_modules', array('module' => $modulename, 'uniacid' => $_W['uniacid']));
	if (!empty($module_profile)) {
		pdo_update('uni_account_modules', array('displayorder' => ++$max_displayorder), array('id' => $module_profile['id']));
	} else {
		pdo_insert('uni_account_modules', array(
			'displayorder' => ++$max_displayorder,
			'module' => $modulename,
			'uniacid' => $_W['uniacid'],
			'enabled' => STATUS_ON,
			'shortcut' => STATUS_OFF,
		));
	}
	itoast('模块置顶成功', referer(), 'success');
} elseif ($do == 'setting') {
	$modulename = $_GPC['m'];
	$module = $_W['current_module'] = $modulelist[$modulename];
	
	if(empty($module)) {
		itoast('抱歉，你操作的模块不能被访问！', '', '');
	}

	if(!uni_user_module_permission_check($modulename.'_settings', $modulename)) {
		itoast('您没有权限进行该操作', '', '');
	}
	
	// 兼容历史性问题：模块内获取不到模块信息$module的问题
	define('CRUMBS_NAV', 1);
	
	$config = $module['config'];
	if (($module['settings'] == 2) && !is_file(IA_ROOT."/addons/{$module['name']}/developer.cer")) {
		
		if (empty($_W['setting']['site']['key']) || empty($_W['setting']['site']['token'])) {
			itoast('站点未注册，请先注册站点。', url('cloud/profile'), 'info');
		}
		
		if (empty($config)) {
			$config = array();
		}
		
		load()->model('cloud');
		load()->func('communication');
		
		$pro_attach_url = tomedia('pro_attach_url');
		$pro_attach_url = str_replace('pro_attach_url', '', $pro_attach_url);
		
		$module_simple = array_elements(array('name', 'type', 'title', 'version', 'settings'), $module);
		$module_simple['pro_attach_url'] = $pro_attach_url;
		
		$iframe = cloud_module_setting_prepare($module_simple, 'setting');
		$result = ihttp_post($iframe, array('inherit_setting' => base64_encode(iserializer($config))));
		if (is_error($result)) {
			itoast($result['message'], '', '');
		}
		$result = json_decode($result['content'], true);
		if (is_error($result)) {
			itoast($result['message'], '', '');
		}
		
		$module_simple = array_elements(array('name', 'type', 'title', 'version', 'settings'), $module);
		$module_simple['pro_attach_url'] = $pro_attach_url;
		
		$iframe = cloud_module_setting_prepare($module_simple, 'setting');
		template('profile/module_setting');
		exit();
	}
	$obj = WeUtility::createModule($module['name']);
	$obj->settingsDisplay($config);
	exit();
}

if ($do == 'permissions') {
	$name = $_GPC['m'];
	$module = $_W['current_module'] = $modulelist[$name];
	if(empty($module)) {
		itoast('抱歉，你操作的模块不能被访问！', '', '');
	}
	if(!uni_user_module_permission_check($name.'_permissions', $name)) {
		itoast('您没有权限进行该操作', '', '');
	}
	
	$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'display';

	if ($op == 'display') {
		$entries = module_entries($name);
		$user_permissions = pdo_getall('users_permission', array('uniacid' => $_W['uniacid'], 'type' => $name, 'uid <>' => ''), '', 'uid');
		$uids = !empty($user_permissions) && is_array($user_permissions) ? array_keys($user_permissions) : array();
		$users_lists = array();
		if (!empty($uids)) {
			$users_lists = pdo_getall('users', array('uid' => $uids), '', 'uid');
		}
		$current_module_permission = module_permission_fetch($name);
		if (!empty($current_module_permission)) {
			foreach ($current_module_permission as $key => $permission) {
				$permission_name[$permission['permission']] = $permission['title'];
			}
		}
		if (!empty($user_permissions)) {
			foreach ($user_permissions as $key => &$permission) {
				$permission['permission'] = explode('|', $permission['permission']);
				foreach ($permission['permission'] as $k => $val) {
					$permission['permission'][$val] = $permission_name[$val];
					unset($permission['permission'][$k]);
				}
				$permission['user_info'] = $users_lists[$key];
			}
			unset($permission);
		}
	}

	if ($op == 'post') {
		load()->model('module');
		$m = trim($_GPC['m']);
		$uniacid = intval($_W['uniacid']);
		$uid = intval($_GPC['uid']);
		//获取用户信息
		$user = pdo_get('users', array('uid' => $uid));
		$module = pdo_get('modules', array('name' => $m));
		//获取模块权限
		$purview = pdo_get('users_permission', array('uniacid' => $uniacid, 'uid' => $uid, 'type' => $m));
		if(!empty($purview['permission'])) {
			$purview['permission'] = explode('|', $purview['permission']);
		} else {
			$purview['permission'] = array();
		}

		$current_module_permission = module_permission_fetch($m);
		foreach ($current_module_permission as &$data) {
			$data['checked'] = 0;
			if(in_array($data['permission'], $purview['permission']) || in_array('all', $purview['permission'])) {
				$data['checked'] = 1;
			}
		}
		if (checksubmit()) {
			$insert_user = array();
			$insert_user['username'] = trim($_GPC['username']);
			$insert_user['remark'] = trim($_GPC['remark']);
			$insert_user['password'] = trim($_GPC['password']);
			$insert_user['repassword'] = trim($_GPC['repassword']);
			$insert_user['type'] = 3;
			$operator_id = intval($_GPC['uid']);
			if (empty($insert_user['username'])) {
				itoast('必须输入用户名，格式为 1-15 位字符，可以包括汉字、字母（不区分大小写）、数字、下划线和句点。', '', '');
			}
			if (empty($operator_id)) {
				if (user_check(array('username' => $insert_user['username']))) {
					itoast('非常抱歉，此用户名已经被注册，你需要更换注册名称！', '', '');
				}
				if (istrlen($insert_user['password']) < 8) {
					itoast('必须输入密码，且密码长度不得低于8位。', '', '');
				}
				if ($insert_user['repassword'] != $insert_user['password']) {
					itoast('两次输入密码不一致', '', '');
				}
				unset($insert_user['repassword']);
				$operator['uid'] = user_register($insert_user);
				if (!$operator['uid']) {
					itoast('注册账号失败', '', '');
				}
			} else {
				$operator = array();
				if (!empty($insert_user['password'])) {
					if (istrlen($insert_user['password']) < 8) {
						itoast('必须输入密码，且密码长度不得低于8位。', '', '');
					}
					if ($insert_user['repassword'] != $insert_user['password']) {
						itoast('两次输入密码不一致', '', '');
					}
					$operator['password'] = $insert_user['password'];
					$operator['salt'] = $user['salt'];
				}
				$operator['uid'] = $operator_id;
				$operator['username'] = $insert_user['username'];
				$operator['remark'] = $insert_user['remark'];
				$operator['type'] = $insert_user['type'];
				user_update($operator);
			}
			$permission = $_GPC['module_permission'];
			if (!empty($permission)) {
				$permission = implode('|', array_unique($permission));
			} else {
				$permission = '';
			}
			$permission_exist = pdo_get('users_permission', array('uniacid' => $_W['uniacid'], 'uid' => $operator['uid'], 'type' => $m));
			if (empty($permission_exist)) {
				pdo_insert('users_permission', array('uniacid' => $_W['uniacid'], 'uid' => $operator['uid'], 'type' => $m, 'permission' => $permission));
			} else {
				pdo_update('users_permission', array('permission' => $permission), array('uniacid' => $_W['uniacid'], 'uid' => $operator['uid'], 'type' => $m));
			}
			$account_user = pdo_get('uni_account_users', array('uniacid' => $_W['uniacid'], 'uid' => $operator['uid']));
			if (empty($account_user)) {
				pdo_insert('uni_account_users', array('uniacid' => $_W['uniacid'], 'uid' => $operator['uid'], 'role' => 'operator'));
			} else {
				pdo_update('uni_account_users', array('role' => 'operator'), array('uniacid' => $_W['uniacid'], 'uid' => $operator['uid']));
			}
			itoast('编辑店员资料成功', url('profile/module/permissions', array('m' => $m, 'op' => 'display')), 'success');
		}
	}

	if ($op == 'delete') {
		$operator_id = intval($_GPC['uid']);
		if (empty($operator_id)) {
			itoast('参数错误', referer(), 'error');
		} else {
			$user = pdo_get('users', array('uid' => $operator_id), array('uid'));
			if (!empty($user)) {
				pdo_delete('users', array('uid' => $operator_id));
				pdo_delete('uni_account_users', array('uid' => $operator_id, 'role' => 'operator', 'uniacid' => $_W['uniacid']));
				pdo_delete('users_permission', array('uid' => $operator_id, 'type' => $_GPC['m'], 'uniacid' => $_W['uniacid']));
			}
			itoast('删除成功', referer(), 'success');
		}
	}
	template('profile/module_permission');
}