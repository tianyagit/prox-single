<?php
/**
 * app端访问统计
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('statistics');

$dos = array('display', 'get_account_api', 'get_module_api');
$do = in_array($do, $dos) ? $do : 'display';
$support_type = array(
	'time' => array('today', 'week', 'month', 'daterange'),
	'divide' => array('bysum', 'byavg', 'byhighest'),
);

if ($do == 'display') {
	$today = stat_visit_info_byuniacid('today');
	$yesterday = stat_visit_info_byuniacid('yesterday');
	$today_module_api = stat_all_visit_statistics('current_account', $today);
	$yesterday_module_api = stat_all_visit_statistics('current_account', $yesterday);
	template('statistics/display');
}

if ($do == 'get_account_api') {
	$data = array();
	$type = trim($_GPC['time_type']);
	$divide_type = trim($_GPC['divide_type']);
	if (!in_array($type, $support_type['time']) || !in_array($divide_type, $support_type['divide'])) {
		iajax(-1, '参数错误！');
	}
	$daterange = array();
	if (!empty($_GPC['daterange'])) {
		$daterange = array(
			'start' => date('Ymd', strtotime($_GPC['daterange']['startDate'])),
			'end' => date('Ymd', strtotime($_GPC['daterange']['endDate'])),
		);
	}
	$result = stat_visit_info_byuniacid($type, '', $daterange);
	if (empty($result)) {
		if ($type == 'today') {
			$data_x = date('Ymd');
		}
		if ($type == 'week') {
			$data_x = stat_date_range(date('Ymd', strtotime('-7 days')), date('Ymd'));
		}
		if ($type == 'month') {
			$data_x = stat_date_range(date('Ymd', strtotime('-30 days')), date('Ymd'));
		}
		if ($type == 'daterange') {
			$data_x = stat_date_range($daterange['start'], $daterange['end']);
		}
		foreach ($data_x as $val) {
			$data_y[] = 0;
		}
		iajax(0, array('data_x' => $data_x, 'data_y' => $data_y));
	}
	foreach ($result as $val) {
		$data_x[] = $val['date'];
		if ($divide_type == 'bysum') {
			$data_y[] = $val['count'];
		} elseif ($divide_type == 'byavg') {
			$data_y[] = $val['avg'];
		} elseif ($divide_type == 'byhighest') {
			$data_y[] = $val['highest'];
		}
	}
	iajax(0, array('data_x' => $data_x, 'data_y' => $data_y));
}

if ($do == 'get_module_api') {
	$modules = array();
	$data = array();
	$modules_info = stat_modules_except_system();
	array_unshift($modules_info, array('name' => 'wesite', 'title' => '微站'));
	foreach ($modules_info as $info) {
		$modules[] = mb_substr($info['title'], 0, 5, 'utf-8');
	}

	$type = trim($_GPC['time_type']);
	$divide_type = trim($_GPC['divide_type']);
	if (!in_array($type, $support_type['time']) || !in_array($divide_type, $support_type['divide'])) {
		iajax(-1, '参数错误！');
	}
	$daterange = array();
	if (!empty($_GPC['daterange'])) {
		$daterange = array(
			'start' => date('Ymd', strtotime($_GPC['daterange']['startDate'])),
			'end' => date('Ymd', strtotime($_GPC['daterange']['endDate'])),
		);
	}

	$result = stat_visit_info_byuniacid($type, '', $daterange);
	if (empty($result)) {
		foreach ($modules_info as $module) {
			$data[] = 0;
		}
		iajax(0, array('data_x' => $data, 'data_y' => $modules));
	}
	foreach ($modules_info as $module) {
		$have_count = false;
		foreach ($result as $val) {
			if ($module['name'] == $val['module']) {
				if ($divide_type == 'bysum') {
					$data[] = $val['count'];
				} elseif ($divide_type == 'byavg') {
					$data[] = $val['avg'];
				} elseif ($divide_type == 'byhighest') {
					$data[] = $val['highest'];
				}
				$have_count = true;
			}
		}
		if (empty($have_count)) {
			$data[] = 0;
		}
	}
	iajax(0, array('data_x' => $data, 'data_y' => $modules));
}