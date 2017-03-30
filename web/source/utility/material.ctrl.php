<?php
/**
 * 自动回复公共组建（素材：video、news、image、voice）
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$type = trim($_GPC['type']);
$pageindex = max(1, intval($_GPC['page']));
$pagesize = 10;
if($type == 'image') {
	$pagesize = 50;
}
$material_list = pdo_getslice('wechat_attachment', array('uniacid' => $_W['uniacid'], 'type' => $type, 'model' => 'perm'), array($pageindex, $pagesize), $total, array(), 'id', 'createtime DESC');
if(!empty($material_list)) {
	foreach($material_list as &$row) {
		if($type == 'video') {
			$row['tag'] = iunserializer($row['tag']);
			$row['attach'] = tomedia($row['attachment'], true);
		} elseif($type == 'news') {
			$row['items'] = pdo_getall('wechat_news', array('uniacid' => $_W['uniacid'], 'attach_id' => $row['id']));
			if(!empty($row['items'])) {
				foreach($row['items'] as &$li) {
					$li['thumb_url'] = tomedia($li['thumb_url']);
				}
				unset($li);
			}
		} elseif($type == 'image') {
			$row['attach'] = tomedia($row['attachment'], true);
			$row['url'] = "url({$row['attach']})";
		} elseif($type == 'voice') {
			$row['attach'] = tomedia($row['attachment'], true);
		}
		$row['createtime_cn'] = date('Y-m-d H:i', $row['createtime']);
	}
	unset($row);
}
$result = array(
	'items' => $material_list,
	'pager' => pagination($total, $pageindex, $pagesize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null')),
);
message($result, '', 'ajax', true);