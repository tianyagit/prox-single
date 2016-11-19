<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$do = !empty($do) ? $do : 'display';
$do = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';

if ($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$condition = '';
	$params = array();
	$multiid = intval($_GPC['__input']['multiid']);
	if($multiid > 0) {
		$condition .= " AND multiid = {$multiid}";
	}
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
	}
	$list = pdo_fetchall("SELECT * FROM ".tablename('site_slide')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY displayorder DESC, uniacid DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('site_slide') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
	message($list, 'ajax', 'success');
}

if ($do == 'post') {
	if ($_W['isajax'] && $_W['ispost']) {
		$post = $_GPC['__input'];
		foreach ($post['slide'] as $key => $val) {
			if(empty($val['thumb'])){
				message('幻灯图片不可为空', 'ajax', 'error');
			}
		}
		$multiid = intval($post['multiid']);
		pdo_fetch("DELETE FROM ".tablename('site_slide')." WHERE uniacid = :uniacid AND multiid = :multiid" , array(':uniacid' => $_W['uniacid'],':multiid' => $multiid));
		foreach ($post['slide'] as  $value) {
			$data = array(
				'uniacid' => $_W['uniacid'],
				'multiid' => $multiid,
				'title' => $value['title'],
				'url' => $value['url'],
				'thumb' => $value['thumb'],
				'displayorder' => intval($value['displayorder']),
			);
			pdo_insert('site_slide', $data);
		}
		message('幻灯片保存成功！', 'ajax', 'success');
	}
}