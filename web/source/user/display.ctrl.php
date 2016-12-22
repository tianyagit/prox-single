<?php
/**
 * 用户列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'check_display', 'check_pass', 'recycle_display', 'recycle_delete','recycle_restore', 'recycle');
$do = in_array($do, $dos) ? $do: 'display';
uni_user_permission_check('system_user_display');

$_W['page']['title'] = '用户列表 - 用户管理';
$founders = explode(',', $_W['config']['setting']['founder']);

if(in_array($do, array('display', 'recycle_display', 'check_display'))) {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	switch ($do) {
		case 'check_display':
			$condition = ' WHERE status = 1 ';
			break;
		case 'recycle_display':
			$condition = ' WHERE status = 3 ';
			break;
		default:
			$condition = ' WHERE status = 2 ';
			break;
	}
	$params = array();
	if (!empty($_GPC['username'])) {
		$condition .= " AND username LIKE :username";
		$params[':username'] = "%{$_GPC['username']}%";
	}
	$sql = 'SELECT * FROM ' . tablename('users') .$condition . " LIMIT " . ($pindex - 1) * $psize .',' .$psize;
	$users = pdo_fetchall($sql, $params);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('users') . $condition, $params);
	$pager = pagination($total, $pindex, $psize);
	$system_module_num = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('modules') . "WHERE type = :type AND issystem = :issystem", array(':type' => 'system',':issystem' => 1));
	foreach ($users as &$user) {
		if(empty($user['endtime'])) {
			$user['endtime'] = '永久有效';
		}else {
			if($user['endtime'] <= TIMESTAMP) {
				$user['endtime'] = '服务已到期';
			}else {
				$user['endtime'] = date('Y-m-d', $user['endtime']);
			}
		}

		$user['founder'] = in_array($user['uid'], $founders);
		$user['uniacid_num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('uni_account_users')." WHERE uid = :uid", array(':uid' => $user['uid']));

		$user['module_num'] =array();
		$group = pdo_fetch("SELECT * FROM ".tablename('users_group')." WHERE id = '{$user['groupid']}'");
		if (!empty($group)) {
			$user['maxaccount'] = in_array($user['uid'], $founders) ? '不限' : $group['maxaccount'];
			$user['groupname'] = $group['name'];
			$package = iunserializer($group['package']);
			$group['package'] = uni_groups($package);
			foreach ($group['package'] as $modules) {
				if (is_array($modules['modules'])) {
					foreach ($modules['modules'] as  $module) {
						$user['module_num'][] = $module['name'];
					}
				}
			}
		}

		$user['module_num'] = array_unique($user['module_num']);
		$user['module_nums'] = count($user['module_num']) + $system_module_num;
	}
	unset($user);
	$usergroups = pdo_fetchall("SELECT * FROM ".tablename('users_group'), array(), 'id');
	template('user/display');
}

if(in_array($do, array('recycle', 'recycle_delete', 'recycle_restore', 'check_pass'))) {
	$uid = intval($_GPC['uid']);
	$uid_user = user_single($uid);
	if (in_array($uid, $founders)) {
		message('访问错误, 无法操作站长.', url('user/display'), 'error');
	}
	if (empty($uid_user)) {
		exit('未指定用户,无法删除.');
	}
	switch ($do) {
		case 'check_pass':
			$data = array('status' => 2);
			pdo_update('users', $data , array('uid' => $uid));
			message('更新成功！', referer());
			break;
		case 'recycle'://删除用户到回收站
			$data = array('status' => 3);
			pdo_update('users', $data , array('uid' => $uid));
			message('更新成功！', referer());
			break;
		case 'recycle_delete'://永久删除用户
			if (pdo_delete('users', array('uid' => $uid)) === 1) {
				//把该用户所属的公众号返给创始人
				cache_build_account_modules();
				pdo_delete('uni_account_users', array('uid' => $uid));
				pdo_delete('users_profile', array('uid' => $uid));
				message('删除成功！', referer());
			}else {
				message('删除失败！', referer());
			}
			break;
		case 'recycle_restore':
			$data = array('status' => 2);
			pdo_update('users', $data , array('uid' => $uid));
			message('启用成功！', referer());
			break;
	}
}