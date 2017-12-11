<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');


$account_api = WeAccount::create();
$check_manange = $account_api->checkIntoManage();

if (is_error($check_manange)) {
	$jump_url = $account_api->jumpCheckUrl();
	itoast('', $jump_url);
}
$check_frame = $account_api->checkFrame();
define('FRAME', $check_frame);