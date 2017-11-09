<?php
/**
 * 找回密码短信签名设置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');
load()->model('setting');

$dos = array('display', 'save_sms_sign');
$do = in_array($do, $dos) ? $do : 'display';

$setting_sms_sign = setting_load('site_sms_sign');
$setting_sms_sign = !empty($setting_sms_sign['site_sms_sign']) ? $setting_sms_sign['site_sms_sign'] : '';

if ($do == 'display') {
	$cloud_sms_info = cloud_sms_info();
	$cloud_sms_signs = $cloud_sms_info['sms_sign'];
}

if ($do == 'save_sms_sign') {
	$setting_sms_sign = trim($_GPC['site_sms_sign']);

	$setting_sms_sign = setting_save($setting_sms_sign, 'site_sms_sign');
	if (is_error($setting_sms_sign)) {
		iajax(-1, '设置失败', url('user/sms-sign'));
	}
	iajax(0, '设置成功', url('user/sms-sign'));
}
template('user/sms-sign');