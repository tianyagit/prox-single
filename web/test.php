<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/index.php : v 14b9a4299104 : 2015/09/11 10:44:21 : yanghf $
 */
define('IN_SYS', true);
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';
load()->web('common');
load()->web('template');
load()->func('communication');
load()->model('cache');
load()->model('frame');
load()->model('cloud');
load()->classs('coupon');

$menu = pdo_get('core_menu', array('permission_name' => 'activity_consume_coupon'));
if (empty($menu)) {
	$menu = pdo_get('core_menu', array('title' => '卡券核销'));
	if (empty($menu)) {
		$pid = pdo_getcolumn('core_menu', array('title' => '卡券管理'), 'id');
		if (!empty($pid)) {
			pdo_insert('core_menu', array(
				'pid' => $pid, 
				'title' => '卡券核销', 
				'name' => 'mc', 
				'url' => './index.php?c=activity&a=consume&do=display&',
				'append_title' => '',
				'append_url' => '',
				'displayorder' => '0',
				'type' => 'url',
				'is_display' => '1',
				'is_system' => '1',
				'permission_name' => 'activity_consume_coupon'
			));
			cache_build_frame_menu();
		}
	}
}
print_r($menu);exit;