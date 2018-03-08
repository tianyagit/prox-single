<?php
/**
 * xall
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('visit');

$dos = array('showjs', 'systemshowjs');
$do = in_array($do, $dos) ? $do : 'showjs';
/* vstart */
if (IMS_FAMILY == 'v') {
	if ($do == 'showjs') {
		echo '';
		exit;
	}
}
/* vend */
/* xstart */
if (IMS_FAMILY == 'x') {
	if ($do == 'showjs') {
		$type = '';
		$module_name = '';
		if ($_GPC['type'] == 'account') {
			$type = 'web';
			$module_name = 'we7_account';
		}
		visit_update_today($type, $module_name);
	}
}
/* xend */

if ($do == 'systemshowjs') {
	if (user_is_founder()) {
//		return true;
	}
	$type = $_GPC['type'];
	$types = array('account', 'wxapp', 'webapp', 'phoneapp');
	if (in_array($type, $types)) {
		$system_visit_info = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $_W['uid']
		);
		system_visit_update($system_visit_info);
	}
	print_r($_GPC['type']);exit;
}