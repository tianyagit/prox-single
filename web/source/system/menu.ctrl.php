<?php
/**
 * 后台菜单管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'del', 'ajax', 'module', 'view', 'switch', 'del_bind');
$do = in_array($do, $dos) ? $do : 'display';
$_W['page']['title'] = '系统管理 - 菜单设置';

if($do == 'display') {
	$system_menu = require_once IA_ROOT . '/web/common/frames.inc.php';
	if (!empty($system_menu)) {
		foreach ($system_menu as $menu_name => $menu) {
			$system_menu[$menu_name]['is_system'] = true;
			$system_menu[$menu_name]['is_display'] = true;
			foreach ($menu['section'] as $section_name => $section) {
				foreach ($section['menu']  as $permission_name => $sub_menu) {
					$system_menu[$menu_name]['section'][$section_name]['menu'][$permission_name]['is_system'] = true;
					$system_menu[$menu_name]['section'][$section_name]['menu'][$permission_name]['is_display'] = true;
				}
			}
		}
	}
	//print_r($system_menu);exit;
	template('system/menu');
}

if($do == 'del') {
	$id = intval($_GPC['id']);
	$menu= pdo_fetch('SELECT * FROM ' . tablename('core_menu') . ' WHERE id = :id', array(':id' => $id));
	if($menu['is_system']) {
		message('系统分类不能删除', referer(), 'error');
	}
	$ids = pdo_fetchall('SELECT id FROM ' . tablename('core_menu') . ' WHERE pid = :id', array(':id' => $id), 'id');
	if(!empty($ids)) {
		$ids_str = implode(',', array_keys($ids));
		pdo_query('DELETE FROM ' . tablename('core_menu') . " WHERE pid IN ({$ids_str})");
		pdo_query('DELETE FROM ' . tablename('core_menu') . " WHERE id IN ({$ids_str})");
	}
	pdo_query('DELETE FROM ' . tablename('core_menu') . " WHERE id = {$id}");
	cache_build_frame_menu();
	message('删除分类成功', referer(), 'success');
}

if($do == 'ajax') {
	$id = intval($_GPC['id']);
	$value = intval($_GPC['value']) ? 0 : 1;
	pdo_update('core_menu', array('is_display' => $value), array('id' => $id));
	cache_build_frame_menu();
	exit();
}

if($do == 'del_bind') {
	$eid = intval($_GPC['eid']);
	$permission = intval($_GPC['permission']);
	pdo_delete('modules_bindings', array('eid' => $eid, 'entry' => 'mine'));
	exit();
}