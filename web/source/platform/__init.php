<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$account_api = WeAccount::create();
$check_manange = $account_api->checkIntoManage();

if (!($action == 'material' && $do == 'delete') && empty($_GPC['version_id']) && empty($_GPC['account_type'])) {
	$no_check_account_url = $account_api->noCheckAccountUrl();
	itoast('', $no_check_account_url);
}

if ($action != 'material-post' && $_GPC['uniacid'] != FILE_NO_UNIACID) {
	$check_frame = $account_api->checkFrame();
	define('FRAME', $check_frame);
}
if ($action == 'qr') {
	$platform_qr_permission = permission_check_account_user('platform_qr', false);
	if ($platform_qr_permission ===  false) {
		header("Location: ". url('platform/url2qr'));
	}
}

if ($action == 'url2qr') {
	define('ACTIVE_FRAME_URL', url('platform/qr'));
}