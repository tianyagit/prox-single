<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

$_W['page']['title'] = '用户回收站 - 用户管理 - 账号/用户';

$do = $_GPC['do'];
$dos = array('delete', 'display','restore','recycle');
$do = in_array($do, $dos) ? $do: 'display';

$uid = intval($_GPC['uid']);
$user = user_single($uid);
$founders = explode(',', $_W['config']['setting']['founder']);
//回收站用户
if ($do == 'display') {
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$where = ' WHERE status =3 ';
$params = array();
if (!empty($_GPC['username'])) {
	$where .= " AND username LIKE :username";
	$params[':username'] = "%{$_GPC['username']}%";
}
$sql = 'SELECT * FROM ' . tablename('users') .$where . " LIMIT " . ($pindex - 1) * $psize .',' .$psize;
$users = pdo_fetchall($sql, $params);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('users') . $where, $params);
$pager = pagination($total, $pindex, $psize);
$module_num = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('modules') . "WHERE type = :type AND issystem = :issystem", array(':type' => 'system','issystem' => 1));
 foreach ($users as $key => $user) {
 	$uniacid_num = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('uni_account_users')." WHERE uid = :uid", array(':uid' => $user['uid']));
 	$users[$key]['uniacid_num'] = $uniacid_num;
 	$users[$key]['module_nums'] = $module_num;
 	$users[$key]['module_num'] =array();
 	$group = pdo_fetch("SELECT * FROM ".tablename('users_group')." WHERE id = '{$user['groupid']}'");
 	if (!empty($group)) {
 		$package = iunserializer($group['package']);
 		$group['package'] = uni_groups($package);
 		foreach ($group['package'] as $modules) {
 			if (is_array($modules['modules'])) {
 				foreach ($modules['modules'] as  $module) {
 					$users[$key]['module_num'][] = $module['name'];
 				}
 			}
 		}
 	$users[$key]['module_num'] = array_unique($users[$key]['module_num']);
 	$users[$key]['module_nums'] += count($users[$key]['module_num']);
 	unset($users[$key]['module_num']);
 	}
 	
 }
//管理员用户不需要显示查看操作，禁止用户，删除用户
$founders = explode(',', $_W['config']['setting']['founder']);
foreach ($users as &$user) {
	$user['founder'] = in_array($user['uid'], $founders);
}
unset($user);

$usergroups = pdo_fetchall("SELECT * FROM ".tablename('users_group'), array(), 'id');
$settings = $_W['setting']['register'];
	template('user/recycle');
}

//启用用户
if ($do == 'restore') {
	if ($_W['ispost'] && $_W['isajax']) {
		if (in_array($uid, $founders)) {
			message('访问错误, 无法操作站长.', url('user/display'), 'error');
		}
		if (empty($user)) {
			exit('未指定用户,无法删除.');
		}
		$founders = explode(',', $_W['config']['setting']['founder']);
		if (in_array($uid, $founders)) {
			exit('站长不能删除.');
		}
		$data = array('status' => 2);
		pdo_update('users', $data , array('uid' => $uid));
		exit('success');
	}
}

//删除用户到回收站
if ($do == 'recycle') {
	if ($_W['ispost'] && $_W['isajax']) {
		if (in_array($uid, $founders)) {
			message('访问错误, 无法操作站长.', url('user/display'), 'error');
		}
		if (empty($user)) {
			exit('未指定用户,无法删除.');
		}
		$founders = explode(',', $_W['config']['setting']['founder']);
		if (in_array($uid, $founders)) {
			exit('站长不能删除.');
		}
		$data = array('status' => 3);
		pdo_update('users', $data , array('uid' => $uid));
		exit('success');
	}
}

//删除用户
if ($do == 'delete') {
	if ($_W['ispost'] && $_W['isajax']) {
		if (in_array($uid, $founders)) {
			message('访问错误, 无法操作站长.', url('user/recycle'), 'error');
		}
		if (empty($user)) {
			exit('未指定用户,无法删除.');
		}
		$founders = explode(',', $_W['config']['setting']['founder']);
		if (in_array($uid, $founders)) {
			exit('站长不能删除.');
		}
		if (pdo_delete('users', array('uid' => $uid)) === 1) {
			//把该用户所属的公众号返给创始人
			cache_build_account_modules();
			pdo_delete('uni_account_users', array('uid' => $uid));
			pdo_delete('users_profile', array('uid' => $uid));
			exit('success');
		}
	}
}
