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
		$jump_url = $account_api->jumpCheckUrl();
		itoast('', $jump_url);
	}
}

if (($action == 'version' && $do == 'home') || in_array($action, array('payment', 'refund', 'module-link-uniacid', 'entrance-link', 'front-download'))) {
	$check_frame = $account_api->checkFrame();
	define('FRAME', $check_frame);
}