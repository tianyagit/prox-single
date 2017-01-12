<?php
/**
 * 公众号欢迎页，统计等信息
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

checkaccount();

$dos = array('platform', 'ext');
$do = in_array($do, $dos) ? $do : 'platform';
define('FRAME', 'account');

if ($do == 'platform') {
	
	template('home/welcome');
} elseif ($do == 'ext') {
	$modulename = $_GPC['m'];
	if (!empty($modulename)) {
		$modules = uni_modules();
		$_W['current_module'] = $modules[$modulename];
	}
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