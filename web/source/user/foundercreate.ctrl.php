<?php
/**
 * 添加副创始人
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');

uni_user_permission_check('system_founder_user_add');
$_W['page']['title'] = '添加创始人 - 创始人管理';
$state = uni_permission($_W['uid']);
if ($state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_VICE_FOUNDER) {
	itoast('没有操作权限！', referer(), 'error');
}

if (checksubmit()) {
	$user_founder = array(
		'username' => trim($_GPC['username']),
		'password' => trim($_GPC['password']),
		'repassword' => trim($_GPC['repassword']),
		'remark' => $_GPC['remark'],
		'groupid' => intval($_GPC['groupid']),
		'starttime' => TIMESTAMP,
		'endtime' => intval($_GPC['timelimit']),
		'founder_groupid' => ACCOUNT_MANAGE_GROUP_VICE_FOUNDER
	);

	$user_add = user_info_save($user_founder, true);
	if (is_error($user_add)) {
		itoast($user_add['message'], '', '');
	}
	itoast($user_add['message'], url('user/edit', array('uid' => $user_add['uid'])), 'success');
}

$groups = user_founder_group();

template('user/founder-create');