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
	$setting_sms_sign['register'] = !empty($setting_sms_sign['register']) ? $setting_sms_sign['register'] : '';
	$setting_sms_sign['find_password'] = !empty($setting_sms_sign['find_password']) ? $setting_sms_sign['find_password'] : '';
	$setting_sms_sign['user_expire'] = !empty($setting_sms_sign['user_expire']) ? $setting_sms_sign['user_expire'] : '';
}

if ($do == 'save_sms_sign') {
	$setting_sms_sign['register'] = trim($_GPC['register']);
	$setting_sms_sign['find_password'] = trim($_GPC['find_password']);
	$setting_sms_sign['user_expire'] = trim($_GPC['user_expire']);
	$result = setting_save($setting_sms_sign, 'site_sms_sign');
	if (is_error($result)) {
		iajax(-1, '设置失败', url('cloud/sms-sign'));
	}
	iajax(0, '设置成功', url('cloud/sms-sign'));
}
template('cloud/sms-sign');