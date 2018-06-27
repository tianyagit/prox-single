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
	$params = array('date' => $today, 'uniacid' => $_W['uniacid'], 'module' => $module_name, 'type' => $type);
	$today_exist = table('statistics')->visitList($params, 'one');
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

/**
 * 访问uniacid或者module的记录或者自己设置置顶的功能($displayorder=true)
 * @param $system_stat_visit
 * @param bool $displayorder
 * @return bool
 */
function visit_system_update($system_stat_visit, $displayorder = false) {
	global $_W;
	load()->model('user');
	if (user_is_founder($_W['uid'])) {
		return true;
	}

	if (empty($system_stat_visit['uniacid']) && empty($system_stat_visit['modulename'])) {
		return true;
	}
	if (empty($system_stat_visit['uid'])) {
		return true;
	}

	$condition['uid'] = $_W['uid'];
	if (!empty($system_stat_visit['uniacid'])) {
		$is_exist = table('users')->userIsHasUniacid($_W['uid'], $system_stat_visit['uniacid']);
		if (empty($is_exist)) {
			return true;
		}
		$condition['uniacid'] = $system_stat_visit['uniacid'];
	}

	if (!empty($system_stat_visit['modulename'])) {
		$user_modules = user_modules($_W['uid']);
		$modules = !empty($user_modules) ? array_keys($user_modules) : array();
		if (empty($modules) || !in_array($system_stat_visit['modulename'], $modules)) {
			return true;
		}
		$condition['modulename'] = $system_stat_visit['modulename'];
	}
	$system_stat_info = pdo_get('system_stat_visit', $condition);

	if (empty($system_stat_info['createtime'])) {
		$system_stat_visit['createtime'] = TIMESTAMP;
	}

	if (empty($system_stat_visit['updatetime'])) {
		$system_stat_visit['updatetime'] = TIMESTAMP;
	}

	if (!empty($displayorder)) {
		$system_stat_max_order = pdo_fetchcolumn("SELECT MAX(displayorder) FROM " . tablename('system_stat_visit') . " WHERE uid = :uid", array(':uid' => $_W['uid']));
		$system_stat_visit['displayorder'] = ++$system_stat_max_order;
	}

	if (empty($system_stat_info)) {
		pdo_insert('system_stat_visit', $system_stat_visit);
	} else {
		$system_stat_visit['updatetime'] = TIMESTAMP;
		pdo_update('system_stat_visit', $system_stat_visit, array('id' => $system_stat_info['id']));
	}
	return true;
}