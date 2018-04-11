<?php
/**
 * 小程序列表
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('wxapp');
load()->model('account');

if ($do == 'home') {
	$last_uniacid = uni_account_last_switch();
	$url = url('account/display', array('type' => 'wxapp'));
	if (empty($last_uniacid)) {
		itoast('', $url, 'info');
	}
	if (!empty($last_uniacid) && $last_uniacid != $_W['uniacid']) {
		uni_account_switch($last_uniacid, '', WXAPP_TYPE_SIGN);
	}
	$permission = permission_account_user_role($_W['uid'], $last_uniacid);
	if (empty($permission)) {
		itoast('', $url, 'info');
	}
	$last_version = wxapp_fetch($last_uniacid);
	if (!empty($last_version)) {
		$url = url('wxapp/version/home', array('version_id' => $last_version['version']['id']));
	}
	itoast('', $url, 'info');
}