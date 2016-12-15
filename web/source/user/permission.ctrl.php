<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

$_W['page']['title'] = '查看用户权限 - 用户管理';

load()->model('setting');

$do = $_GPC['do'];
$dos = array('deny');
$do = in_array($do, $dos) ? $do: 'deny';

$uid = intval($_GPC['uid']);
$user = user_single($uid);
if (empty($user)) {
	message('访问错误, 未找到指定操作用户.');
}

$founders = explode(',', $_W['config']['setting']['founder']);
$isfounder = in_array($user['uid'], $founders);
if ($isfounder) {
	message('访问错误, 无法编辑站长.');
}

//禁止/开启用户
if ($do == 'deny') {
	if ($_W['ispost'] && $_W['isajax']) {
		$founders = explode(',', $_W['config']['setting']['founder']);
		if (in_array($uid, $founders)) {
			exit('管理员用户不能禁用.');
		}
		$somebody = array();
		$somebody['uid'] = $uid;
		
		if (intval($user['status']) == 2) {
			$somebody['status'] = 1;
		} else {
			$somebody['status'] = 2;
		}
		if (user_update($somebody)) {
			exit('success');
		}
	}
}