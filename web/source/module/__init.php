<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (in_array($action, array('permission', 'default-entry', 'manage-account'))) {
	define('FRAME', 'account');
	$referer = (url_params(referer()));
	if (empty($_GPC['version_id']) && intval($referer['version_id']) > 0) {
		itoast('', $_W['siteurl'] . '&version_id=' . $referer['version_id']);
	}
	$account_api = WeAccount::createByUniacid($_W['uniacid']);
	if (is_error($account_api)) {
		itoast('', url('module/display'));
	}
	$check_manange = $account_api->checkIntoManage();
	if (is_error($check_manange)) {
		$account_display_url = $account_api->accountDisplayUrl();
		itoast('', $account_display_url);
	}
}
if (in_array($action, array('group', 'manage-system'))) {
	define('FRAME', 'system');
}

if (in_array($action, array('display'))) {
	define('FRAME', '');
}
/* sxstart */
if (IMS_FAMILY == 's' || IMS_FAMILY == 'x') {
	$_GPC['account_type'] = !empty($_GPC['account_type']) || !empty($_GPC['system_welcome']) ? $_GPC['account_type'] : ACCOUNT_TYPE_OFFCIAL_NORMAL;
	if (!empty($_GPC['system_welcome'])) {
		define('ACCOUNT_TYPE_TEMPLATE', '-welcome');
	}
}
/* sxend */
$account_base = WeAccount::createByType($_GPC['account_type']);
define('ACCOUNT_TYPE', $account_base->type);
define('ACCOUNT_TYPE_TEMPLATE', $account_base->typeTempalte);

$module_all_support = module_support_type();
$module_support = !empty($module_all_support[$_GPC['support']]) ? $module_all_support[$_GPC['support']]['type'] : MODULE_SUPPORT_ACCOUNT_NAME;
$module_support_name = $_GPC['support'];
/* vstart */
if (IMS_FAMILY == 'v') {
	$_GPC['account_type'] = !empty($_GPC['account_type']) ? $_GPC['account_type'] : ACCOUNT_TYPE_OFFCIAL_NORMAL;
}
/* vend */