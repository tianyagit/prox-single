<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * 素材管理列表页
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('list', 'sync', 'del_material');
$do = in_array($do, $dos) ? $do : 'list';

$_W['page']['title'] = '永久素材-微信素材';
uni_user_permission_check('material_mass');
load()->model('material');

if($do == 'list') {
	$type = trim($_GPC['type']) ? trim($_GPC['type']) : 'news';
	if ($type == 'news') {
		$condition = " as a RIGHT JOIN " . tablename('wechat_news') . " as b ON a.id = b.attach_id WHERE a.uniacid = :uniacid AND a.type = :type AND a.model = :model AND a.media_id != ''";
		$params = array(':uniacid' => $_W['uniacid'], ':type' => $type, ':model' => 'perm');
		$id = intval($_GPC['id']);
		$title = addslashes($_GPC['title']);
		if (!empty($title)) {
			$condition .= ' AND (b.title LIKE :title OR b.author LIKE :title OR b.digest LIKE :title)';
			$params[':title'] = '%' . $title . "%";
		}
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize = 21;
		$limit = " ORDER BY a.id DESC, b.id ASC LIMIT " . ($pageindex - 1) * $pagesize . ", {$pagesize}";
		$total = pdo_fetchall("SELECT a.* FROM " . tablename('wechat_attachment') . $condition, $params);
		$total = count($total);

		$material_list = pdo_fetchall("SELECT a.* FROM " . tablename('wechat_attachment') . $condition . $limit, $params, 'id');
		if (!empty($material_list)) {
			foreach ($material_list as &$material) {
				if ($type == 'video') {
					$material['tag'] = iunserializer($row['tag']);
				} elseif ($type == 'news') {
					$material['items'] = pdo_fetchall("SELECT * FROM " . tablename('wechat_news') . " WHERE uniacid = :uniacid AND attach_id = :attach_id ORDER BY displayorder ASC", array(':uniacid' => $_W['uniacid'], ':attach_id' => $material['id']));
					if (!empty($material['items'])) {
						$material['prompt_msg'] = false;
						foreach ($material['items'] as $material_row) {
							if (empty($material_row['title']) || empty($material_row['thumb_url']) || empty($material_row['content'])) {
								$material['prompt_msg'] = true;
								break;
							}
						}
					}
				}
			}
		}
		$pager = pagination($total, $pageindex, $pagesize);
	}
	if ($type == 'image') {
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize = 24;
		$image_list = pdo_getslice('wechat_attachment', array('uniacid' => $_W['uniacid'], 'type' => 'image', 'model' => 'perm'), array($pageindex, $pagesize), $total, array(),'', 'id DESC');
		$pager = pagination($total, $pageindex, $pagesize);
	}
	if ($type == 'voice') {
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize = 24;
		$voice_list = pdo_getslice('wechat_attachment', array('uniacid' => $_W['uniacid'], 'type' => 'voice', 'model' => 'perm'), array($pageindex, $pagesize), $total, array(),'', 'id DESC');
		$pager = pagination($total, $pageindex, $pagesize);
	}
	if ($type == 'video') {
		$pageindex = max(1, intval($_GPC['page']));
		$pagesize = 24;
		$video_list = pdo_getslice('wechat_attachment', array('uniacid' => $_W['uniacid'], 'type' => 'video', 'model' => 'perm'), array($pageindex, $pagesize), $total, array(),'', 'id DESC');
		foreach($video_list as &$row) {
			$row['tag'] = iunserializer($row['tag']);
		}
		$pager = pagination($total, $pageindex, $pagesize);
	}
}

if ($do == 'del_material') {
	$wechat_api = WeAccount::create($_W['acid']);
	$media_id = $_GPC['__input']['media_id'];
	$material = pdo_get('wechat_attachment', array('uniacid' => $_W['uniacid'], 'media_id' => $media_id));
	$result = $wechat_api->delMaterial($media_id);
	if ($result['errcode'] == 0) {
		$result = error(0, $material['type']);
		if ($material['type'] == 'news') {
			pdo_delete('wechat_news', array('uniacid' => $_W['uniacid'], 'attach_id' => $material['id']));
		}
		pdo_delete('wechat_attachment', array('uniacid' => $_W['uniacid'], 'media_id' => $media_id));
	}
	message($result, '', 'ajax');
}

if ($do == 'sync') {
	$wechat_api = WeAccount::create($_W['acid']);
	$pageindex = max(1, $_GPC['pageindex']);
	$news_list = $wechat_api->batchGetMaterial('news', ($pageindex-1)*20);
	$wechat_existid = empty($_GPC['wechat_existid']) ? array() : $_GPC['wechat_existid'];
	if ($pageindex == 1) {
		$wechat_existid = syncMaterial($news_list['data'], array());
		if ($news_list['total_count'] > 20) {
			$total = ceil($news_list['total_count']/20);
			message(error('1', array('total' => $total, 'pageindex' => $pageindex+1, 'wechat_existid' => $wechat_existid)), '', 'ajax');
		} else {
			$news_allid = pdo_getall('wechat_attachment', array('uniacid' => $_W['uniacid'], 'type' => 'news'), array('id'), 'id');
			$news_allid = array_keys($news_allid);
			$delete_id = array_diff($news_allid, $wechat_existid);
			if (!empty($delete_id)) {
				foreach ($delete_id as $id) {
					pdo_delete('wechat_attachment', array('uniacid' => $_W['uniacid'], 'id' => $id));
					pdo_delete('wechat_news', array('uniacid' => $_W['uniacid'], 'attach_id' => $id));
				}
			}
			message(error(0), '', 'ajax');
		}
	} else {
		$total = intval($_GPC['total']);
		$wechat_existid = syncMaterial($news_list['data'], $wechat_existid);
		if ($total == $pageindex) {
			$news_allid = pdo_getall('wechat_attachment', array('uniacid' => $_W['uniacid'], 'type' => 'news'), array('id'), 'id');
			$news_allid = array_keys($news_allid);
			$delete_id = array_diff($news_allid, $wechat_existid);
			if (!empty($delete_id)) {
				foreach ($delete_id as $id) {
					pdo_delete('wechat_attachment', array('uniacid' => $_W['uniacid'], 'id' => $id));
					pdo_delete('wechat_news', array('uniacid' => $_W['uniacid'], 'attach_id' => $id));
				}
			}
			message(error(0), '', 'ajax');
		} else {
			message(error('1', array('total' => $total, 'pageindex' => $pageindex+1, 'wechat_existid' => $wechat_existid)), '', 'ajax');
		}
	}
}

template('platform/material');