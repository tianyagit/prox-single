<?php
/**
 * 添加用户
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('user');
$_W['page']['title'] = '添加用户 - 用户管理';

if (checksubmit()) {
	$user_founder = array(
		'username' => safe_gpc_string($_GPC['username']),
		'password' => $_GPC['password'],
		'repassword' => $_GPC['repassword'],
		'remark' => safe_gpc_string($_GPC['remark']),
		'groupid' => intval($_GPC['groupid']) ? intval($_GPC['groupid']) : 0,
		'starttime' => TIMESTAMP,
		'endtime' => intval(strtotime($_GPC['endtime'])),
		'owner_uid' => 0,
	);

	$user_add = user_info_save($user_founder);
	if (is_error($user_add)) {
		itoast($user_add['message'], '', '');
	}
	$uid = $user_add['uid'];
	itoast($user_add['message'], url('user/edit', array('uid' => $user_add['uid'])), 'success');
}
$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates'));
template('user/create');