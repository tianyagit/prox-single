<?php
/**
 * 小程序下载
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');


//load()->func('communication');
load()->classs('cloudapi');

$dos = array('front_download', 'uuid', 'qrcode', 'checkscan','commitcode');
$do = in_array($do, $dos) ? $do : 'front_download';

$_W['page']['title'] = '小程序下载 - 小程序 - 管理';

$uniacid = intval($_GPC['uniacid']);
$version_id = intval($_GPC['version_id']);
if (!empty($uniacid)) {
	$wxapp_info = wxapp_fetch($uniacid);
}
if (!empty($version_id)) {
//	$version_info = wxapp_version($version_id);
//	$wxapp_info = wxapp_fetch($version_info['uniacid']);
	template('wxapp/wxapp-up');
}
$cloud_api = new CloudApi();
if($do == 'uuid') {
	$data = $cloud_api->get('wxapp', 'upload', array('do' => 'uuid'));
	echo json_encode($data);
}

if($do == 'qrcode') {
	$uuid = $_GPC['uuid'];
	$cloud_api = new CloudApi();
	$data = $cloud_api->get('wxapp', 'upload', array('do' => 'qrcode',
		'uuid'=>$uuid),
		 'html');
	echo $data;
}

if($do == 'checkscan') {
	$uuid = $_GPC['uuid'];
	$last = $_GPC['last'];
	$cloud_api = new CloudApi();
	$data = $cloud_api->get('wxapp', 'upload', array('do' => 'checkscan',
		'uuid'=>$uuid,
		'last'=>$last),
		'json');
	echo json_encode($data);
}

// 上传代码
if($do == 'commitcode') {
	$appid = $_GPC['appid'];
	$user_version = $_GPC['user_version'];
	$user_desc = $_GPC['user_desc'];
	$ticket = $_GPC['ticket'];

	$cloud_api = new CloudApi();
	$data = $cloud_api->get('wxapp', 'upload', array('do' => 'commitcode',
		'appid' => $appid,
		'user_version'=>$user_version,
		'user_desc'=>$user_desc,
		'ticket'=>$ticket),
		'json');
	echo json_encode($data);
}


// 代码所有页面

