<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($action == 'manage' && $do == 'createview') {
	define('FRAME', 'system');
}
if ($action == 'manage' && $do == 'list') {
	define('FRAME', '');
} else {
	$account_api = WeAccount::create(array('type' => ACCOUNT_TYPE_WEBAPP_NORMAL));
	$check_manange = $account_api->checkIntoManage();
	if (is_error($check_manange)) {
		$account_display_url = $account_api->accountDisplayUrl();
		itoast('', $account_display_url);
	} else {
		$frame_type = $account_api->frameType();
		define('FRAME', $frame_type);
	}
}
