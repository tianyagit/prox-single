<?php
/**
 * 编辑用户
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');
load()->func('file');
load()->func('cache');
load()->model('visit');

$dos = array('edit_base', 'edit_modules_tpl', 'edit_account', 'edit_users_permission');
$do = in_array($do, $dos) ? $do: 'edit_base';

$_W['page']['title'] = '编辑用户 - 用户管理';

$uid = intval($_GPC['uid']);
$user = user_single($uid);
if (empty($user)) {
	itoast('访问错误, 未找到该操作员.', url('user/display'), 'error');
}

$founders = explode(',', $_W['config']['setting']['founder']);
$profile = pdo_get('users_profile', array('uid' => $uid));
if (!empty($profile)) $profile['avatar'] = tomedia($profile['avatar']);

//编辑用户基础信息
if ($do == 'edit_base') {
	$account_num = permission_user_account_num($uid);
	$user['last_visit'] = date('Y-m-d H:i:s', $user['lastvisit']);
	$user['joindate'] = date('Y-m-d H:i:s', $user['joindate']);
	$user['end'] = $user['endtime'] == 0 ? '永久' : date('Y-m-d', $user['endtime']);
	$user['endtype'] = $user['endtime'] == 0 ? 1 : 2;
	$user['url'] = user_invite_register_url($uid);

	$profile = user_detail_formate($profile);
	
	$table = table('core_profile_fields');
	$extra_fields = $table->getExtraFields();
	template('user/edit-base');
}
