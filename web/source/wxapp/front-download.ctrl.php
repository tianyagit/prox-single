<?php
/**
 * 小程序下载
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('wxapp');
load()->classs('cloudapi');

$dos = array('front_download', 'set_domain', 'uuid', 'qrcode', 'checkscan', 'commitcode','download');
$do = in_array($do, $dos) ? $do : 'front_download';

$_W['page']['title'] = '小程序下载 - 小程序 - 管理';

$uniacid = intval($_GPC['uniacid']);
$version_id = intval($_GPC['version_id']);
if (!empty($uniacid)) {
	$wxapp_info = wxapp_fetch($uniacid);
}
if (!empty($version_id)) {
	$version_info = wxapp_version($version_id);
	$wxapp_info = wxapp_fetch($version_info['uniacid']);
}
//91ec1f9324753048c0096d036a694f86
if ($do == 'front_download') {
	$appurl = $_W['siteroot'].'/app/index.php';
	$uptype = $_GPC['uptype'] == 'auto'? 'auto':'';
	$wxapp_versions_info = wxapp_version($version_id);
	template('wxapp/version-front-download');
}
if($do == 'auto_upload') {
	template('wxapp/version-front-download');
}

if($do == 'set_domain') {

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


