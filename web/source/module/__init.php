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
	$account_api = WeAccount::create();
	$check_manange = $account_api->checkIntoManage();
	if (is_error($check_manange)) {
		$account_display_url = $account_api->accountDisplayUrl();
		itoast('', $account_display_url);
	}
}
if (in_array($action, array('group', 'manage-system'))) {
	define('FRAME', 'system');
}
$account_param = WeAccount::createByType($_GPC['account_type']);
define('ACCOUNT_TYPE', $account_param->accountManageType);
define('ACCOUNT_TYPE_TEMPLATE', $account_param->accountTypeTemplate);

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