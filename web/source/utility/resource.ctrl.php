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

//if (in_array($do, array('keyword', 'news', 'video', 'voice', 'module', 'image'))) {
//	$result = Resource::getResource($do)->getResources();
//	iajax(0, $result);
//	return ;
//}
$isLocal = $_GPC['local'] == 'local';
$uniacid = $_W['uniacid'];
$acid = $_W['acid'];
$uid = $_W['uid'];

if ($do == 'keyword') {
	$keyword = addslashes($_GPC['keyword']);
	$pindex = max(1, $_GPC['page']);
	$psize = 24;
	$condition = array('uniacid' => $this->uniacid, 'status' => 1);
	if (!empty($keyword)) {
		$condition['content like'] = '%'.$keyword.'%';
	}
	$keyword_lists = pdo_getslice('rule_keyword', $condition, array($pindex, $psize), $total, array(), 'id');
	$result = array(
		'items' => $keyword_lists,
		'pager' => pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback' => 'null', 'isajax' => 1)),
	);
	iajax(0, $result);
}

if ($do == 'news') {
	$server = $isLocal ? MATERIAL_LOCAL : MATERIAL_WEXIN;
	$page_index = max(1, $_GPC['page']);
	$page_size = 24;
	$search = addslashes($_GPC['keyword']);
	$material_news_list = material_news_list($server, $search, array('page_index' => $page_index, 'page_size' => $page_size), true);
	$material_list = $material_news_list['material_list'];
	$pager = $material_news_list['page'];
	$result = array('items' => $material_list, 'pager' => $pager);
	iajax(0, $result);
}

if ($do == 'video' || $do == 'voice') {
	$server = $isLocal ? MATERIAL_LOCAL : MATERIAL_WEXIN;
	$page_index = max(1, $_GPC['page']);
	$page_size = 10;
	$material_news_list = material_list($do, $server, array('page_index' => $page_index, 'page_size' => $page_size));
	$material_list = $material_news_list['material_list'];
	$pager = $material_news_list['page'];
	$result = array('items' => $material_list, 'pager' => $pager);
	iajax(0, $result);
}

if ($do == 'image') {
	if ($isLocal) {
		$page = max(1, $_GPC['page']);
		$condition = ' WHERE uniacid = :uniacid AND type = :type';
		$params = array(':uniacid' => $this->uniacid, ':type' => 1);

		$year = intval($this->query('year'));
		$month = intval($this->query('month'));
		if ($year > 0 || $month > 0) {
			if ($month > 0 && !$year) {
				$year = date('Y');
				$starttime = strtotime("{$year}-{$month}-01");
				$endtime = strtotime('+1 month', $starttime);
			} elseif ($year > 0 && !$month) {
				$starttime = strtotime("{$year}-01-01");
				$endtime = strtotime('+1 year', $starttime);
			} elseif ($year > 0 && $month > 0) {
				$year = date('Y');
				$starttime = strtotime("{$year}-{$month}-01");
				$endtime = strtotime('+1 month', $starttime);
			}
			$condition .= ' AND createtime >= :starttime AND createtime <= :endtime';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}

		$sql = 'SELECT * FROM '.tablename('core_attachment')." {$condition} ORDER BY id DESC LIMIT ".(($page - 1) * $this->pagesize).','.$this->pagesize;
		//		dd($sql);
		$list = pdo_fetchall($sql, $params, 'id');
		foreach ($list as &$item) {
			$item['url'] = tomedia($item['attachment']);
			unset($item['uid']);
		}
		$total = pdo_fetchcolumn('SELECT count(*) FROM '.tablename('core_attachment')." {$condition}", $params);
		$result = array(
			'items' => $list,
			'pager' => pagination($total, $page, $this->pagesize, '', array('before' => '2', 'after' => '3', 'ajaxcallback' => 'null')),
		);
		iajax(0, $result);
	} else {
		$server = MATERIAL_WEXIN;
		$page_index = max(1, $_GPC['page']);
		$material_news_list = material_list('image', $server, array('page_index' => $page_index, 'page_size' => 24));
		$material_list = $material_news_list['material_list'];
		$pager = $material_news_list['page'];
		// 因 meterial.js finish 输出的内容需要 url
		foreach ($material_list as &$meterial) {
			$meterial['attach'] = tomedia($meterial['attachment'], true);
			$meterial['url'] = $meterial['attach'];
		}
		$result = array('items' => $material_list, 'pager' => $pager);
		iajax(0, $result);
	}
}

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
		iajax(1, $meterial['message']);
		return;
	}
	$attach_id = material_news_set($meterial,$resourceid);
	if(is_error($resource)) {
		iajax(1, $attach_id['message']);
		return;
	}
	iajax(0, $meterial);// 返回转换后的数据



}

// 转为微信资源
/*
 *   [media_id] => UgGyzOLsgOJs57hLpQ-Z3SC-2FIYLju7jar57w2WMnE
[url] => http://mmbiz.qpic.cn/mmbiz_png/GiaZj7Tr2pg816UtmOWR2zUJ2d5q3DJsy0efpAL8aGRcBWkTW2aGIcfaN2icqqQ3CCrIicgHTlKLYm7LicUCQShMhw/0?wx_fmt=png
 */
if ($do == 'towechat') {
	$type = 'image';
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
