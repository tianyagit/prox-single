<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (in_array($action, array('permission', 'manage-account'))) {
	define('FRAME', 'account');
	$referer = (url_params(referer()));
	if (empty($_GPC['version_id']) && intval($referer['version_id']) > 0) {
		itoast('', $_W['siteurl'] . '&version_id=' . $referer['version_id']);
	}
	if (!empty($_GPC['version_id'])) {
		checkwxapp();
	} else {
		checkaccount();
	}
}
if (in_array($action, array('group', 'manage-system'))) {
	define('FRAME', 'system');
}
/* xstart */
if (IMS_FAMILY == 'x') {
	$_GPC['account_type'] = !empty($_GPC['account_type']) || !empty($_GPC['system_welcome']) ? $_GPC['account_type'] : ACCOUNT_TYPE_OFFCIAL_NORMAL;
	if (!empty($_GPC['system_welcome'])) {
		define('ACCOUNT_TYPE_TEMPLATE', '-welcome');
	}
}
/* xend */
/* vstart */
if (IMS_FAMILY == 'v') {
	$_GPC['account_type'] = !empty($_GPC['account_type']) ? $_GPC['account_type'] : ACCOUNT_TYPE_OFFCIAL_NORMAL;
}
/* vend */
if ($_GPC['account_type'] == ACCOUNT_TYPE_APP_NORMAL) {
	define('ACCOUNT_TYPE', ACCOUNT_TYPE_APP_NORMAL);
	define('ACCOUNT_TYPE_TEMPLATE', '-wxapp');
} elseif ($_GPC['account_type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL || $_GPC['account_type'] == ACCOUNT_TYPE_OFFCIAL_AUTH) {
	define('ACCOUNT_TYPE', ACCOUNT_TYPE_OFFCIAL_NORMAL);
	define('ACCOUNT_TYPE_TEMPLATE', '');
} else {
	define('ACCOUNT_TYPE', $_GPC['account_type']);
}