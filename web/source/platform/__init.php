<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (empty($_W['uniacid'])) {
	message('请先选择您要操作的公众号', url('account/display'), 'error');
}

if ($action != 'material-post') {
	define('FRAME', 'account');
}
if ($action == 'qr') {
	$platform_qr_permission =  uni_user_permission_check('platform_qr', false);
	if ($platform_qr_permission ===  false) {
		header("Location: ". url('platform/url2qr'));
	}
}

if ($action == 'url2qr') {
	define('ACTIVE_FRAME_URL', url('platform/qr'));
}