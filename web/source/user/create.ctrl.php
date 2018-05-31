<?php
/**
 * 添加用户
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');

$_W['page']['title'] = '添加用户 - 用户管理';

if (checksubmit()) {
	$vice_founder_name = safe_gpc_string($_GPC['vice_founder_name']);
	$is_founder = user_single(array('username' => $vice_founder_name));
	if (empty($is_founder)) {
		itoast('副创始人不存在！');
	}
	if (!user_is_vice_founder($is_founder['uid'])) {
		itoast('请勿添加非副创始人姓名！');
	}
	$user_founder = array(
		'username' => safe_gpc_string($_GPC['username']),
		'password' => $_GPC['password'],
		'repassword' => $_GPC['repassword'],
		'remark' => safe_gpc_string($_GPC['remark']),
		'groupid' => intval($_GPC['groupid']),
		'starttime' => TIMESTAMP,
		'endtime' => intval($_GPC['timelimit']),
		'vice_founder_name' => $vice_founder_name
	);

	$user_add = user_info_save($user_founder);
	if (is_error($user_add)) {
		itoast($user_add['message'], '', '');
	}
	itoast($user_add['message'], url('user/edit', array('uid' => $user_add['uid'])), 'success');
}

$groups = user_group();
template('user/create');