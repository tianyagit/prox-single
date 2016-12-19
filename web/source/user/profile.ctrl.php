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

if($do == 'post' && $_W['isajax'] && $_W['ispost']) {
	$post = $_GPC;
	$type = $post['type'];

	$uid = is_array($post['uid']) ? 0 : intval($post['uid']);
	if(empty($uid)) message('-1', 'ajax', 'error');
	switch ($type) {
		case 'avatar':
			$result = pdo_update('users_profile', array('avatar' => $post['avatar']), array('uid' => $uid));
			break;
		case 'username':
			$result = pdo_update('users', array('username' => $post['username']), array('uid' => $uid));
			break;
		case 'password':
			if($post['newpwd'] !== $post['renewpwd']) message('2', 'ajax', 'error');
			$pwd = user_hash($post['oldpwd'], $user['salt']);
			if($pwd != $user['password']) message('3', 'ajax', 'error');
			$newpwd = user_hash($post['newpwd'], $user['salt']);
			$result = pdo_update('users', array('password' => $newpwd), array('uid' => $uid));
			break;
		case 'endtime' :
			if($post['endtype'] == 1) {
				$endtime = 0;
			}else {
				$endtime = strtotime($post['endtime']);
			}
			$result = pdo_update('users', array('endtime' => $endtime), array('uid' => $uid));
			break;
		case 'realname':
			$result = pdo_update('users_profile', array('realname' => $post['realname']), array('uid' => $uid));
			break;
		case 'birth':
			$result = pdo_update('users_profile', array('birthyear' => $post['year'], 'birthmonth' => $post['month'], 'birthday' => $post['day']), array('uid' => $uid));
			break;
		case 'address':
			$result = pdo_update('users_profile', array('address' => $post['address']), array('uid' => $uid));
			break;
		case 'reside':
			$result = pdo_update('users_profile', array('resideprovince' => $post['province'], 'residecity' => $post['city'], 'residedist' => $post['district']), array('uid' => $uid));
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

		$profile['births'] = ($profile['birthyear'] ? $profile['birthyear'] : '--') . '年' . ($profile['birthmonth'] ? $profile['birthmonth'] : '--') . '月' . ($profile['birthday'] ? $profile['birthday'] : '--') .'日';
	}
	template('user/profile');
}