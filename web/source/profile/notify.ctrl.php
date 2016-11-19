<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/10
 * Time: 10:42
 * 通信参数配置
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('mail');
$do = in_array($do, $dos) ? $do : 'mail';

uni_user_permission_check('profile_notify');
if ($do == 'mail') {
	$_W['page']['title'] = '邮件通知参数配置';
	if (checksubmit('submit')) {
		$notify['mail'] = array(
			'username' => $_GPC['username'],
			'password' => $_GPC['password'],
			'smtp' => $_GPC['smtp'],
			'sender' => $_GPC['sender'],
			'signature' => $_GPC['signature'],
		);
		$setting = array('notify' => iserializer($notify));
		pdo_update('uni_settings', $setting, array('uniacid' => $_W['uniacid']));
		load()->func('communication');
		$result = ihttp_email($notify['mail']['username'], $_W['account']['name'] . '验证邮件'.date('Y-m-d H:i:s'), '如果您收到这封邮件则表示您系统的发送邮件配置成功！');
		if (is_error($result)) {
			message('配置失败，请检查配置信息');
		}
		cache_delete("unisetting:{$_W['uniacid']}");
		message('更新设置成功！', url('profile/notify',array('do' => 'mail')));
	}
	$notify_setting = uni_setting_load('notify');
	$mail_setting = empty($notify_setting['notify']['mail'])? array() : $notify_setting['notify']['mail'];
}
template('profile/notify');