<?php
/**
 * 用户管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');
load()->model('message');

$dos = array('display', 'operate');
$do = in_array($do, $dos) ? $do: 'display';

$_W['page']['title'] = '用户列表 - 用户管理';
$founders = explode(',', $_W['config']['setting']['founder']);

if ($do == 'display') {
	$message_id = $_GPC['message_id'];
	message_notice_read($message_id);

	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$users_table = table('users');
	$type = empty($_GPC['type']) ? 'display' : $_GPC['type'];
	if (in_array($type, array('display', 'check', 'recycle', 'clerk'))) {
		switch ($type) {
			case 'check':
				permission_check_account_user('system_user_check');
				$users_table->searchWithStatus(USER_STATUS_CHECK);
				break;
			case 'recycle':
				permission_check_account_user('system_user_recycle');
				$users_table->searchWithStatus(USER_STATUS_BAN);
				break;
			case 'clerk':
				permission_check_account_user('system_user_clerk');
				$users_table->searchWithStatus(USER_STATUS_NORMAL);
				$users_table->searchWithType(USER_TYPE_CLERK);
				break;
			default:
				permission_check_account_user('system_user');
				$users_table->searchWithStatus(USER_STATUS_NORMAL);
				$users_table->searchWithType(USER_TYPE_COMMON);
				$users_table->searchWithFounder(array(ACCOUNT_MANAGE_GROUP_GENERAL, ACCOUNT_MANAGE_GROUP_FOUNDER));
				break;
		}

		$username = trim($_GPC['username']);
		if (!empty($username)) {
			$users_table->searchWithName($username);
		}

		/* xstart */
		if (IMS_FAMILY == 'x') {
			if (user_is_vice_founder()) {
				$users_table->searchWithOwnerUid($_W['uid']);
			}
		}
		/* xend */

		$users_table->searchWithPage($pindex, $psize);
		$users = $users_table->searchUsersList();
		$total = $users_table->getLastQueryTotal();
		$users = user_list_format($users);
		$pager = pagination($total, $pindex, $psize);
	}
	template('user/display');
}

if ($do == 'operate') {
	if (!$_W['isajax'] || !$_W['ispost']) {
		iajax(-1, '非法操作！', referer());
	}
	$type = $_GPC['type'];
	$types = array('recycle', 'recycle_delete', 'recycle_restore', 'check_pass');
	if (!in_array($type, $types)) {
		iajax(-1, '类型错误!', referer());
	}
	switch ($type) {
		case 'check_pass':
			permission_check_account_user('system_user_check');
			break;
		case 'recycle':
		case 'recycle_delete':
		case 'recycle_restore':
			permission_check_account_user('system_user_recycle');
			break;
	}
	$uid = intval($_GPC['uid']);
	$uid_user = user_single($uid);
	if (in_array($uid, $founders)) {
		iajax(-1, '访问错误, 无法操作站长.', url('user/display'));
	}
	if (empty($uid_user)) {
		exit('未指定用户,无法删除.');
	}
	/* xstart */
	if (IMS_FAMILY == 'x') {
		if ($uid_user['founder_groupid'] != ACCOUNT_MANAGE_GROUP_GENERAL) {
			iajax(-1, '非法操作', referer());
		}
	}
	/* xend */
	$users_table = table('users');
	switch ($type) {
		case 'check_pass':
			$data = array('status' => 2);
			pdo_update('users', $data , array('uid' => $uid));
			iajax(0, '更新成功', referer());
			break;
		case 'recycle'://删除用户到回收站
			user_delete($uid, true);
			iajax(0, '更新成功', referer());
			break;
		case 'recycle_delete'://永久删除用户
			user_delete($uid);
			iajax(0, '删除成功', referer());
			break;
		case 'recycle_restore':
			$data = array('status' => 2);
			pdo_update('users', $data , array('uid' => $uid));
			iajax(0, '启用成功', referer());
			break;
	}
}