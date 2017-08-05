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
	$modules = uni_modules();
	$today = stat_visit_info('today');
	$yesterday = stat_visit_info('yesterday');
	$today_module_api = stat_all_visit_statistics($today);
	$yesterday_module_api = stat_all_visit_statistics($yesterday);
	template('statistics/app-display');
}

if ($do == 'get_account_api') {
}

if ($do == 'get_module_api') {
}