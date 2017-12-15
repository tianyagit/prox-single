<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

$account_api = WeAccount::create();
if (is_error($account_api)) {
	message($account_api['message'], url('account/display'));
}
$check_manange = $account_api->checkIntoManage();

if (is_error($check_manange)) {
	$account_display_url = $account_api->accountDisplayUrl();
	itoast('', $account_display_url);
} else {
	$account_type = $account_api->accountType();
	define('FRAME', $account_type);
}