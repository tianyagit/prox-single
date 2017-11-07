<?php
/**
 * qq登录配置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('setting');

$_W['page']['title'] = '站点管理 - 设置 - 第三方登录';

setting_load('qq_platform');

if ($_W['isajax'] && $_W['ispost']) {
	$data = array();
	$appid = trim($_GPC['appid']);
	$appsecret = trim($_GPC['appsecret']);

	$authstate = isset($_GPC['authstate']) ? intval($_GPC['authstate']) : $_W['setting']['qq_platform']['authstate'];
	$data['appid'] = !empty($appid) ? $appid : $_W['setting']['qq_platform']['appid'];
	$data['appsecret'] = !empty($appsecret) ? $appsecret : $_W['setting']['qq_platform']['appsecret'];
	$data['authstate'] = !empty($authstate) ? 1 : 0;

	$result = setting_save($data,'qq_platform');
	if($result) {
		iajax(0, '修改成功！', '');
	}else {
		iajax(1, '修改失败！', '');
	}
}

if(empty($_W['setting']['qq_platform'])) {
	$_W['setting']['qq_platform'] = array(
		'appid' => '',
		'appsecret' => '',
		'authstate' => 1
	);
	setting_save($_W['setting']['qq_platform'], 'qq_platform');
}
$url = parse_url($_W['siteroot']);
template('system/qq-platform');
