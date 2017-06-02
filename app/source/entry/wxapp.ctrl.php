<?php
/**
 * 小程序入口
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');
if (strexists($_SERVER['HTTP_REFERER'], 'https://servicewechat.com/')) {
	$referer_url = parse_url($_SERVER['HTTP_REFERER']);
	list($appid, $version) = explode('/', ltrim($referer_url['path'], '/'));
}
$site = WeUtility::createModuleWxapp($entry['module']);
if(!is_error($site)) {
	$site->appid = $appid;
	$site->version = $version;
	$method = 'doPage' . ucfirst($entry['do']);
	if (!empty($site->token)) {
		if (!$site->checkSign()) {
			message(error(1, '签名错误'), '', 'ajax');
		}
	}
	if (!empty($_W['uniacid'])) {
		$version = trim($_GPC['v']);
		$version_info = pdo_get('wxapp_versions', array('uniacid' => $_W['uniacid'], 'version' => $version), array('id', 'uniacid', 'template', 'modules'));
		if (!empty($version_info)) {
			$connection = iunserializer($version_info['modules'], true);
			$_W['uniacid'] = !empty($connection[$entry['module']]) ? $connection[$entry['module']]['uniacid'] : $version_info['uniacid'];
		} else {
			message('该小程序版本不存在！');
		}
	}
	exit($site->$method());
}
exit();