<?php
/**
 * 支付参数配置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('account');
load()->model('wxapp');
$dos = array('get_setting', 'display');
$do = in_array($do, $dos) ? $do : 'display';

if ($do == 'get_setting') {
	$pay_setting = wxapp_payment_param();
	iajax(0, $pay_setting, '');
}

if ($do = 'display') {
	$pay_setting = wxapp_payment_param();
}
template('wxapp/payment');