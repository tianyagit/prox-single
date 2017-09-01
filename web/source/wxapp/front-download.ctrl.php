<?php
/**
 * 小程序下载
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('wxapp');
load()->classs('wxapp/wxappcloud');
load()->func('communication');

$dos = array('front_download','redirect','oauth','commitcode','qrcode','submit_audit','category','getpage');
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

if ($do == 'front_download') {
	$wxapp_versions_info = wxapp_version($version_id);

}


$cloud = new WxAppCloud('wx991ec14508b7d1e7');
$wxapp_id  = 'wxb0e582aaff9a169d';//
if($do == 'redirect') {
	$siteroot = $_W['siteroot'];
	$redirect_uri = $cloud->redirect($siteroot.'web/index.php?c=wxapp&a=front-download&do=oauth');
	header('Location:'.$redirect_uri);
}
if ($do == 'oauth') {
	$auth_code = $_GPC['auth_code'];
	var_dump($cloud->authData($auth_code));
	exit;
}

// 上传代码
if($do == 'commitcode') {

	if($_W['ispost']) {
		$user_version = $_GPC['user_version'];
		$user_desc = $_GPC['user_desc'];
		$template_id = $_GPC['template_id'];
		$cloud->commitCode($template_id,$wxapp_id, $user_version, $user_desc);
		itoast('上传代码成功');
	}

}
// 预览应用
if($do == 'preview') {

}

if($do == 'qrcode') {
	header('Content-Type: images/jpeg');
	echo $cloud->getQrCode($wxapp_id);
}

// 可使用的分类
if($do == 'category') {
	dump($cloud->getCategory($wxapp_id));
}
// 代码所有页面
if($do == 'getpage') {
	dump($cloud->getPage($wxapp_id));
}
// 提交审核
if($do == 'submit_audit') {
	$data = array();
	$cloud->submitAudit($wxapp_id,$data);
}
template('wxapp/wxapp-up');