<?php
/**
 * 退款参数配置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('payment');
load()->model('account');

$dos = array('save_setting', 'display');
$do = in_array($do, $dos) ? $do : 'display';
uni_user_permission_check('profile_setting');
$_W['page']['title'] = '退款参数 - 公众号选项';

if ($do == 'display') {
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$setting = $setting['payment'];
	if (empty($setting['wechat_refund'])) {
		$setting['wechat_refund'] = array('switch' => 0, 'key' => '', 'cert' => '');
	}
}

if ($do == 'save_setting') {
	$type = $_GPC['type'];
	$param = $_GPC['param'];
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$pay_setting = $setting['payment'];
	if ($type == 'wechat_refund') {
		if (empty($_FILES['cert']['tmp_name'])) {
			if (empty($setting['payment']['wechat_refund']['cert']) && $param['switch'] == 1) {
				itoast('请上传apiclient_cert.pem证书', '', 'info');
			}
			$param['cert'] = $setting['payment']['wechat_refund']['cert'];
		} else {
			$param['cert'] = file_get_contents($_FILES['cert']['tmp_name']);
		}
		if (empty($_FILES['key']['tmp_name'])) {
			if (empty($setting['payment']['wechat_refund']['key']) && $param['switch'] == 1) {
				itoast ('请上传apiclient_key.pem证书', '', 'info');
			}
			$param['key'] = $setting['payment']['wechat_refund']['key'];
		} else {
			$param['key'] = file_get_contents($_FILES['key']['tmp_name']);
		}
	}
	$pay_setting[$type] = $param;
	uni_setting_save('payment', $pay_setting);
	itoast('设置成功', '', 'success');
}

template('profile/refund');