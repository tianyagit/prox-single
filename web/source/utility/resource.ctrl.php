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
$uniacid = intval($_W['uniacid']);
$uid = intval($_W['uid']);
if ($do == 'tolocal' || $do == 'towechat') {
}
// 转为本地图片
if ($do == 'tolocal') {
	$type = $_GPC['type'];
	if (!in_array($type, array('news', 'image', 'video', 'voice'))) {
		iajax(1, '转换类型不正确');
	}
	$resourceid = $_GPC['resource_id'];

	$material = material_get($resourceid);
	if(is_error($material)) {
		iajax(1, $material['message']);
		return;
	}


	if($type != 'news') {
		$path = file_remote_attach_fetch($material['url']); //网络转本地图片
		if(is_error($path)) {
			iajax(1 , $path['message']);
			return;
		}
		$filetemp = explode('/',$path);
		$filename = array_reverse($filetemp)[0];
		$data = array('uniacid' => $uniacid, 'uid' => $uid,
			'filename' => $filename,
			'attachment' => $path,
			'type' => $type == 'image' ? 1 : ($type == 'video'? 2 : 3),
			'createtime'=>TIMESTAMP
		);
		pdo_insert('core_attachment', $data);
		$id = pdo_insertid();
		$data['id'] = $id;
		iajax('0', $data);
		return;
	}
	// 如果是 news 类型
	$material = material_get($resourceid);
	if(is_error($material)) {
		iajax(1, $material['message']);
		return;
	}
	$attach_id = material_news_set($material,$resourceid);
	if(is_error($resource)) {
		iajax(1, $attach_id['message']);
		return;
	}
	iajax(0, $material);// 返回转换后的数据



}

// 转为微信资源
/*
 *   [media_id] => UgGyzOLsgOJs57hLpQ-Z3SC-2FIYLju7jar57w2WMnE
[url] => http://mmbiz.qpic.cn/mmbiz_png/GiaZj7Tr2pg816UtmOWR2zUJ2d5q3DJsy0efpAL8aGRcBWkTW2aGIcfaN2icqqQ3CCrIicgHTlKLYm7LicUCQShMhw/0?wx_fmt=png
 */
if ($do == 'towechat') {
	$type = $_GPC['type'];
	if (!in_array($type, array('news', 'image', 'video', 'voice'))) {
		iajax(1, '转换类型不正确');
	}
	$resourceid = $_GPC['resource_id'];
	// 图片 视频 语音 传到微信 并保存数据库返回
	if ($type != 'news') {
		$result = material_local_upload($resourceid); //本地资源上传到服务器
		if (is_error($result)) {
			iajax(1, $result['message']);
			return;
		}
		$data = array('uniacid' => $uniacid, 'uid' => $uid, 'acid' => $acid,
				'media_id' => $result['media_id'],
				'attachment' => $result['url'],
				'type' => $type,
				'model' => 'perm',
				'createtime'=>TIMESTAMP
			);
		pdo_insert('wechat_attachment', $data);
		$id = pdo_insertid();
		$data['url'] = tomedia($result['url']);
		$data['id'] = $id;
		iajax('0', $data);
		return;
	}
	// 本地图文到服务器
	$news = material_local_news_upload($resourceid);
	if (is_error($news)) {
		iajax(1, $news['message']);
		return;
	}
	iajax(0, $news);
}
