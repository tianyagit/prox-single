<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('application','base','account');
$do = in_array($do, $dos) ? $do : 'base';

$uid = intval($_GPC['uid'])?intval($_GPC['uid']):$_W['uid'];
$user = user_single($uid);
//账号信息
if ($do == 'base') {
	$_W['page']['title'] = '账号信息 - 我的账户 - 用户管理';
	$sql = "SELECT username, password, salt, groupid, lastvisit, lastip , starttime, endtime FROM " . tablename('users') . " WHERE `uid` = '{$_W['uid']}'";
	$user = pdo_fetch($sql);

	if (empty($user)) {
		message('抱歉，用户不存在或是已经被删除！', url('user/profile'), 'error');
	}
	$user['groupname'] = pdo_fetchcolumn('SELECT name FROM ' . tablename('users_group') . ' WHERE id = :id', array(':id' => $user['groupid']));
	$user['role'] = pdo_fetchcolumn('SELECT role FROM ' . tablename('uni_account_users') . ' WHERE uid = :uid', array(':uid' => $_W['uid']));
	
	$extendfields = pdo_fetchall("SELECT field, title, description, required FROM ".tablename('profile_fields')." WHERE available = '1' AND showinregister = '1' ORDER BY displayorder DESC");
	$profile = pdo_fetch('SELECT * FROM '.tablename('users_profile').' WHERE `uid` = :uid LIMIT 1',array(':uid' => $_W['uid']));
	$profile['reside'] = array(
		'province' => $profile['resideprovince'],
		'city' => $profile['residecity'],
		'district' => $profile['residedist']
	);
	$profile['birth'] = array(
		'year' => $profile['birthyear'],
		'month' => $profile['birthmonth'],
		'day' => $profile['birthday'],
	);
	$profile['resides'] = $profile['resideprovince'] . $profile['residecity'] . $profile['residedist'] ;
	$profile['births'] = $profile['birthyear'] . '年' . $profile['birthmonth'] . '月' . $profile['birthday'] .'日' ;
}

//应用
if ($do == 'application') {
}
//公众号列表
if ($do == 'account') {
	//获取用户组信息
	if (!empty($user['groupid'])) {
		$group = pdo_fetch("SELECT * FROM ".tablename('users_group')." WHERE id = '{$user['groupid']}'");
		if (!empty($group)) {
			$package = iunserializer($group['package']);
			$group['package'] = uni_groups($package);
		}
	}
	$weids = pdo_fetchall("SELECT uniacid, role FROM ".tablename('uni_account_users')." WHERE uid = '$uid'", array(), 'uniacid');
	if (!empty($weids)) {
		$wechats = pdo_fetchall("SELECT * FROM ".tablename('uni_account')." WHERE uniacid IN (".implode(',', array_keys($weids)).")");
		foreach ($wechats as $key => $wechat) {
			$sql = "SELECT acid , level FROM " . tablename('account_wechats') . " WHERE `uniacid` = '{$wechat['uniacid']}'";
			$account = pdo_fetch($sql);
			$wechats[$key]['acid'] = $account['acid'];
			$wechats[$key]['level'] = $account['level'];
		}
	}
}

template('user/profile');
