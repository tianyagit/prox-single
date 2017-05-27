<?php
/**
 * 支付参数配置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('account');

if ($do == 'get_setting') {
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$pay_setting = $setting['payment'];
	if(!is_array($pay_setting) || empty($pay_setting)) {
		$pay_setting = array(
				'wechat' => array('switch' => false, 'account' => '', 'signkey' => '', 'partner' => '', 'key' => '', 'version' => '', 'mchid' => '', 'apikey' => '', 'service' => '', 'borrow' => '', 'sub_mch_id' => '')
		);
	}
	iajax(0, $pay_setting, '');
}

if ($do = 'display') {
	$proxy_wechatpay_account = account_wechatpay_proxy();
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$pay_setting = $setting['payment'];

	if (empty($pay_setting['wechat'])) {
		$pay_setting['wechat'] = array('switch' => false, 'account' => '', 'signkey' => '', 'partner' => '', 'key' => '', 'version' => '', 'mchid' => '', 'apikey' => '', 'service' => '', 'borrow' => '', 'sub_mch_id' => '');
	}

	$accounts = array();
	$accounts[$_W['acid']] = array_elements(array('name', 'acid', 'key', 'secret', 'level'), $_W['account']);
}
template('wxapp/payment');