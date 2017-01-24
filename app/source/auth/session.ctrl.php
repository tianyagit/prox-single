<?php
/**
 * 小程序身份获取
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

$dos = array('openid', 'userinfo');
$do = in_array($do, $dos) ? $do : 'openid';

if ($do == 'openid') {
	$code = $_GPC['code'];
	
	if (empty($_W['account']['oauth']) || empty($code)) {
		exit('通信错误，请在微信中重新发起请求');
	}
	$account_api = WeAccount::create();
	$oauth = $account_api->getOauthInfo($code);
	if (!empty($oauth) && !is_error($oauth)) {
		$_SESSION['openid'] = $oauth['openid'];
		$_SESSION['session_key'] = $oauth['session_key'];
		exit(json_encode($account_api->result(0, '', array('sessionid' => $_W['session_id']))));
	} else {
		exit(json_encode($account_api->result(0, $oauth['message'])));
	}
} elseif ($do == 'userinfo') {
	print_r($_GPC);exit;
}