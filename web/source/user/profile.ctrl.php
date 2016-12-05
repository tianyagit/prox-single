<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('base', 'post');
$do = in_array($do, $dos) ? $do : 'base';
uni_user_permission_check('system_user_profile');

load()->model('user');
$uid = intval($_GPC['uid']) ? intval($_GPC['uid']) : $_W['uid'];
$user = user_single($uid);

if($do == 'post' && $_W['isajax'] && $_W['ispost']) {
	$post = $_GPC['__input'];
	$type = $post['type'];
	if(empty($user)) message('-1', 'ajax', 'error');
	switch ($type) {
		case 'avatar':
			$result = pdo_update('users_profile', array('avatar' => $post['avatar']), array('uid' => $_W['uid']));
			break;
		case 'username':
			$result = pdo_update('users', array('username' => $post['username']), array('uid' => $_W['uid']));
			break;
		case 'password':
			if($post['newpwd'] !== $post['renewpwd']) message('2', 'ajax', 'error');
			$pwd = user_hash($post['oldpwd'], $user['salt']);
			if($pwd != $user['password']) message('3', 'ajax', 'error');
			$newpwd = user_hash($post['newpwd'], $user['salt']);
			$result = pdo_update('users', array('password' => $newpwd), array('uid' => $_W['uid']));
			break;
		case 'realname':
			$result = pdo_update('users_profile', array('realname' => $post['realname']), array('uid' => $_W['uid']));
			break;
		case 'birth':
			$result = pdo_update('users_profile', array('birthyear' => $post['year'], 'birthmonth' => $post['month'], 'birthday' => $post['day']), array('uid' => $_W['uid']));
			break;
		case 'address':
			$result = pdo_update('users_profile', array('address' => $post['address']), array('uid' => $_W['uid']));
			break;
		case 'reside':
			$result = pdo_update('users_profile', array('resideprovince' => $post['province'], 'residecity' => $post['city'], 'residedist' => $post['district']), array('uid' => $_W['uid']));
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
	$_W['page']['title'] = '账号信息 - 我的账户 - 用户管理';

	if (empty($user)) {
		message('抱歉，用户不存在或是已经被删除！', url('user/profile'), 'error');
	}
	$user['last_visit'] = date('Y-m-d H:i:s', $user['lastvisit']);
	
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
	$profile['avatar'] = tomedia($profile['avatar']);
	$profile['resides'] = $profile['resideprovince'] . $profile['residecity'] . $profile['residedist'] ;
	$profile['births'] = $profile['birthyear'] . '年' . $profile['birthmonth'] . '月' . $profile['birthday'] .'日' ;
	template('user/profile');
}