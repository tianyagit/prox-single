<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/29
 * Time: 18:03
 * 模块管理
 */

defined('IN_IA') or exit('Access Denied');

load()->model('extension');
load()->model('cloud');
load()->model('cache');
load()->func('file');
load()->model('module');

$dos = array('installed', 'not_installed');
$do = in_array($do, $dos) ? $do : 'installed';
if ($do == 'installed') {
	$_W['page']['title'] = '应用列表';

	$localUninstallModules = get_all_unistalled_module('unistalled');
	$total_uninstalled = count($localUninstallModules);

	$pageindex = max($_GPC['page'], 1);
	$pagesize = 15;
	$letter = $_GPC['letter'];
	$title = $_GPC['title'];
	$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

	$condition = " WHERE issystem = 0 ";
	$params = array();

	if (!empty($letter) && strlen($letter) == 1) {
		if(in_array($letter, $letters)){
			$condition .= " AND `title_initial` = :letter";
		}else {
			$condition .= " AND `title_initial` NOT IN ('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z')";
		}
		$params[':letter'] = $letter;
	}
	if (!empty($title)) {
		$condition .= " AND title LIKE :title";
		$params[':title'] = "%".$title. "%";
	}
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ". tablename('modules'). $condition, $params);
	$module_list = pdo_fetchall("SELECT * FROM ". tablename('modules'). $condition. " ORDER BY `issystem` DESC, `mid` ASC". " LIMIT ".($pageindex-1)*$pagesize.", ". $pagesize, $params);
	$pager = pagination($total, $pageindex, $pagesize);
	if (!empty($module_list)) {
		foreach ($module_list as &$module) {
			if(file_exists(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg')) {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg');
			} else {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon.jpg');
			}
			$manifest = ext_module_manifest($module['name']);
			if(is_array($manifest) && ver_compare($module['version'], $manifest['application']['version']) == '-1') {
				$module['upgrade'] = true;
			}
		}
	}
}
if ($do == 'not_installed') {
	$_W['page']['title'] = '安装模块 - 模块 - 扩展';
	include IA_ROOT . '/framework/library/pinyin/pinyin.php';
	$pinyin = new Pinyin_Pinyin();

	$status = $_GPC['status'] == ''? 'unistalled' : 'recycle';
	$title = $_GPC['title'];
	$letter = $_GPC['letter'];
	$pageindex = max($_GPC['page'], 1);
	$pagesize = 2;

	$recycle_modules = pdo_getall('modules_recycle', array(), array(), 'modulename');
	$recycle_modules = array_keys($recycle_modules);
	$all_uninstalled = get_all_unistalled_module('uninstalled');
	$total_uninstalled = count($all_uninstalled);
	$localUninstallModules = get_all_unistalled_module($status);
	if (!empty($localUninstallModules)) {
		foreach($localUninstallModules as $name => $module) {
			if (!empty($title)) {
				if (!strexists($module['title'], $title)) {
					unset($localUninstallModules[$name]);
				}
			}
			if (!empty($letter) && strlen($letter) == 1) {
				$first_char = $pinyin->get_first_char($module['title']);
				if ($letter != $first_char) {
					unset($localUninstallModules[$name]);
				}
			}
		}
	}
	$total = count($localUninstallModules);
	$localUninstallModules = array_slice($localUninstallModules, ($pageindex - 1)*$pagesize, $pagesize);
	$pager = pagination($total, $pageindex, $pagesize);
	$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
}
template('system/module');