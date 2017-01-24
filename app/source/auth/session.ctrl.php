<?php
/**
 * 小程序身份获取
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

load()->model('mc');

$dos = array('openid', 'userinfo');
$do = in_array($do, $dos) ? $do : 'openid';

$account_api = WeAccount::create();
if ($do == 'openid') {
	$code = $_GPC['code'];
	if (empty($_W['account']['oauth']) || empty($code)) {
		exit('通信错误，请在微信中重新发起请求');
	}
	
	$oauth = $account_api->getOauthInfo($code);
	if (!empty($oauth) && !is_error($oauth)) {
		$_SESSION['openid'] = $oauth['openid'];
		$_SESSION['session_key'] = $oauth['session_key'];
		//更新Openid到mapping_fans表中
		$fans = mc_fansinfo($oauth['openid']);
		print_r($fans);exit;
		$account_api->result(0, '', array('sessionid' => $_W['session_id']));
	} else {
		$account_api->result(0, $oauth['message']);
	}
} elseif ($do == 'userinfo') {
	$encrypt_data = $_GPC['encryptedData'];
	$iv = $_GPC['iv'];
	if (empty($_SESSION['session_key']) || empty($encrypt_data) || empty($iv)) {
		$account_api->result(1, '请先登录');
	}
	
	$sign = sha1(htmlspecialchars_decode($_GPC['rawData']).$_SESSION['session_key']);
	if ($sign !== $_GPC['signature']) {
		$account_api->result(1, '签名错误');
	}
	
	$userinfo = $account_api->pkcs7Encode($encrypt_data, $iv);
	print_r($userinfo);exit;
}