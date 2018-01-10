<?php
/**
 * 小程序的接口文件
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$.
*/
defined('IN_IA') or exit('Access Denied');
load()->model('wxapp');
$dos = array('nav', 'slide', 'commend', 'wxapp_web', 'wxappweb_pay', 'wxappweb_pay_result', 'package_app');
$do = in_array($_GPC['do'], $dos) ? $_GPC['do'] : 'nav';

$multiid = intval($_GPC['t']);

if ($do == 'nav') {
	$navs = pdo_getall('site_nav', array(
		'uniacid' => $_W['uniacid'],
		'multiid' => $multiid,
		'status' => 1,
		'icon !=' => '',
	), array('url', 'name', 'icon'), '', 'displayorder DESC');

	if (!empty($navs)) {
		foreach ($navs as $i => &$row) {
			$row['icon'] = tomedia($row['icon']);
		}
	}
	message(error(0, $navs), '', 'ajax');
} elseif ($do == 'slide') {
	$slide = pdo_getall('site_slide', array(
		'uniacid' => $_W['uniacid'],
		'multiid' => $multiid,
	), array('url', 'title', 'thumb'), '', 'displayorder DESC');
	if (!empty($slide)) {
		foreach ($slide as $i => &$row) {
			$row['thumb'] = tomedia($row['thumb']);
		}
	}
	message(error(0, $slide), '', 'ajax');
} elseif ($do == 'commend') {
	//获取一级分类
	$category = pdo_getall('site_category', array(
		'uniacid' => $_W['uniacid'],
		'multiid' => $multiid,
	), array('id', 'name', 'parentid'), '', 'displayorder DESC');
	//一级分类不能添加文章，推荐时获取到其子类
	if (!empty($category)) {
		foreach ($category as $id => &$category_row) {
			if (empty($category_row['parentid'])) {
				$condition['pcate'] = $category_row['id'];
			} else {
				$condition['ccate'] = $category_row['id'];
			}
			$category_row['article'] = pdo_getall('site_article', $condition, array('id', 'title', 'thumb'), '', 'displayorder DESC', array(8));
			if (!empty($category_row['article'])) {
				foreach ($category_row['article'] as &$row) {
					$row['thumb'] = tomedia($row['thumb']);
				}
			} else {
				unset($category[$id]);
			}
		}
	}
	message(error(0, $category), '', 'ajax');
}

if ($do == 'wxapp_web') {
	$version = trim($_GPC['v']);
	$version_info = wxapp_version_by_version($version);
	$url = $_GPC['url'];
	if (empty($url)) {
		//无需查询绑定域名 因为本do方法就是根据小程序域名访问的
		if (count($version_info['modules']) > 1) {
			$url = murl('wxapp/home/package_app', array('v'=>$version));//多模块打包入口
		} else {
			$url = murl('entry', array('eid'=>$version_info['entry_id']), true, true);
		}
	}
	if ($url) {
		setcookie(session_name(), $_W['session_id']);
		header('Location:' . $url);
		exit;
	}
	//跳转到错误页面
	message('找不到模块入口', 'refresh', 'error');
}


if ($do == 'package_app') {
	$version = trim($_GPC['v']);
	$version = '1.01';
	$version_info = wxapp_version_by_version($version);

	$version_info['modules'] = array_map(function($module) {
		 $module['url'] = murl('entry', array('eid'=>$module['defaultentry']), true, true);
		 return $module;
	}, $version_info['modules']);



	$version_info['quickmenu']['menus'] = array_map(function($menu){
		 $menu['url'] = murl('entry', array('eid'=>$menu['defaultentry']), true, true);
		 return $menu;
	}, $version_info['quickmenu']['menus']);

	template('wxapp/wxapp');
}


if ($do == 'wxappweb_pay') {
	$site = WeUtility::createModuleWxapp('wxappweb');
	$site->doPagePay();
}

if ($do == 'wxappweb_pay_result') {
	$site = WeUtility::createModuleWxapp('wxappweb');
	$site->doPagePayResult();
}