<?php
/**
 * 系统管理欢迎页
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('cloud');
load()->func('communication');
load()->func('db');
load()->model('extension');
load()->model('module');
load()->model('user');
$r = cloud_prepare();
if (is_error($r)) {
	itoast($r['message'], url('cloud/profile'), 'error');
}
$_W['page']['title'] = '欢迎页 - 系统管理';
$dos = array('display','upgrade');
$do = in_array($do, $dos) ? $do : 'display';
if ($do == 'display') {
	//数据备份信息
	$path = IA_ROOT . '/data/backup/';
	$reduction = database_reduction ($path);
	if (!empty($reduction)) {
		$last_reduction = array_pop($reduction);
	}
	$last_backup_time = !empty($last_reduction['time']) ? $last_reduction['time'] : time();
	$backup_days = floor((time() - $last_backup_time) / (3600 * 24));
	//系统更新信息
	$upgrade = cloud_build();
	//未安装应用
	$uninstall_modules = module_get_all_unistalled('uninstalled');
	$account_uninstall_modules_nums = $uninstall_modules['app_count'];
	$wxapp_uninstall_modules_nums = $uninstall_modules['wxapp_count'];
	
	$wxapp_modules = $account_modules = $module_list = user_modules($_W['uid']);
	if (!empty($module_list)) {
		foreach ($module_list as $key => &$module) {
			if ((!empty($module['issystem']) && $module['name'] != 'we7_coupon')) {
				unset($wxapp_modules[$key]);
				unset($account_modules[$key]);
			}
			if ($module['wxapp_support'] != 2) {
				unset($wxapp_modules[$key]);
			}
			if ($module['app_support'] != 2) {
				unset($account_modules[$key]);
			}
		}
		unset($module);
		unset($module_list);
	} 
	//应用总数
	$account_modules_total = count($account_modules) + $account_uninstall_modules_nums;
	$wxapp_modules_total = count($wxapp_modules) + $wxapp_uninstall_modules_nums;
	//可升级应用
	$account_upgrade_modules = module_filter_upgrade(array_keys($account_modules));
	$wxapp_upgrade_modules = module_filter_upgrade(array_keys($wxapp_modules));
	$account_upgrade_module_nums = count($account_upgrade_modules);
	$wxapp_upgrade_module_nums = count($wxapp_upgrade_modules);
	$account_upgrade_module_list = array_slice($account_upgrade_modules, 0, 4);
	foreach ($wxapp_upgrade_module_list as $key => &$module) {
		$module_fetch = module_fetch($key);
		$module['logo'] = $module_fetch['logo'];
	}
	unset($module);
	foreach ($account_upgrade_module_list as $key => &$module) {
		$module_fetch = module_fetch($key);
		$module['logo'] = $module_fetch['logo'];
	}
	unset($module);
	$wxapp_upgrade_module_list = array_slice($wxapp_upgrade_modules, 0, 4);
	template('system/welcome');
}