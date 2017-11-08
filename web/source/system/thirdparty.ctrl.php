<?php
/**
 * 第三方登录配置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('setting');

$dos = array('qq_login', 'wechat_login');
$do = in_array($do, $dos) ? $do : 'qq_login';

$_W['page']['title'] = '第三方登录配置';

if ($_W['isajax'] && $_W['ispost']) {
	$data = array();
	$appid = trim($_GPC['appid']);
	$appsecret = trim($_GPC['appsecret']);

	$authstate = isset($_GPC['authstate']) ? intval($_GPC['authstate']) : ($do == 'qq_login' ? $_W['setting']['qq_platform']['authstate'] : $_W['setting']['wechat_platform']['authstate']);
	$data['appid'] = !empty($appid) ? $appid : ($do == 'qq_login' ? $_W['setting']['qq_platform']['appid'] : $_W['setting']['wechat_platform']['appid']);
	$data['appsecret'] = !empty($appsecret) ? $appsecret : ($do == 'qq_login' ? $_W['setting']['qq_platform']['appsecret'] : $_W['setting']['wechat_platform']['appsecret']);
	$data['authstate'] = !empty($authstate) ? 1 : 0;

	$result = $do == 'qq_login' ? setting_save($data,'qq_platform') : setting_save($data, 'wechat_platform');
	if($result) {
		iajax(0, '修改成功！', referer());
	}else {
		iajax(1, '修改失败！', referer());
	}
}

if ($do == 'qq_login') {
	if(empty($_W['setting']['qq_platform'])) {
		$_W['setting']['qq_platform'] = array(
			'appid' => '',
			'appsecret' => '',
			'authstate' => 0
		);
		setting_save($_W['setting']['qq_platform'], 'qq_platform');
	}
	$url = parse_url($_W['siteroot']);
}

if ($do == 'wechat_login') {
	if(empty($_W['setting']['wechat_platform'])) {
		$_W['setting']['wechat_platform'] = array(
			'appid' => '',
			'appsecret' => '',
			'authstate' => 0
		);
		setting_save($_W['setting']['wechat_platform'], 'wechat_platform');
	}	
}
template('system/thirdparty');