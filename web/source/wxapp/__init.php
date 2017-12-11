<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$account_api = WeAccount::create(array('type' => ACCOUNT_TYPE_APP_NORMAL));
$check_manange = $account_api->checkIntoManage();

if (!in_array($action, array('display', 'post', 'manage'))) {
	if (is_error($check_manange)) {
		$account_display_url = $account_api->accountDisplayUrl();
		itoast('', $account_display_url);
	}
}

if (($action == 'version' && $do == 'home') || in_array($action, array('payment', 'refund', 'module-link-uniacid', 'entrance-link', 'front-download'))) {
	$frame_type = $account_api->frameType();
	define('FRAME', $frame_type);
}