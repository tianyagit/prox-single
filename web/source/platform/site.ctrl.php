<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/platform/url2qr.ctrl.php : 2016年11月5日 10:40:19 brjun $
 */

defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('platform_site');
$dos = array('wesite', 'article', 'wesite_tpl');
$do = !empty($_GPC['do']) && in_array($do, $dos) ? $do : 'wesite';

if($do == 'wesite') {

	template('platform/wesite-display');
}

if($do == 'wesite_tpl') {

	template('platform/wesite-tpl-display');
}

if($do == 'article') {
	$operations = array('edit_article', 'del_article', 'edit_category', 'del_category', 'display_category');
	$operation = !empty($_GPC['operation']) && in_array($_GPC['operation'], $operations) ? $_GPC['operation'] : '';
	switch ($operation) {
		case 'edit_article':
			$id = intval($_GPC['id']);
			var_dump($id);
			break;
		case 'del_article':
			load()->func('file');
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id,rid,kid,thumb FROM ".tablename('site_article')." WHERE id = :id", array(':id' => $id));
			
			if (empty($row)) {
				message('抱歉，文章不存在或是已经被删除！');
			}
			if (!empty($row['thumb'])) {
				file_delete($row['thumb']);
			}
			if(!empty($row['rid'])) {
				pdo_delete('rule', array('id' => $row['rid'], 'uniacid' => $_W['uniacid']));
				pdo_delete('rule_keyword', array('rid' => $row['rid'], 'uniacid' => $_W['uniacid']));
				pdo_delete('news_reply', array('rid' => $row['rid']));
			}
			if(pdo_delete('site_article', array('id' => $id))){
				message('删除成功！', referer(), 'success');
			}else {
				message('删除失败！', referer(), 'error');
			}
			break;
		case 'edit_category':
			echo 'edit_category';
			break;
		case 'del_category':
			load()->func('file');
			$id = intval($_GPC['id']);
			$category = pdo_fetch("SELECT id, parentid, nid FROM ".tablename('site_category')." WHERE id = '$id'");
			if (empty($category)) {
				message('抱歉，分类不存在或是已经被删除！', referer(), 'error');
			}
			$navs = pdo_fetchall("SELECT icon, id FROM ".tablename('site_nav')." WHERE id IN (SELECT nid FROM ".tablename('site_category')." WHERE id = {$id} OR parentid = '$id')", array(), 'id');
			if (!empty($navs)) {
				foreach ($navs as $row) {
					file_delete($row['icon']);
				}
				pdo_query("DELETE FROM ".tablename('site_nav')." WHERE id IN (".implode(',', array_keys($navs)).")");
			}
			pdo_delete('site_category', array('id' => $id, 'parentid' => $id), 'OR');
			message('分类删除成功！', referer(), 'success');
			break;
		case 'display_category':
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					$update = array('displayorder' => intval($displayorder['displayorder']));
					pdo_update('site_category', $update, array('id' => $id));
				}
				message('分类排序更新成功！', 'refresh', 'success');
			}
			$children = array();
			$category = pdo_fetchall("SELECT * FROM ".tablename('site_category')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid, displayorder DESC, id");
			foreach ($category as $index => $row) {
				if (!empty($row['parentid'])){
					$children[$row['parentid']][] = $row;
					unset($category[$index]);
				}
			}
			template('platform/wesite-category-display');
			break;
		default:
			$category = pdo_fetchall("SELECT id,parentid,name FROM ".tablename('site_category')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
			$parent = array();
			$children = array();
			if (!empty($category)) {
				foreach ($category as $cid => $cate) {
					if (!empty($cate['parentid'])) {
						$children[$cate['parentid']][] = $cate;
					} else {
						$parent[$cate['id']] = $cate;
					}
				}
			}

			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			$params = array();
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND `title` LIKE :keyword";
				$params[':keyword'] = "%{$_GPC['keyword']}%";
			}
			
			if (!empty($_GPC['category']['childid'])) {
				$cid = intval($_GPC['category']['childid']);
				$condition .= " AND ccate = '{$cid}'";
			} elseif (!empty($_GPC['category']['parentid'])) {
				$cid = intval($_GPC['category']['parentid']);
				$condition .= " AND pcate = '{$cid}'";
			}
			$list = pdo_fetchall("SELECT * FROM ".tablename('site_article')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('site_article') . " WHERE uniacid = '{$_W['uniacid']}'".$condition, $params);
			$pager = pagination($total, $pindex, $psize);
			template('platform/wesite-article-display');			
			break;
	}

}