<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');


/**
 * 更新今日访问信息
 * @param string $type 更新类型：web:后端、app:手机端、api:微信api
 * @param string $module_name 模块名
 * @return boolean
 */
function visit_update_today($type, $module_name = '') {
	global $_W;
	$module_name = trim($module_name);
	$type = trim($type);
	if (empty($type) || !in_array($type, array('app', 'web', 'api'))) {
		return false;
	}
	if ($type == 'app' && empty($module_name)) {
		return false;
	}

	$today = date('Ymd');
	$today_exist = pdo_get('stat_visit', array('date' => $today, 'uniacid' => $_W['uniacid'], 'module' => $module_name, 'type' => $type));
	if (empty($today_exist)) {
		$insert_data = array(
			'uniacid' => $_W['uniacid'],
			'module' => $module_name,
			'type' => $type,
			'date' => $today,
			'count' => 1
		);
		pdo_insert('stat_visit', $insert_data);
	} else {
		$data = array('count' => $today_exist['count'] + 1);
		pdo_update('stat_visit' , $data, array('id' => $today_exist['id']));
	}

	return true;
}