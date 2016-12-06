<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

$_W['page']['title'] = '用户回收站 - 用户管理';
$do = $_GPC['do'];
$dos = array('delete', 'display','restore');
$do = in_array($do, $dos) ? $do: 'display';

$uid = intval($_GPC['uid']);
$user = user_single($uid);
$founders = explode(',', $_W['config']['setting']['founder']);

//删除用户
if ($do == 'delete') {
	if (in_array($uid, $founders)) {
		message('访问错误, 无法操作站长.', url('user/recycle'), 'error');
	}
	if (empty($user)) {
		exit('未指定用户,无法删除.');
	}
	if (pdo_delete('users', array('uid' => $uid)) === 1) {
		//把该用户所属的公众号返给创始人
		cache_build_account_modules();
		pdo_delete('uni_account_users', array('uid' => $uid));
		pdo_delete('users_profile', array('uid' => $uid));
		message('删除成功！', referer());
	}
}

//启用用户
if ($do == 'restore') {
	if (in_array($uid, $founders)) {
		message('访问错误, 无法操作站长.', url('user/display'));
	}
	if (empty($user)) {
		exit('未指定用户,无法删除.');
	}
	$data = array('status' => 2);
	pdo_update('users', $data , array('uid' => $uid));
	message('启用成功！', referer());
}

//回收站用户
if ($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' WHERE status =3 ';
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
			$user['maxaccount'] = $group['maxaccount'];
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
	$usergroups = pdo_fetchall("SELECT * FROM ".tablename('users_group'), array(), 'id');
	template('user/recycle');
}


