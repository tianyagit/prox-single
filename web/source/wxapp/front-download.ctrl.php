<?php
/**
 * 小程序下载
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('wxapp');

$dos = array('front_download');
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
	template('wxapp/wxapp-up');
}

$oauth = new WxAppOAuth('wx991ec14508b7d1e7',
	'deba8d99fd614522bf6f9c074f7801c9',
	'ticket@@@7CiP6eLB1jG1jG_MEQXDOi3dmWe2uBvOX_y-OsbxlPh9R0Ds9HtwApjNYutja0mtM5i5XdrOIFb4kl_uezA8_Q');

if($do == 'redirect') {
	$siteroot = $_W['siteroot'];
	$redirect_uri = $oauth->redirect($siteroot.'web/index.php?c=account&a=openwechat&do=oauth');
	header('Location:'.$redirect_uri);
}
if ($do == 'oauth') {
	$auth_code = $_GPC['auth_code'];
	dump($oauth->authData($auth_code));
	exit;
}