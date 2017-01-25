<?php
/**
 * 管理小程序
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');

$dos = array('edit', 'get_categorys', 'save_category', 'del_category', 'switch_version');
$do = in_array($do, $dos) ? $do : 'edit';
$_W['page']['title'] = '小程序 - 管理';

if ($do == 'del_category') {
	$id = $_GPC['id'];
	$result = pdo_delete('site_category', array('id' => $id));
}

if ($do == 'get_categorys') {
	$multiid = intval($_GPC['multiid']);
	$categorys = pdo_getall('site_category', array('uniacid' => $_GPC['uniacid'], 'multiid' => $multiid));
	return message(error(1, $categorys), '', 'ajax');
}

if ($do == 'save_category') {
	$post =  $_GPC['post'];
	$multiid = intval($_GPC['multiid']);
	foreach ($post as $category) {
		if (!empty($category['id'])) {
			$update = array('name' => $category['name'], 'displayorder' => $category['displayorder'], 'linkurl' => $category['linkurl']);
			pdo_update('site_category', $update, array('uniacid' => $_GPC['uniacid'], 'id' => $category['id']));
		} else {
			if (!empty($category['name'])) {
				$insert = $category;
				$insert['uniacid'] = $_GPC['uniacid'];
				$insert['multiid'] = $multiid;
				unset($insert['$$hashKey']);
				pdo_insert('site_category', $insert);
			}
		}
	}
	return message(error(1, 1), '', 'ajax');
}

if ($do == 'edit') {
	$multiid = intval($_GPC['multiid']);
	$operate = $_GPC['operate'];
	$version_id = intval($_GPC['version_id']);
	if ($operate == 'delete') {
		$type = $_GPC['type'];
		$id = intval($_GPC['id']);
		pdo_delete('site_'.$type, array('id' => $id));
		message('删除成功', url('wxapp/version/edit', array('multiid' => $multiid)), 'success');
	}
	if (checksubmit('submit')) {
		$slide = $_GPC['slide'];
		$nav = $_GPC['nav'];
		$recommend = $_GPC['recommend'];
		$id = intval($_GPC['id']);
		if (!empty($slide)) {
			if (empty($id)) {
				$slide['uniacid'] = $_GPC['uniacid'];
				$slide['multiid'] = $multiid;
				pdo_insert('site_slide', $slide);
				message('添加幻灯片成功', url('wxapp/version/edit', array('multiid' => $multiid, 'uniacid' => $_GPC['uniacid'])), 'success');
			} else {
				$result = pdo_update('site_slide', $slide, array('uniacid' => $_GPC['uniacid'], 'multiid' => $multiid, 'id' => $id));
				message('更新幻灯片成功', url('wxapp/version/edit', array('multiid' => $multiid, 'uniacid' => $_GPC['uniacid'])), 'success');
			}
		}
		if (!empty($nav)) {
			if (empty($id)) {
				$nav['uniacid'] = $_GPC['uniacid'];
				$nav['multiid'] = $multiid;
				$nav['status'] = 1;
				pdo_insert('site_nav', $nav);
				message('添加导航图标成功', url('wxapp/version/edit', array('wxapp' => 'nav', 'multiid' => $multiid, 'uniacid' => $_GPC['uniacid'])), 'success');
			} else {
				pdo_update('site_nav', $nav, array('uniacid' => $_GPC['uniacid'], 'multiid' => $multiid, 'id' => $id));
				message('更新导航图标成功', url('wxapp/version/edit', array('wxapp' => 'nav', 'multiid' => $multiid, 'uniacid' => $_GPC['uniacid'])), 'success');
			}
		}
		if (!empty($recommend)) {
			if (empty($id)) {
				$recommend['uniacid'] = $_GPC['uniacid'];
				$result = pdo_insert('site_article', $recommend);
				message('添加推荐图片成功', url('wxapp/version/edit', array('wxapp' => 'recommend', 'multiid' => $multiid, 'uniacid' => $_GPC['uniacid'])), 'success');
			} else {
				pdo_update('site_article', $recommend, array('uniacid' => $_GPC['uniacid'], 'id' => $id));
				message('更新推荐图片成功', url('wxapp/version/edit', array('wxapp' => 'recommend', 'multiid' => $multiid, 'uniacid' => $_GPC['uniacid'])), 'success');
			}
		}
	}
	$slides = pdo_getall('site_slide', array('uniacid' => $_GPC['uniacid'], 'multiid' => $multiid));
	$navs = pdo_getall('site_nav', array('uniacid' => $_GPC['uniacid'], 'multiid' => $multiid));
	if (!empty($navs)) {
		foreach($navs as &$nav) {
			$nav['css'] = iunserializer($nav['css']);
		}
	}
	$recommends = pdo_getall('site_article', array('uniacid' => $_GPC['uniacid']));
	$version_info = pdo_get('wxapp_versions', array('multiid' => $multiid, 'uniacid' => $_GPC['uniacid'], 'id' => $version_id), array('id', 'version', 'uniacid'));
	$wxapp_info = pdo_get('account_wxapp', array('uniacid' => $version_info['uniacid']));
	$versionid = $version_info['id'];
	$modules = pdo_getcolumn('wxapp_versions', array('id' => $versionid), 'modules');
	$modules = json_decode($modules, true);
	if (!empty($modules)) {
		foreach ($modules as $module => &$version) {
			$version = pdo_get('modules', array('name' => $module));
		}
	}
	template('wxapp/wxapp-edit');
}

if ($do == 'switch_version') {
	$uniacid = intval($_GPC['uniacid']);
	if (!empty($uniacid)) {
		$wxapp_version_lists = pdo_getall('wxapp_versions', array('uniacid' => $uniacid));
	}
	template('wxapp/switch-version');
}