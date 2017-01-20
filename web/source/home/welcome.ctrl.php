<?php
/**
 * 公众号欢迎页，统计等信息
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

checkaccount();

$dos = array('platform', 'ext');
$do = in_array($do, $dos) ? $do : 'platform';

if ($do == 'platform') {
	define('FRAME', 'account');
	if (empty($_W['account']['endtime']) && !empty($_W['account']['endtime']) && $_W['account']['endtime'] < time()) {
		message('公众号已到服务期限，请续费', referer(), 'info');
	}

	uni_update_week_stat();
	$_W['page']['title'] = '平台相关数据';
	$yesterday = date('Ymd', strtotime('-1 days'));
	$yesterday_stat = pdo_get('stat_fans', array('date' => $yesterday, 'uniacid' => $_W['uniacid']));
	$today_stat = pdo_get('stat_fans', array('date' => date('Ymd'), 'uniacid' => $_W['uniacid']));
	//今日粉丝详情
	$today_add_num = intval($today_stat['new']);
	$today_cancel_num = intval($today_stat['cancel']);
	$today_jing_num = $today_add_num - $today_cancel_num;
	$today_total_num = intval($today_jing_num) + intval($yesterday_stat['cumulate']);
	if($today_total_num < 0) {
		$today_total_num = 0;
	}
	
	template('home/welcome');
} elseif ($do == 'ext') {
	$modulename = $_GPC['m'];
	if (!empty($modulename)) {
		$modules = uni_modules();
		$_W['current_module'] = $modules[$modulename];
	}
	//如果模块存在自定义封面，则调用
	$site = WeUtility::createModule($modulename);
	if (!is_error($site)) {
		define('FRAME', 'module_welcome');
		$method = 'welcomeDisplay';
		if(method_exists($site, $method)){
			$entries = module_entries($modulename, array('menu', 'home', 'profile', 'shortcut', 'cover', 'mine'));
			$site->$method($entries);
			exit;
		}
	}
	define('FRAME', 'account');
	$frames = buildframes('account');
	foreach ($frames['section'] as $secion) {
		foreach ($secion['menu'] as $menu) {
			if (!empty($menu['url'])) {
				header('Location: ' . $_W['siteroot'] . 'web/' . $menu['url']);
				exit;
			}
		}
	}
	template('home/welcome-ext');
}