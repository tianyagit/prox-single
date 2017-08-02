<?php
/**
 * [WeEngine System] Copyright (c) 2017 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('material');
load()->model('mc');
load()->func('file');

load()->classs('resource');

if (in_array($do, array('keyword', 'news', 'video', 'voice', 'module', 'image'))) {
	$result = Resource::getResource($do)->getResources();
	iajax(0, $result);
	return ;
}


$type = $_GPC['type'];
$resourceid = intval($_GPC['resource_id']);
$uniacid = intval($_W['uniacid']);
$uid = intval($_W['uid']);
$acid = intval($_W['acid']);
$url = $_GPC['url'];
$isnetwork_convert = !empty($url);

/**
 *  校验数据
 */
if($do == 'tolocal' || $do == 'towechat') {
	if (!in_array($type, array('news', 'image', 'video', 'voice'))) {
		iajax(1, '转换类型不正确');
		return;
	}
}

/**
 *  网络图转本地
 */
if ($do == 'networktolocal') {
	$material = network_image_to_local($url, $uniacid, $uid);
	if (is_error($material)) {
		iajax(1, $material['message']);
		return;
	}
	iajax(0, $material);
}
/**
 *  转为本地图片
 */
if ($do == 'tolocal') {

	if ($type == 'news') {
		$material = news_to_local($resourceid); // 微信图文转到本地数据库
	} else {
		$material = material_to_local($resourceid, $uniacid, $uid, $type); // 微信素材转到本地数据库
	}
	if (is_error($material)) {
		iajax(1, $material['message']);
		return;
	}
	iajax(0, $material);
}
/**
 *  网络图片转 wechat
 */
if ($do == 'networktowechat') {
	$material = network_image_to_wechat($url, $uniacid, $uid, $acid); //网络图片转为 微信 图片
	if (is_error($material)) {
		iajax(1, $material['message']);
		return;
	}
	iajax(0, $material);
	return;
}

/*
 *   转为微信资源
 */
if ($do == 'towechat') {
	// 图片 视频 语音 传到微信 并保存数据库返回
	$material = null;
	if ($type != 'news') {
		$material = material_to_wechat($resourceid, $uniacid, $uid, $acid, $type); // 本地素材 传到微信服务器
	}else {
		$material = material_local_news_upload($resourceid); 	// 本地图文到服务器
		if (!is_error($material)) {
			$material['items'] = $material['news']; //前台静态界面需要items;
		}
	}
	if (is_error($material)) {
		iajax(1, $material['message']);
		return;
	}
	iajax(0, $material);
}
