<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

load()->model('setting');
load()->model('user');

$_W['page']['title'] = '编辑用户 - 用户管理 - 账号/用户';

$do = $_GPC['do'];
$dos = array('edit');
$do = in_array($do, $dos) ? $do: 'edit';

$uid = intval($_GPC['uid']);
$user = user_single($uid);
$founders = explode(',', $_W['config']['setting']['founder']);

//编辑用户
if ($do == 'edit') {
	if (empty($user)) {
		message('访问错误, 未找到指定操作员.', url('user/display'), 'error');
	}
	$extendfields = pdo_fetchall("SELECT field, title, description, required FROM ".tablename('profile_fields')." WHERE available = '1' AND showinregister = '1'");
	$user['profile'] = pdo_fetch("SELECT * FROM ".tablename('users_profile')." WHERE uid = :uid", array(':uid' => $uid));
	$user['profile']['reside'] = array(
		'province' => $user['profile']['resideprovince'],
		'city' => $user['profile']['residecity'],
		'district' => $user['profile']['residedist'],
	);
	unset($user['profile']['resideprovince']);
	unset($user['profile']['residecity']);
	unset($user['profile']['residedist']);
	$user['profile']['birth'] = array(
		'year' => $user['profile']['birthyear'],
		'month' => $user['profile']['birthmonth'],
		'day' => $user['profile']['birthday'],
	);
	unset($user['profile']['birthyear']);
	unset($user['profile']['birthmonth']);
	unset($user['profile']['birthday']);
	$groups = pdo_fetchall("SELECT id, name FROM ".tablename('users_group')." ORDER BY id ASC");
	template('user/edit');
}

