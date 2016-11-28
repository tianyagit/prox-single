<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/user/group.ctrl.php : v a210cf4d3592 : 2015/09/09 10:18:06 : RenChao $
 */
defined('IN_IA') or exit('Access Denied');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

if ($do == 'display') {
	$where = '' ;
	$params = array();
	if (!empty($_GPC['name'])) {
		$where .= "WHERE name LIKE :name";
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
	$sql = 'SELECT * FROM ' . tablename('users_group').$where;
	$list = pdo_fetchall($sql, $params);
	foreach ($list as $key => $group) {
		$package = iunserializer($group['package']);
		$group['package'] = uni_groups($package);
		$list[$key]['ss'] = $package;
		 if (empty($list[$key]['ss'])) {
		 	$list[$key]['module_nums'] = $module_num;
		 	unset($list[$key]['ss']);
		 	continue;
		 }
		 if(is_array($list[$key]['ss'])) {
			 if (in_array(-1, $list[$key]['ss']) ) {
			 	$list[$key]['module_nums'] = -1;
			 	unset($list[$key]['ss']);
			 	continue;
			 }
		}
		foreach ($group['package'] as $modules) {
			$list[$key]['Packagess'][] = $modules['name'];
			if (is_array($modules['modules'])) {
				foreach ($modules['modules'] as  $module) {
					$list[$key]['module_num'][] = $module['name'];
				}
			}
			$list[$key]['module_num'] = array_unique($list[$key]['module_num']);
			$list[$key]['numss'] = $module_num;
			$list[$key]['nums'] = count($list[$key]['module_num']);
			$list[$key]['module_nums'] = $list[$key]['numss'] + $list[$key]['nums'];
			unset($list[$key]['ss']);
		}
		$list[$key]['Packages'] = implode(',', $list[$key]['Packagess']);
		unset($list[$key]['Packagess']);
	}
}

if ($do == 'post') {
	$id = intval($_GPC['id']);
	$_W['page']['title'] = $id ? '编辑用户组 - 用户组 - 用户管理' : '添加用户组 - 用户组 - 用户管理';
	if (!empty($id)) {
		$group = pdo_fetch("SELECT * FROM ".tablename('users_group') . " WHERE id = :id", array(':id' => $id));
		$group['package'] = iunserializer($group['package']);
	}
	$packages = uni_groups();
	if (checksubmit('submit')) {
		if (empty($_GPC['name'])) {
			message('请输入用户组名称！');
		}
		if (!empty($_GPC['package'])) {
			foreach ($_GPC['package'] as $value) {
				$package[] = intval($value);
			}
		}
		$data = array(
			'name' => $_GPC['name'],
			'package' => iserializer($package),
			'maxaccount' => intval($_GPC['maxaccount']),
			'timelimit' => intval($_GPC['timelimit'])
		);
		if (empty($id)) {
			pdo_insert('users_group', $data);
		} else {
			pdo_update('users_group', $data, array('id' => $id));
		}
		message('用户组更新成功！', url('user/group/display'), 'success');
	}
}

template('user/group');