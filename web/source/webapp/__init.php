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
		$jump_url = $account_api->jumpCheckUrl();
		itoast('', $jump_url);
	} else {
		$check_frame = $account_api->checkFrame();
		define('FRAME', $check_frame);
	}
}
