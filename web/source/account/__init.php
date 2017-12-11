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

$account_param = WeAccount::createType($_GPC['account_type']);
define('ACCOUNT_TYPE', $account_param['account_type']);
define('ACCOUNT_TYPE_NAME', $account_param['account_type_name']);
define('ACCOUNT_TYPE_TEMPLATE', $account_param['account_ype_template']);
define('ACCOUNT_TYPE_SUPPORT', $account_param['account_type_support']);