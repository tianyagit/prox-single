<?php
/**
 * 小程序列表
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('miniprogram');

$dos = array('version_display');
$do = in_array($do, $dos) ? $do : 'version_display';
// echo "<pre>";
// print_r($_W['account']);
// echo "</pre>";
// exit;
if ($do == 'version_display') {
	$version_list = miniprogram_version_all($_W['uniacid']);
	template('miniprogram/version-display');
}