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

if ($do == 'display') {
	$today = stat_visit_info_byuniacid('today');
	$yesterday = stat_visit_info_byuniacid('yesterday');
	$today_module_api = stat_all_visit_statistics('current_account', $today);
	$yesterday_module_api = stat_all_visit_statistics('current_account', $yesterday);
	template('statistics/display');
}

if ($do == 'get_module_api') {
	$modules = array();
	$data = array();
	$modules_info = stat_modules_except_system();
	array_unshift($modules_info, array('name' => 'wesite', 'title' => '微站'));
	foreach ($modules_info as $info) {
		$modules[] = mb_substr($info['title'], 0, 5, 'utf-8');
	}

	$support_type = array(
		'time' => array('today', 'week', 'month', 'daterange'),
		'divide' => array('bydate', 'byuniacid'),
	);
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
	if ($divide_type == 'bydate') {
		$result = stat_visit_info_bydate($type, '', $daterange);
	}
	if ($divide_type == 'byuniacid') {
		$result = stat_visit_info_byuniacid($type, '', $daterange);
	}
	if (empty($result)) {
		foreach ($modules_info as $module) {
			$data[] = 0;
		}
		iajax(0, array('data_x' => $modules, 'data_y' => $data));
	}
	if ($divide_type == 'bydate') {
		foreach ($result as $val) {
			$data_x[] = $val['date'];
			$data_y[] = $val['count'];
		}
		iajax(0, array('data_x' => $data_x, 'data_y' => $data_y));
	}
	if ($divide_type == 'byuniacid') {
		foreach ($modules_info as $module) {
			$have_count = false;
			foreach ($result as $val) {
				if ($module['name'] == $val['module']) {
					$data[] = $val['count'];
					$have_count = true;
				}
			}
			if (empty($have_count)) {
				$data[] = 0;
			}
		}
		iajax(0, array('data_x' => $modules, 'data_y' => $data));
	}
}