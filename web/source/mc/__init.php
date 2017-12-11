<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

$account_api = WeAccount::create();
$check_manange = $account_api->checkIntoManage();

if (is_error($check_manange)) {
	$account_display_url = $account_api->accountDisplayUrl();
	itoast('', $account_display_url);
} else {
	$frame_type = $account_api->frameType();
	define('FRAME', $frame_type);
}