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
		$account_api = WeAccount::create(array('type' => ACCOUNT_TYPE_APP_NORMAL));
	} elseif (empty($_W['uniacid']) || empty($_W['account']['type'])){
		$account_api = WeAccount::create(array('type' => ACCOUNT_SUBSCRIPTION));
	}
	$check_manange = $account_api->checkIntoManage();
	if (is_error($check_manange)) {
		$no_check_account_url = $account_api->noCheckAccountUrl();
		itoast('', $no_check_account_url);
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
} elseif ($_GPC['account_type'] == ACCOUNT_TYPE_WEBAPP_NORMAL){
	define('ACCOUNT_TYPE', ACCOUNT_TYPE_WEBAPP_NORMAL);
	define('ACCOUNT_TYPE_TEMPLATE', '-webapp');
} else {
	define('ACCOUNT_TYPE', $_GPC['account_type']);
}