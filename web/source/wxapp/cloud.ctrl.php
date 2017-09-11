<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/31
 * Time: 9:06
 */
// 去授权 因域名限制 只能跳转到云服务域名下 再去授权
if($do == 'redirect') {
	$siteroot = $_W['siteroot'];
	$redirect_uri = $oauth->redirect($siteroot.'web/index.php?c=account&a=openwechat&do=auth');
	header('Location:'.$redirect_uri);
}
// 获取token
if($do == 'auth') {
	$auth_code = $_GPC['auth_code'];
	dump($oauth->authData($auth_code));
	exit;
}
// 上传代码
if($do == 'commitcode') {

}
// 预览应用
if($do == 'preview') {

}

// 可使用的分类
if($do == 'category') {

}

// 提交审核
if($do == 'commitAudit') {

}

