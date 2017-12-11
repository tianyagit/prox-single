<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

$account_api = WeAccount::create();
$check_manange = $account_api->checkIntoManage();

if (is_error($check_manange)) {
	$no_check_account_url = $account_api->noCheckAccountUrl();
	itoast('', $no_check_account_url);
} else {
	$check_frame = $account_api->checkFrame();
	define('FRAME', $check_frame);
}