<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
if($action != 'display') {
	define('FRAME', 'system');
}
if ($controller == 'account' && $action == 'manage') {
	if ($_GPC['account_type'] == '4') {
		define('ACTIVE_FRAME_URL', url('account/manage/display', array('account_type' => '4')));
	} 
}

if ($_GPC['account_type'] == ACCOUNT_TYPE_APP_NORMAL) {
	define('ACCOUNT_TYPE', ACCOUNT_TYPE_APP_NORMAL);
	define('ACCOUNT_TYPE_NAME', '小程序');
	define('ACCOUNT_TYPE_TABLENAME', 'account_wxapp');
	define('ACCOUNT_TYPE_TEMPLATE', '-wxapp');
} else {
	define('ACCOUNT_TYPE', '');
	define('ACCOUNT_TYPE_NAME', '公众号');
	define('ACCOUNT_TYPE_TABLENAME', 'account_wechats');
	define('ACCOUNT_TYPE_TEMPLATE', '');
}