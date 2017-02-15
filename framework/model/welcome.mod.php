<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

/**
	* 从商城获取最新应用
	* @return 	array
*/
function welcome_get_last_modules() {
	load()->classs('cloudapi');

	$api = new CloudApi();
	$last_modules = $api->get('store', 'app_fresh');
	return $last_modules;
}