<?php
/**
 * 添加用户
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');

uni_user_permission_check('system_user_post');
$_W['page']['title'] = '添加用户 - 用户管理';
$state = uni_permission($_W['uid']);
if (!in_array($state, array(ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER))) {
	itoast('没有操作权限！', referer(), 'error');
}

if (checksubmit()) {
	$username = trim($_GPC['username']);
	$vice_founder_name = trim($_GPC['vice_founder_name']);
	if (!preg_match(REGULAR_USERNAME, $username)) {
		itoast('必须输入用户名，格式为 3-15 位字符，可以包括汉字、字母（不区分大小写）、数字、下划线和句点。', '', '');
	}
	if (user_check(array('username' => $username))) {
		itoast('非常抱歉，此用户名已经被注册，你需要更换注册名称！', '', '');
	}
	if (istrlen($_GPC['password']) < 8) {
		itoast('必须输入密码，且密码长度不得低于8位。', '', '');
	}
	if (trim($_GPC['password']) !== trim($_GPC['repassword'])) {
		itoast('两次密码不一致！', '', '');
	}
	$timelimit = intval($group['timelimit']);
	$timeadd = 0;
	if ($timelimit > 0) {
		$timeadd = strtotime($timelimit . ' days');
	}
	$data = array(
			'username' => $username,
			'password' => trim($_GPC['password']),
			'remark' => $_GPC['remark'],
			'starttime' => TIMESTAMP,
			'endtime' => $timeadd,
	);
	if ($do != ACCOUNT_MANAGE_NAME_VICE_FOUNDER) {
		if (!intval($_GPC['groupid'])) {
			itoast('请选择所属用户组', '', '');
		}
		$group = pdo_fetch("SELECT id,timelimit FROM ".tablename('users_group')." WHERE id = :id", array(':id' => intval($_GPC['groupid'])));
		if (empty($group)) {
			itoast('会员组不存在', '', '');
		}
		$data['groupid'] = intval($_GPC['groupid']);
		$vice_founder_id = user_get_uid_byname($vice_founder_name);
		if (empty($vice_founder_id)) {
			itoast('推荐人不存在！', '', '');
		}
		$data['vice_founder_id'] = $vice_founder_id == ture ? 0 : $vice_founder_id;
		if (!empty($_W['is_vice_founder'])) {
			$data['vice_founder_id'] = $_W['uid'];
		}
	}

	if ($do == ACCOUNT_MANAGE_NAME_VICE_FOUNDER) {
		$data['is_vice_founder'] = 1;
	}

	$uid = user_register($data);
	if ($uid > 0) {
		unset($data);
		itoast('增加成功！', url('user/edit/' . $do, array('uid' => $uid)), 'success');
	}
	itoast('增加失败，请稍候重试或联系网站管理员解决！', '', '');
}
$group_condition = array();
if (!empty($_W['is_vice_founder'])) {
	$group_condition['vice_founder_id'] = $_W['uid'];
}
$groups = pdo_getall('users_group', $group_condition, array('id', 'name'));
template('user/create');