<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */

defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('material_mass');
$_W['page']['title'] = '永久素材-微信素材';
$dos = array('image', 'del', 'export', 'news', 'down', 'list', 'preview', 'modal', 'send');
$do = in_array($do, $dos) ? $do : 'list';

if($do == 'list') {
	$type = trim($_GPC['type']) ? trim($_GPC['type']) : 'news';
	$condition = " as a RIGHT JOIN ". tablename('wechat_news')." as b ON a.id = b.attach_id WHERE a.uniacid = :uniacid AND a.type = :type AND a.model = :model AND a.media_id != ''";
	$params = array(':uniacid' => $_W['uniacid'], ':type' => $type, ':model' => 'perm');
	$id = intval($_GPC['id']);
	$title = addslashes($_GPC['title']);
	if(!empty($title)) {
		$condition .= ' AND (b.title LIKE :title OR b.author LIKE :title OR b.digest LIKE :title)';
		$params[':title'] = '%'.$title. "%";
	}
	$pageindex = max(1, intval($_GPC['page']));
	$pagesize = 21;
	$limit = " ORDER BY a.id DESC, b.id ASC LIMIT " . ($pageindex - 1) * $pagesize . ", {$pagesize}";
	$total = pdo_fetchall("SELECT a.* FROM " . tablename('wechat_attachment') .$condition, $params, 'id');
	$total = count($total);
	$material_list = pdo_fetchall("SELECT a.* FROM " . tablename('wechat_attachment') .$condition . $limit, $params, 'id');
	if(!empty($material_list)) {
		foreach($material_list as &$material) {
			if($type == 'video') {
				$material['tag'] = iunserializer($row['tag']);
			} elseif($type == 'news') {
				$material['items'] = pdo_fetchall("SELECT * FROM " . tablename('wechat_news') . " WHERE uniacid = :uniacid AND attach_id = :attach_id ORDER BY displayorder ASC", array(':uniacid' => $_W['uniacid'], ':attach_id' => $material['id']));
				if (!empty($material['items'])) {
					$material['prompt_msg'] = false;
					foreach($material['items'] as $material_row) {
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

if($do == 'preview') {
	$wxname = trim($_GPC['__input']['wxname']);
	$type = trim($_GPC['__input']['type']);
	$media_id = trim($_GPC['__input']['media_id']);

template('platform/material');
