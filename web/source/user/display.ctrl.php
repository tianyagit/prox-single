<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$_W['page']['title'] = '用户列表 - 用户管理 - 账号/用户';

$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$where = ' WHERE status!=3 ';
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
 	$uniacid_create_num = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('uni_account_users')." WHERE uid = :uid AND role IN('owner','manager')", array(':uid' => $user['uid']));
 	$users[$key]['uniacid_num'] = $uniacid_num;
 	$users[$key]['uniacid_create_num'] = $uniacid_create_num;
 	$users[$key]['module_nums'] = $module_num;
 	$users[$key]['module_num'] =array();
 	$group = pdo_fetch("SELECT * FROM ".tablename('users_group')." WHERE id = '{$user['groupid']}'");
 	if (!empty($group)) {
 		$package = iunserializer($group['package']);
 		$group['package'] = uni_groups($package);
 		$users[$key]['ss'] = $package;
 		if (empty($users[$key]['ss'])) {
 			$users[$key]['module_nums'] = $module_num;
 			unset($users[$key]['ss']);
 			continue;
 		}
 		if(is_array($users[$key]['ss'])) {
 			if (in_array(-1, $users[$key]['ss']) ) {
 				$users[$key]['module_nums'] = -1;
 				unset($users[$key]['ss']);
 				continue;
 			}
 		}
 		foreach ($group['package'] as $modules) {
 			if (is_array($modules['modules'])) {
 				foreach ($modules['modules'] as  $module) {
 					$users[$key]['module_num'][] = $module['name'];
 				}
 			}
 		}
 		$users[$key]['module_num'] = array_unique($users[$key]['module_num']);
 		$users[$key]['numss'] = $module_num;
 		$users[$key]['nums'] = count($users[$key]['module_num']);
 		$users[$key]['module_nums'] = $users[$key]['numss'] + $users[$key]['nums'];
 		unset($users[$key]['ss']);
 	}
 }
//管理员用户不需要显示查看操作，禁止用户，删除用户
$founders = explode(',', $_W['config']['setting']['founder']);
foreach ($users as &$user) {
	$user['founder'] = in_array($user['uid'], $founders);
}
unset($user);
	array_shift($users);
$usergroups = pdo_fetchall("SELECT * FROM ".tablename('users_group'), array(), 'id');
$settings = $_W['setting']['register'];
template('user/display');
