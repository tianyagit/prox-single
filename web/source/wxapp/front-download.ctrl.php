<?php
/**
 * 小程序下载
 * [WeEngine System] Copyright (c) 2014 WE7.CC.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('wxapp');
load()->classs('cloudapi');

$dos = array('front_download', 'domainset', 'uuid', 'qrcode', 'checkscan', 'commitcode', 'download', 'ticket');
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
if ($do == 'domainset') {
	$appurl = $_W['siteroot'].'app/index.php';
	if($version_info) {
		$wxapp  = pdo_get('account_wxapp', array('uniacid'=>$version_info['uniacid']));
		if($wxapp && !empty($wxapp['appdomain'])) {
			$appurl = $wxapp['appdomain'];
		}
	}

	if($_W['ispost']) {
		$appurl = $_GPC['appurl'];
		if(! starts_with($appurl, 'https')) {
			itoast('域名必须以https开头');
			return;
		}
		if($version_info) {
			$update = pdo_update('account_wxapp', array('appdomain'=>$appurl), array('uniacid'=>$version_info['uniacid']));
			if($update) {
				itoast('更新小程序域名成功');
			}
			itoast('更新小程序域名失败');
		}
	}
	template('wxapp/version-front-download');
}
//91ec1f9324753048c0096d036a694f86
if ($do == 'front_download') {
	$appurl = $_W['siteroot'].'/app/index.php';
	$uptype = $_GPC['uptype'];
	if(!in_array($uptype, array('auto','normal'))) {
		$uptype = 'auto';
	}
	$wxapp_versions_info = wxapp_version($version_id);
	template('wxapp/version-front-download');
}

$cloud_api = new CloudApi();
if ($do == 'uuid') {
	$data = $cloud_api->get('wxapp', 'upload', array('do' => 'uuid'), 'json', false);
	echo json_encode($data);
}

if ($do == 'qrcode') {
	$uuid = $_GPC['uuid'];
	$cloud_api = new CloudApi();
	$data = $cloud_api->get('wxapp', 'upload', array('do' => 'qrcode',
		'uuid' => $uuid, ),
		'html', false);
	echo $data;
}

if ($do == 'checkscan') {
	$uuid = $_GPC['uuid'];
	$last = $_GPC['last'];
	$cloud_api = new CloudApi();
	$data = $cloud_api->get('wxapp', 'upload', array('do' => 'checkscan',
		'uuid' => $uuid,
		'last' => $last, ),
		'json', false);
	echo json_encode($data);
}
if ($do == 'ticket') {
	$code = $_GPC['code'];
	$cloud_api = new CloudApi();
	$data = $cloud_api->get('wxapp', 'upload', array('do' => 'ticket',
		'code' => $code, ),
		'html', false);
	echo $data;
}
// 上传代码
if ($do == 'commitcode') {

	$user_version = $_GPC['user_version'];
	$user_desc = $_GPC['user_desc'];
	$ticket = $_GPC['ticket'];

	if (empty($version_id)) {
		itoast('参数错误！', '', '');
	}
	$account_wxapp_info = wxapp_fetch($version_info['uniacid'], $version_id);
	if (empty($account_wxapp_info)) {
		itoast('版本不存在！', referer(), 'error');
	}
	$siteurl = $_W['siteroot'].'app/index.php';
	if(!empty($account_wxapp_info['appdomain'])) {
		$siteurl = $account_wxapp_info['appdomain'];
	}
	$appid = $account_wxapp_info['key'];
	$appdata = array(
		'name' => $account_wxapp_info['name'],
		'modules' => $account_wxapp_info['version']['modules'],
		'siteInfo' => array(
			'name' => $account_wxapp_info['name'],
			'uniacid' => $account_wxapp_info['uniacid'],
			'acid' => $account_wxapp_info['acid'],
			'multiid' => $account_wxapp_info['version']['multiid'],
			'version' => $account_wxapp_info['version']['version'],
			'siteroot' => $siteurl,
			'design_method' => $account_wxapp_info['version']['design_method'],
		),
		'tabBar' => json_decode($account_wxapp_info['version']['quickmenu'], true),
	);

	$commit_data = array('do' => 'commitcode',
		'appid' => $appid,
		'user_version' => $user_version,
		'user_desc' => $user_desc,
		'ticket' => $ticket,
		'modules' => $appdata['modules'],
		'siteInfo' => $appdata['siteInfo'],
		'tabBar' => $appdata['tabBar'],
	);

	$cloud_api = new CloudApi();
	$data = $cloud_api->get('wxapp', 'upload', $commit_data,
		'json', false);
	//	echo $data;
	echo json_encode($data);
}
