<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 获取包括系统及模块所有的菜单权限
 * 
 */
function system_menu_permission_list($role = '') {
	$system_menu = cache_load('system_frame');
	if(empty($system_menu)) {
		cache_build_frame_menu();
		$system_menu = cache_load('system_frame');
	}
	//根本不同的角色得到不同的菜单权限
	if ($role == ACCOUNT_MANAGE_NAME_OPERATOR) {
		unset($system_menu['appmarket']);
		unset($system_menu['adviertisement']);
		unset($system_menu['system']);
	} if ($role == ACCOUNT_MANAGE_NAME_OPERATOR) {
		unset($system_menu['appmarket']);
		unset($system_menu['adviertisement']);
	}
	return $system_menu;
}