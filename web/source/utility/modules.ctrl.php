<?php
/**
 * 素材管理
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
error_reporting(0);
$dos = array('list');
if (!in_array($do, array('list'))) {
	exit('Access Denied');
}
if($do == 'list') {
	load()->model('module');
	$installedmodulelist = uni_modules(false);
	foreach ($installedmodulelist as $k => &$value) {
		$value['official'] = empty($value['issystem']) && (strexists($value['author'], 'WeEngine Team') || strexists($value['author'], '微擎团队'));
	}
	foreach($installedmodulelist as $name => $module) {
		// if ((empty($_W['setting']['permurls']['modules']) && !in_array($name, $_W['setting']['permurls']['modules'])) || empty($module['isdisplay'])) {
		// 	continue;
		// }
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
	$psize = 10;
	$arr = array_slice($enable_modules, $pindex * $psize, $psize);
	$result = array(
		'items' => $arr,
		'pager' => pagination(count($enable_modules), $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null')),
	);
	message($result, '', 'ajax');
	// $type = trim($_GPC['type']) == 'all' ? '' : trim($_GPC['type']);
	
	// if(!empty($type)) {
	// 	$condition = " WHERE uniacid = :uniacid AND status = 1 AND module = :module";
	// 	$params = array(':uniacid' => $_W['uniacid'], ':module' => $type);
	// }else {
	// 	$condition = " WHERE uniacid = :uniacid AND status = 1";
	// 	$params = array(':uniacid' => $_W['uniacid']);
	// }

	// $pindex = max(1, intval($_GPC['page']));
	// $psize = 10;
	// $limit = " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ", {$psize}";

	// $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rule') . $condition, $params);
	// $lists = pdo_fetchall('SELECT * FROM ' . tablename('rule') . $condition . $limit, $params, 'id');
	// if(!empty($lists)) {
	// 	foreach($lists as &$row) {
	// 		if(!empty($type)) {
	// 			$row['child_items'] = pdo_getall('rule_keyword', array('uniacid' => $_W['uniacid'], 'rid' => $row['id'], 'status' => 1, 'module' => $type));
	// 		}else {
	// 			$row['child_items'] = pdo_getall('rule_keyword', array('uniacid' => $_W['uniacid'], 'rid' => $row['id'], 'status' => 1));
	// 		}
	// 	}
	// }
	// $result = array(
	// 	'items' => $lists,
	// 	'pager' => pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null')),
	// );
	message($result, '', 'ajax');
}
