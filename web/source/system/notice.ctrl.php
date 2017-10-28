<?php
/**
 * 用户到期短信提醒时间设置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('cron');

$dos = array('display', 'post');
$do = in_array($do, $dos) ? $do : 'display';
$_W['page']['title'] = '站点管理 - 设置  - 到期提醒';

$sms_info = pdo_get('core_cron', array('filename' => 'sms'));
if ($do == 'display') {
	if (empty($sms_info)) {
		$sms_info = array(
			'module' => 'task',
			'type' => 2,
			'name' => '用户到期发送短信任务',
			'filename' => 'sms',
			'day' => -1,
			'hour' => 16,
			'minute' => 30,
			'status' => 1,
			'createtime' =>TIMESTAMP
		);
		$cron = cron_add($sms_info);

		if (is_error($cron)) {
			itoast($cron['message']);
		}
	}
	if (empty($sms_info['hour'])) {
		$sms_info['hour'] = '00';
	}
	if (empty($sms_info['minute'])) {
		$sms_info['minute'] = '00';
	}
	$time = $sms_info['hour'] . ':' . $sms_info['minute'];
}

if ($do == 'post') {
	$time = trim($_GPC['time']);
	$time_arr = explode(':', $time);
	pdo_update('core_cron', array('hour' => $time_arr[0], 'minute' => $time_arr['1'], 'status' => $_GPC['status']), array('id' =>$sms_info['id'], 'filename' => 'sms'));
	$cron_status = cron_check($sms_info['cloudid']);
	if (is_error($cron_status)) {
		itoast($cron_status['message'], url('system/notice/display'));
	}
	$cron_update = cron_run($sms_info['id']);
	if (is_error($cron_update)) {
		itoast($cron_update['message'], url('system/notice/display'));
	}
	itoast('设置成功', url('system/notice/display'));
}

template('system/notice');