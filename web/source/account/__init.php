<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
if ($action != 'display') {
	define('FRAME', 'system');
}
if ($controller == 'account' && $action == 'manage') {
	if ($_GPC['account_type'] == ACCOUNT_TYPE_APP_NORMAL) {
		define('ACTIVE_FRAME_URL', url('account/manage/display', array('account_type' => ACCOUNT_TYPE_APP_NORMAL)));
	}
}

$_GPC['account_type'] = !empty($_GPC['account_type']) ? $_GPC['account_type'] : ACCOUNT_TYPE_OFFCIAL_NORMAL;
$account_api = WeAccount::create(array('type' => $_GPC['account_type']));

define('ACCOUNT_TYPE', $account_api->accountManageType);
define('ACCOUNT_TYPE_OFFCIAL', $account_api->accountTypeOffcial);
define('ACCOUNT_TYPE_NAME', $account_api->accountTypeName);
define('ACCOUNT_TYPE_TEMPLATE', $account_api->accountTypeTemplate);
define('ACCOUNT_TYPE_SUPPORT', $account_api->accountTypeSupport);