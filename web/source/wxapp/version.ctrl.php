<?php
/**
 * 小程序欢迎页
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('miniapp');
load()->model('welcome');

$dos = array('home', 'get_daily_visittrend');
$do = in_array($do, $dos) ? $do : 'home';
$_W['page']['title'] = '小程序 - 管理';

$version_id = intval($_GPC['version_id']);
$wxapp_info = miniapp_fetch($_W['uniacid']);
if (!empty($version_id)) {
	$version_info = miniapp_version($version_id);
}

if ($do == 'home') {
	if ($version_info['design_method'] == WXAPP_TEMPLATE) {
		$version_site_info = miniapp_site_info($version_info['multiid']);
	}
	$role = permission_account_user_role($_W['uid'], $wxapp_info['uniacid']);

	$notices = welcome_notices_get();
	template('wxapp/version-home');
}

if ($do == 'get_daily_visittrend') {
	miniapp_update_daily_visittrend();
	//昨日指标
	$yesterday = date('Ymd', strtotime('-1 days'));
	$yesterday_stat = pdo_get('wxapp_general_analysis', array('uniacid' => $_W['uniacid'], 'type' => '2', 'ref_date' => $yesterday));
	if (empty($yesterday_stat)) {
		$yesterday_stat = array('session_cnt' => 0, 'visit_pv' => 0, 'visit_uv' => 0, 'visit_uv_new' => 0);
	}
	iajax(0, array('yesterday' => $yesterday_stat), '');
}

