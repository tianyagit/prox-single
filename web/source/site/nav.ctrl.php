<?php
/**
 * 微站导航管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');

$dos = array('home', 'profile');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'home';

uni_user_permission_check('platform_nav_' . $do, true, 'nav');
$modulename = $_GPC['m'];

//首页导航
if ($do == 'home' || $do == 'profile') {
	$modules = uni_modules();
	$bindings = array();
	
	if(!empty($modulename)) {
		$modulenames = array($modulename);
	} else {
		$modulenames = array_keys($modules);
	}
	
	foreach($modulenames as $modulename) {
		$entries = module_entries($modulename, array($do));
		if(!empty($entries[$do])) {
			$bindings[$modulename] = $entries[$do];
		}
	}
	$entries = array();
	if(!empty($bindings)) {
		foreach($bindings as $modulename => $group) {
			foreach($group as $bind) {
				$entries[] = array('module' => $modulename, 'from' => $bind['from'], 'title' => $bind['title'], 'url' => $bind['url']);
			}
		}
	}
	template('site/nav');
}