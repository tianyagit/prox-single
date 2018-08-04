<?php
/**
 * 小程序欢迎页
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('miniprogram');
load()->model('welcome');

$dos = array('home');
$do = in_array($do, $dos) ? $do : 'home';
$_W['page']['title'] = '小程序 - 管理';

$version_id = intval($_GPC['version_id']);
$wxapp_info = miniprogram_fetch($_W['uniacid']);
if (!empty($version_id)) {
	$version_info = miniprogram_version($version_id);
}

if ($do == 'home') {
	$notices = welcome_notices_get();
	template('miniprogram/version-home');
}