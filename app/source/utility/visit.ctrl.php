<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/app/source/utility/style.ctrl.php : v b53c8ba00893 : 2014/06/16 12:17:57 : RenChao $
 */
defined('IN_IA') or exit('Access Denied');

load()->model('app');

$dos = array('showjs', 'health');
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
		$module_name = !empty($_GPC['module_name']) ? $_GPC['module_name'] : 'wesite';
		$uniacid = !empty($_GPC['uniacid']) ? intval($_GPC['uniacid']) : 0;
		app_update_today_visit($module_name);
	}
}
/* xend */

// https 站点校验是否能正常访问
if($do == 'health') {
	echo json_encode(error(0, 'success'));
	exit;
}