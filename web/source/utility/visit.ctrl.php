<?php
/**
 * xall
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('visit');

$dos = array('showjs');
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
		$type = 'web';
		$module_name = '';
		if ($_GPC['type'] == 'account') {
			$module_name = 'we7_account';
		} elseif ($_GPC['type'] == 'webapp') {
			$module_name = 'we7_webapp';
		}
		visit_update_today($type, $module_name);
	}
}
/* xend */
