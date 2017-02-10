<?php
/**
 * 公众号欢迎页，统计等信息
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

checkaccount();

load()->model('welcome');

$dos = array('platform', 'ext');
$do = in_array($do, $dos) ? $do : 'platform';

if ($do == 'platform') {
	define('FRAME', 'account');
	if (empty($_W['account']['endtime']) && !empty($_W['account']['endtime']) && $_W['account']['endtime'] < time()) {
		message('公众号已到服务期限，请续费', referer(), 'info');
	}

	uni_update_week_stat();
	$_W['page']['title'] = '平台相关数据';
	//今日昨日指标
	$yesterday = date('Ymd', strtotime('-1 days'));
	$yesterday_stat = pdo_get('stat_fans', array('date' => $yesterday, 'uniacid' => $_W['uniacid']));
	$yesterday_stat['new'] = intval($yesterday_stat['new']);
	$yesterday_stat['cancel'] = intval($yesterday_stat['cancel']);
	$yesterday_stat['cumulate'] = intval($yesterday_stat['cumulate']);
	$today_stat = pdo_get('stat_fans', array('date' => date('Ymd'), 'uniacid' => $_W['uniacid']));
	//今日粉丝详情
	$today_add_num = intval($today_stat['new']);
	$today_cancel_num = intval($today_stat['cancel']);
	$today_jing_num = $today_add_num - $today_cancel_num;
	$today_total_num = intval($today_jing_num) + intval($yesterday_stat['cumulate']);
	if($today_total_num < 0) {
		$today_total_num = 0;
	}

	//公告
	$notices = pdo_getall('article_notice', array('is_display' => 1), array('id', 'title', 'createtime'), '', 'createtime DESC', array(1,5));
	if(!empty($notices)) {
		foreach ($notices as $key => $notice_val) {
			$notices[$key]['url'] = url('article/notice-show/detail', array('id' => $notice_val['id']));
			$notices[$key]['createtime'] = date('Y-m-d', $notice_val['createtime']);
		}
	}

	//最新模块
	$last_modules = welcome_get_last_modules();

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
		$method = 'welcomeDisplay';
		if(method_exists($site, $method)){
			define('FRAME', 'module_welcome');
			$entries = module_entries($modulename, array('menu', 'home', 'profile', 'shortcut', 'cover', 'mine'));
			$site->$method($entries);
			exit;
		}
	}
	define('FRAME', 'account');
	template('home/welcome-ext');
}