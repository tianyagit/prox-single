<?php
/**
 * 欢迎页，统计等信息
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($do == 'platform' || $do == 'ext') {
	checkaccount();
}

load()->model('welcome');
load()->model('cloud');
load()->func('communication');
load()->func('db');
load()->model('extension');
load()->model('module');
load()->model('system');

$dos = array('platform', 'system', 'ext', 'get_fans_kpi', 'get_last_modules', 'get_module_statistics');
$do = in_array($do, $dos) ? $do : 'platform';

if ($do == 'platform') {
	$last_uniacid = uni_account_last_switch();
	if (empty($last_uniacid)) {
		itoast('', url('account/display'), 'info');
	}
	if (!empty($last_uniacid) && $last_uniacid != $_W['uniacid']) {
		uni_account_switch($last_uniacid,  url('home/welcome'));
	}
	define('FRAME', 'account');

	if (empty($_W['account']['endtime']) && !empty($_W['account']['endtime']) && $_W['account']['endtime'] < time()) {
		itoast('公众号已到服务期限，请联系管理员并续费', url('account/manage'), 'info');
	}
	//公告
	$notices = welcome_notices_get();

	template('home/welcome');
} elseif ($do == 'system') {
	define('FRAME', 'system');
	$_W['page']['title'] = '欢迎页 - 系统管理';
	$cloud = cloud_prepare();
	if (is_error($cloud)) {
		itoast($cloud['message'], url('cloud/profile'), 'error');
	}
	if(!$_W['isfounder']){
		header('Location: ' . url('account/manage', array('account_type' => 1)), true);
		exit;
	}
	
	//数据备份信息
	$reduction = system_database_backup();
	//数据库最后一次备份时间
	$max_backup_time = time();
	if (!empty($reduction)) {
		$backups = array_values($reduction);
		$max_backup_time = $backups[0]['time'];
		foreach ($backups as $key => $backup) {
			if ($backup['time'] <= $max_backup_time) {
				continue;
			}
			$max_backup_time = $backup['time'];
		}
	}
	$last_backup_time = $max_backup_time;
	$backup_days = floor((time() - $last_backup_time) / (3600 * 24));
	template('home/welcome-system');
} elseif ($do == 'ext') {
	$modulename = $_GPC['m'];
	if (!empty($modulename)) {
		$_W['current_module'] = module_fetch($modulename);
	}
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
	define('IN_MODULE', $modulename);
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
} elseif ($do == 'get_fans_kpi') {
	uni_update_week_stat();
	//今日昨日指标
	$yesterday = date('Ymd', strtotime('-1 days'));
	$yesterday_stat = pdo_get('stat_fans', array('date' => $yesterday, 'uniacid' => $_W['uniacid']));
	$yesterday_stat['new'] = intval($yesterday_stat['new']);
	$yesterday_stat['cancel'] = intval($yesterday_stat['cancel']);
	$yesterday_stat['jing_num'] = intval($yesterday_stat['new']) - intval($yesterday_stat['cancel']);
	$yesterday_stat['cumulate'] = intval($yesterday_stat['cumulate']);
	//今日粉丝详情
	$today_stat = pdo_get('stat_fans', array('date' => date('Ymd'), 'uniacid' => $_W['uniacid']));
	$today_stat['new'] = intval($today_stat['new']);
	$today_stat['cancel'] = intval($today_stat['cancel']);
	$today_stat['jing_num'] = $today_stat['new'] - $today_stat['cancel'];
	$today_stat['cumulate'] = intval($today_stat['jing_num']) + $yesterday_stat['cumulate'];
	if($today_stat['cumulate'] < 0) {
		$today_stat['cumulate'] = 0;
	}
	iajax(0, array('yesterday' => $yesterday_stat, 'today' => $today_stat), '');
} elseif ($do == 'get_last_modules') {
	//最新模块
	$last_modules = welcome_get_last_modules();
	if (is_error($last_modules)) {
		iajax(1, $last_modules['message'], '');
	} else {
		iajax(0, $last_modules, '');
	}
} elseif ($do = 'get_module_statistics') {
	//系统更新信息
	$upgrade = cloud_build();
	if (is_error($upgrade) || empty($upgrade['upgrade'])) {
		$upgrade = array();
	}
	//未安装应用
	$uninstall_modules = module_get_all_unistalled('uninstalled');
	$account_uninstall_modules_nums = $uninstall_modules['app_count'];
	$wxapp_uninstall_modules_nums = $uninstall_modules['wxapp_count'];
	
	$wxapp_modules = $account_modules = $module_list = user_modules($_W['uid']);
	if (!empty($module_list)) {
		foreach ($module_list as $key => &$module) {
			if ((!empty($module['issystem']) && $module['name'] != 'we7_coupon')) {
				unset($wxapp_modules[$key]);
				unset($account_modules[$key]);
			}
			if ($module['wxapp_support'] != 2) {
				unset($wxapp_modules[$key]);
			}
			if ($module['app_support'] != 2) {
				unset($account_modules[$key]);
			}
		}
		unset($module);
		unset($module_list);
	}
	//应用总数
	$account_modules_total = count($account_modules) + $account_uninstall_modules_nums;
	$wxapp_modules_total = count($wxapp_modules) + $wxapp_uninstall_modules_nums;
	
	//可升级应用
	$account_upgrade_modules = module_filter_upgrade(array_keys($account_modules));
	$wxapp_upgrade_modules = module_filter_upgrade(array_keys($wxapp_modules));
	$account_upgrade_module_nums = count($account_upgrade_modules);
	$wxapp_upgrade_module_nums = count($wxapp_upgrade_modules);
	$account_upgrade_module_list = array_slice($account_upgrade_modules, 0, 4);
	$wxapp_upgrade_module_list = array_slice($wxapp_upgrade_modules, 0, 4);
	foreach ($wxapp_upgrade_module_list as $key => &$module) {
		$module_fetch = module_fetch($key);
		$module['logo'] = $module_fetch['logo'];
		$module['link'] = url('system/module/module_detail', array('name' => $module['name'], 'show' =>'upgrade', 'account_type' => 1));
	}
	unset($module);
	foreach ($account_upgrade_module_list as $key => &$module) {
		$module_fetch = module_fetch($key);
		$module['logo'] = $module_fetch['logo'];
		$module['link'] = url('system/module/module_detail', array('name' => $module['name'], 'show' =>'upgrade', 'account_type' => 4));
	}
	unset($module);
	$account_upgrade_module_show = 0;
	if (count($account_upgrade_module_list) > 0) {
		$account_upgrade_module_show = 1;
	}
	$wxapp_upgrade_module_show = 0;
	if (count($wxapp_upgrade_module_list) > 0) {
		$wxapp_upgrade_module_show = 1;
	}
	$module_statistics = array(
		'account_uninstall_modules_nums' => $account_uninstall_modules_nums,
		'wxapp_uninstall_modules_nums' => $wxapp_uninstall_modules_nums,
		'account_upgrade_module_nums' => $account_upgrade_module_nums,
		'account_modules_total' => $account_modules_total,
		'wxapp_modules_total' => $wxapp_modules_total,
		'wxapp_upgrade_module_nums' => $wxapp_upgrade_module_nums,
		'wxapp_upgrade_module_list' => $wxapp_upgrade_module_list,
		'account_upgrade_module_list' => $account_upgrade_module_list,
		'account_upgrade_module_show' => $account_upgrade_module_show,
		'wxapp_upgrade_module_show' => $wxapp_upgrade_module_show
	);
	iajax(0, $module_statistics, '');
}