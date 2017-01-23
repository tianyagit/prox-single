<?php
/**
 * 小程序身份获取
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');
$code = $_GPC['code'];

if (empty($_W['account']['oauth']) || empty($code)) {
	exit('通信错误，请在微信中重新发起请求');
}
$account_api = WeAccount::create();
$oauth = $account_api->getOauthInfo($code);