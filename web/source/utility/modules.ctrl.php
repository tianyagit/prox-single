<?php
/**
 * 素材管理
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
error_reporting(0);
load()->model('module');

$dos = array('list');
if (!in_array($do, array('list'))) {
	exit('Access Denied');
}

if($do == 'list') {
	$installedmodulelist = uni_modules(false);
	foreach ($installedmodulelist as $k => $value) {
		$installedmodulelist[$k]['official'] = empty($value['issystem']) && (strexists($value['author'], 'WeEngine Team') || strexists($value['author'], '微擎团队'));
	}
	foreach($installedmodulelist as $name => $module) {
		if($module['issystem']) {
			$path = '/framework/builtin/' . $module['name'];
		} else {
			$path = '../addons/' . $module['name'];
		}
		$cion = $path . '/icon-custom.jpg';
		if(!file_exists($cion)) {
			$cion = $path . '/icon.jpg';
			if(!file_exists($cion)) {
				$cion = './resource/images/nopic-small.jpg';
			}
		}
		$module['icon'] = $cion;
		if($module['enabled'] == 1) {
			$enable_modules[$name] = $module;
		} else {
			$unenable_modules[$name] = $module;
		}
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 30;
	$current_module_list = array_slice($enable_modules, $pindex * $psize, $psize);
	$result = array(
		'items' => $current_module_list,
		'pager' => pagination(count($enable_modules), $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null')),
	);
	message($result, '', 'ajax');
}