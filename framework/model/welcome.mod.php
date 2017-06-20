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

/**
 * 获取公告
 * @return array
*/
function welcome_notices_get() {
	$notices = pdo_getall('article_notice', array('is_display' => 1), array('id', 'title', 'createtime'), '', 'createtime DESC', array(1,15));
	if(!empty($notices)) {
		foreach ($notices as $key => $notice_val) {
			$notices[$key]['url'] = url('article/notice-show/detail', array('id' => $notice_val['id']));
			$notices[$key]['createtime'] = date('Y-m-d', $notice_val['createtime']);
		}
	}
	return $notices;
}