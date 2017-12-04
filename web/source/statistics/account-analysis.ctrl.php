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
	$today = stat_visit_info('today', '', array(), true);
	$yesterday = stat_visit_info('yesterday', '', array(), true);
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

	$support_type = array('today', 'week', 'month', 'daterange');
	$type = trim($_GPC['type']);
	if (!in_array($type, $support_type)) {
		iajax(-1, '参数错误！');
	}
	$daterange = array();
	if (!empty($_GPC['daterange'])) {
		$daterange = array(
			'start' => date('Ymd', strtotime($_GPC['daterange']['startDate'])),
			'end' => date('Ymd', strtotime($_GPC['daterange']['endDate'])),
		);
	}

	$result = stat_visit_info($type, '', $daterange, true);
	if (empty($result)) {
		foreach ($account_list as $account) {
			$data[] = 0;
		}
	} else {
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
	}

	iajax(0, array('account' => $accounts, 'data' => $data));
}

template('statistics/account-analysis');