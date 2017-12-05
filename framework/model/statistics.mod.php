<?php
/**
 * xall
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 获取昨日、最近一周、最近一个月内的访问信息数据
 * @param string $type 类型：today、yesterday、week、month、daterange
 * @param string $module 要统计的模块，为空则默认统计所有模块
 * @return array()
 */
function stat_visit_info($type, $module = '', $daterange = array(), $is_system_stat = false) {
	global $_W;
	$result = array();
	if (empty($type)) {
		return $result;
	}
	$params = array();
	if (empty($is_system_stat)) {
		$params['uniacid'] = $_W['uniacid'];
	}
	if (!empty($module)) {
		$params['module'] = $module;
	}
	switch ($type) {
		case 'today':
			$params['date'] = date('Ymd');
			break;
		case 'yesterday':
			$params['date'] = date('Ymd', strtotime('-1 days'));
			break;
		case 'week':
			$params['date >'] = date('Ymd', strtotime('-7 days'));
			$params['date <='] = date('Ymd');
			break;
		case 'month':
			$params['date >'] = date('Ymd', strtotime('-30 days'));
			$params['date <='] = date('Ymd');
			break;
		case 'daterange':
			if (empty($daterange)) {
				return stat_visit_info('month', $module, array(), $is_system_stat);
			}
			$params['date >='] = date('Ymd', strtotime($daterange['start']));
			$params['date <='] = date('Ymd', strtotime($daterange['end']));
			break;
	}
	$visit_info = pdo_getall('stat_visit', $params, array('uniacid', 'module', 'count', 'date'), '', 'date ASC');
	if (!empty($visit_info)) {
		$result = $visit_info;
	}
	return $result;
}

/**
 * 根据公众号划分，获取昨日、最近一周、最近一个月内的访问信息数据
 * @param string $type 类型：today、yesterday、week、month、daterange
 * @param string $module 要统计的模块，为空则默认统计所有模块
 * @return array()
 */
function stat_visit_info_byuniacid($type, $module = '', $daterange = array(), $is_system_stat = false) {
	$result = array();
	$visit_info = stat_visit_info($type, $module, $daterange, $is_system_stat);
	if (empty($visit_info)) {
		return $result;
	}
	foreach ($visit_info as $info) {
		if ($is_system_stat) {
			if (empty($info['uniacid'])) {
				continue;
			}
			if ($result[$info['uniacid']]['uniacid'] == $info['uniacid']) {
				$result[$info['uniacid']]['count'] += $info['count'];
			} else {
				$result[$info['uniacid']] = $info;
			}
		} else {
			if (empty($info['module'])) {
				continue;
			}
			if ($result[$info['module']]['module'] == $info['module']) {
				$result[$info['module']]['count'] += $info['count'];
			} else {
				$result[$info['module']] = $info;
			}
		}
	}
	return $result;
}

/**
 * 根据日期划分，获取昨日、最近一周、最近一个月内的访问信息数据
 * @param string $type 类型：today、yesterday、week、month、daterange
 * @param string $module 要统计的模块，为空则默认统计所有模块
 * @return array()
 */
function stat_visit_info_bydate($type, $module = '', $daterange = array(), $is_system_stat = false) {
	$result = array();
	$visit_info = stat_visit_info($type, $module, $daterange, $is_system_stat);
	if (empty($visit_info)) {
		return $result;
	}
	foreach ($visit_info as $info) {
		if (empty($info['uniacid']) || empty($info['date'])) {
			continue;
		}
		if ($result[$info['date']]['date'] == $info['date']) {
			$result[$info['date']]['count'] += $info['count'];
		} else {
			unset($info['module'], $info['uniacid']);
			$result[$info['date']] = $info;
		}
	}
	return $result;
}


/**
 * 统计公众号整体访问信息
 * @param string $type 'current_account'当前公众号；'all_account' 所有公众号
 * @data array() 要统计的访问信息
 * @return array()
 */
function stat_all_visit_statistics($type, $data) {
	if ($type == 'current_account') {
		$modules = stat_modules_except_system();
		$count = count($modules);
	} elseif ($type == 'all_account') {
		$account_table = table('account');
		$account_table->searchWithType(array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH));
		$account_table->accountRankOrder();
		$account_list = $account_table->searchAccountList();
		$count = count($account_list);
	}
	$result = array(
		'visit_sum' => 0,
		'visit_highest' => 0,
		'visit_avg' => 0
	);
	if (empty($data)) {
		return $result;
	}
	foreach ($data as $val) {
		$result['visit_sum'] += $val['count'];
		if ($result['visit_highest'] < $val['count']) {
			$result['visit_highest'] = $val['count'];
		}

	}
	$result['visit_avg'] = round($result['visit_sum'] / $count);
	return $result;
}

/**
 * 获取当前公号下除系统模块外的所有安装模块及模块信息
 * @return array()
 */
function stat_modules_except_system() {
	$modules = uni_modules();
	if (!empty($modules)) {
		foreach ($modules as $key => $module) {
			if (!empty($module['issystem'])) {
				unset($modules[$key]);
			}
		}
	}
	return $modules;
}