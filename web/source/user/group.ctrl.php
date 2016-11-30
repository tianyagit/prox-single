<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'post', 'del');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

if ($do == 'display') {
	$condition = '' ;
	$params = array();
	if (!empty($_GPC['name'])) {
		$condition .= "WHERE name LIKE :name";
		$params[':name'] = "%{$_GPC['name']}%";
	}
	$_W['page']['title'] = '用户组列表 - 用户组 - 用户管理';
	if (checksubmit('submit')) {
		if (!empty($_GPC['delete'])) {
			pdo_query("DELETE FROM ".tablename('users_group')." WHERE id IN ('".implode("','", $_GPC['delete'])."')");
		}
		message('用户组更新成功！', referer(), 'success');
	}
	$module_num = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('modules') . "WHERE type = :type AND issystem = :issystem", array(':type' => 'system','issystem' => 1));
	$sql = 'SELECT * FROM ' . tablename('users_group').$condition;
	$lists = pdo_fetchall($sql, $params);
	foreach ($lists as $key => $group) {
		$package = iunserializer($group['package']);
		$group['package'] = uni_groups($package);
		if (empty($package)) {
			$lists[$key]['module_nums'] = $module_num;
			continue;
		}
		if(is_array($package) && in_array(-1, $package)) {
			$lists[$key]['module_nums'] = -1;
			continue;
		}
		$names = array();
		foreach ($group['package'] as $modules) {
			$names[] = $modules['name'];
			$lists[$key]['nums'] = count($modules['modules']);
			$lists[$key]['module_nums'] = $module_num + $lists[$key]['nums'];
		}
		$lists[$key]['Packages'] = implode(',', $names);
	}
	template('user/group-display');
}

if ($do == 'post') {
	$id = intval($_GPC['id']);
	echo $id;
	exit;
	// $_W['page']['title'] = $id ? '编辑用户组 - 用户组 - 用户管理' : '添加用户组 - 用户组 - 用户管理';
	// if (!empty($id)) {
	// 	$group = pdo_fetch("SELECT * FROM ".tablename('users_group') . " WHERE id = :id", array(':id' => $id));
	// 	$group['package'] = iunserializer($group['package']);
	// }
	// $packages = uni_groups();
	// if (checksubmit('submit')) {
	// 	if (empty($_GPC['name'])) {
	// 		message('请输入用户组名称！');
	// 	}
	// 	if (!empty($_GPC['package'])) {
	// 		foreach ($_GPC['package'] as $value) {
	// 			$package[] = intval($value);
	// 		}
	// 	}
	// 	$data = array(
	// 		'name' => $_GPC['name'],
	// 		'package' => iserializer($package),
	// 		'maxaccount' => intval($_GPC['maxaccount']),
	// 		'timelimit' => intval($_GPC['timelimit'])
	// 	);
	// 	if (empty($id)) {
	// 		pdo_insert('users_group', $data);
	// 	} else {
	// 		pdo_update('users_group', $data, array('id' => $id));
	// 	}
	// 	message('用户组更新成功！', url('user/group/display'), 'success');
	// }
}

if($do == 'del') {
	$id = intval($_GPC['id']);
	exit;
}