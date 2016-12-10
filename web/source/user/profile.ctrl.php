<?php
/**
 * 我的账户
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');

$dos = array('base', 'post');
$do = in_array($do, $dos) ? $do : 'base';
uni_user_permission_check('system_user_profile');

$_W['page']['title'] = '账号信息 - 我的账户 - 用户管理';

if($do == 'post' && $_W['ispost']) {
	if(!empty($_GPC['type'])) {
		$type = $_GPC['type'];
	}else {
		message('40035', 'ajax', 'error');
	}
	$uid = is_array($_GPC['uid']) ? 0 : intval($_GPC['uid']);
	if(empty($uid)) message('-1', 'ajax', 'error');
	switch ($type) {
		case 'avatar':
			$result = pdo_update('users_profile', array('avatar' => $_GPC['avatar']), array('uid' => $uid));
			break;
		case 'username':
			$result = pdo_update('users', array('username' => $_GPC['username']), array('uid' => $uid));
			break;
		case 'password':
			if($_GPC['newpwd'] !== $_GPC['renewpwd']) message('2', 'ajax', 'error');
			$pwd = user_hash($_GPC['oldpwd'], $user['salt']);
			if($pwd != $user['password']) message('3', 'ajax', 'error');
			$newpwd = user_hash($_GPC['newpwd'], $user['salt']);
			$result = pdo_update('users', array('password' => $newpwd), array('uid' => $uid));
			break;
		case 'endtime' :
			if($_GPC['endtype'] == 1) {
				$endtime = 0;
			}else {
				$endtime = strtotime($_GPC['endtime']);
			}
			$result = pdo_update('users', array('endtime' => $endtime), array('uid' => $uid));
			break;
		case 'realname':
			$result = pdo_update('users_profile', array('realname' => $_GPC['realname']), array('uid' => $uid));
			break;
		case 'birth':
			$result = pdo_update('users_profile', array('birthyear' => $_GPC['year'], 'birthmonth' => $_GPC['month'], 'birthday' => $_GPC['day']), array('uid' => $uid));
			break;
		case 'address':
			$result = pdo_update('users_profile', array('address' => $_GPC['address']), array('uid' => $uid));
			break;
		case 'reside':
			$result = pdo_update('users_profile', array('resideprovince' => $_GPC['province'], 'residecity' => $_GPC['city'], 'residedist' => $_GPC['district']), array('uid' => $uid));
			break;
	}
	if($result) {
		message('0', 'ajax', 'success');
	}else {
		message('1', 'ajax', 'error');
	}
}

//账号信息
if ($do == 'base') {
	$uid = intval($_GPC['uid']) ? intval($_GPC['uid']) : $_W['uid'];
	$user = user_single($uid);
	if (empty($user)) {
		message('抱歉，用户不存在或是已经被删除！', url('user/profile'), 'error');
	}
	$user['last_visit'] = date('Y-m-d H:i:s', $user['lastvisit']);
	
	$profile = pdo_fetch('SELECT * FROM '.tablename('users_profile').' WHERE `uid` = :uid LIMIT 1',array(':uid' => $_W['uid']));
	if(!empty($profile)) {
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
		$profile['avatar'] = tomedia($profile['avatar']);
		$profile['resides'] = $profile['resideprovince'] . $profile['residecity'] . $profile['residedist'] ;
		$profile['births'] = $profile['birthyear'] . '年' . $profile['birthmonth'] . '月' . $profile['birthday'] .'日' ;
	}
	template('user/profile');
}