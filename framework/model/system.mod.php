<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 获取包括系统及模块所有的菜单权限
 * 
 */
function system_menu_permission_list() {
	cache_build_frame_menu();
	$system_menu = cache_load('system_frame');
	if(empty($system_menu)) {
		cache_build_frame_menu();
		$system_menu = cache_load('system_frame');
	}
	return $system_menu;
}