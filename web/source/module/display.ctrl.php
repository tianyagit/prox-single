<?php 
/**
 * 应用列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('wxapp');

$dos = array('display', 'have_permission_uniacids');
$do = in_array($do, $dos) ? $do : 'display';

if ($do == 'display') {
	$user_module = user_modules($_W['uid']);
	foreach ($user_module as $key => $module_value) {
		if (!empty($module_value['issystem'])) {
			unset($user_module[$key]);
		}
	}
	template('module/display');
}

if ($do == 'have_permission_uniacids') {
	$module_name = trim($_GPC['module_name']);
	$accounts_list = module_link_uniacid_fetch($_W['uid'], $module_name);
	iajax(0, $accounts_list);
}