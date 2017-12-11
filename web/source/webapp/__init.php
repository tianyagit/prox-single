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
		$no_check_account_url = $account_api->noCheckAccountUrl();
		itoast('', $no_check_account_url);
	} else {
		$check_frame = $account_api->checkFrame();
		define('FRAME', $check_frame);
	}
}
