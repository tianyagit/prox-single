<?php
/**
 * app端所有公众号访问统计
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('statistics');

$dos = array('display', 'get_account_api');
$do = in_array($do, $dos) ? $do : 'display';

if ($do == 'display') {
	$today = stat_visit_info_byuniacid('today', '', array(), true);
	$yesterday = stat_visit_info_byuniacid('yesterday', '', array(), true);
	$today_module_api = stat_all_visit_statistics('all_account', $today);
	$yesterday_module_api = stat_all_visit_statistics('all_account', $yesterday);
}

if ($do == 'get_account_api') {
	$accounts = array();
	$data = array();
	$account_table = table('account');
	$account_table->searchWithType(array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH));
	$account_table->accountRankOrder();
	$account_list = $account_table->searchAccountList();
	foreach ($account_list as $key => $account) {
		$account_list[$key] = uni_fetch($account['uniacid']);
		$accounts[] = mb_substr($account_list[$key]['name'], 0, 5, 'utf-8');
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
		$result = stat_visit_info_bydate($type, '', $daterange, true);
	}
	if ($divide_type == 'byuniacid') {
		$result = stat_visit_info_byuniacid($type, '', $daterange, true);
	}

	if (empty($result)) {
		foreach ($account_list as $account) {
			$data[] = 0;
		}
		iajax(0, array('data_x' => $accounts, 'data_y' => $data));
	}
	if ($divide_type == 'bydate') {
		foreach ($result as $val) {
			$data_x[] = $val['date'];
			$data_y[] = $val['count'];
		}
		iajax(0, array('data_x' => $data_x, 'data_y' => $data_y));
	}
	if ($divide_type == 'byuniacid') {
		foreach ($account_list as $account) {
			$have_count = false;
			foreach ($result as $val) {
				if ($account['uniacid'] == $val['uniacid']) {
					$data[] = $val['count'];
					$have_count = true;
				}
			}
			if (empty($have_count)) {
				$data[] = 0;
			}
		}
		iajax(0, array('data_x' => $accounts, 'data_y' => $data));
	}
}

template('statistics/account-analysis');