<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (strexists($_W['siteurl'], 'c=profile&a=module&do=setting')) {
	$other_params = parse_url($_W['siteurl'], PHP_URL_QUERY);
	$other_params = str_replace('c=profile&a=module&do=setting', '', $other_params);
	itoast('', url('module/manage-account/setting'). $other_params, 'info');
}

$account_api = WeAccount::create();
$check_manange = $account_api->checkIntoManage();

if (is_error($check_manange)) {
	$no_check_account_url = $account_api->noCheckAccountUrl();
	itoast('', $no_check_account_url);
}
$check_frame = $account_api->checkFrame();
define('FRAME', $check_frame);