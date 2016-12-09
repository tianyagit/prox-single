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

$dos = array('installed', 'not_installed', 'recycle', 'uninstall', 'get_module_info', 'save_module_info');
$do = in_array($do, $dos) ? $do : 'installed';

if ($do == 'save_module_info') {
	$module_info = $_GPC['__input']['moduleinfo'];
	if (!empty($module_info['logo'])) {
		$image = file_get_contents ($module_info['logo']);
		$icon = file_exists (IA_ROOT . "/addons/" . $module_info['name'] . "/icon-custom.jpg") ? 'icon-custom.jpg' : 'icon.jpg';
		$result = file_put_contents (IA_ROOT . "/addons/" . $module_info['name']."/".$icon, $image);
	}
	if (!empty($module_info['preview'])) {
		$image = file_get_contents($module_info['preview']);
		$preview = file_exists(IA_ROOT."/addons/".$module_info['name']. "/preview-custom.jpg") ? 'preview-custom.jpg' : 'preview.jpg';
		$result = file_put_contents(IA_ROOT."/addons/".$module_info['name']."/".$preview, $image);
	}
	unset($module_info['logo'], $module_info['preview']);
	$data = array(
		'title' => $module_info['title'],
		'ability' => $module_info['ability'],
		'description' => $module_info['description'],
	);
	$result =  pdo_update('modules', $data, array('mid' => $module_info['mid']));
	message(error(0), '', 'ajax');
}

if ($do == 'get_module_info') {
	$mid = intval($_GPC['__input']['mid']);
	if ($mid) {
		$module = pdo_get('modules', array('mid' => $mid));
		if (file_exists(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg')) {
			$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg');
		} else {
			$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon.jpg');
		}
		if (file_exists(IA_ROOT.'/addons/'.$module['name'].'/preview-custom.jpg')) {
			$module['preview'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/preview-custom.jpg');
		} else {
			$module['preview'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/preview.jpg');
		}
	}
	message(error(0, $module), '', 'ajax');
}

if ($do == 'uninstall') {
	if (empty($_W['isfounder'])) {
		message('您没有卸载模块的权限', '', 'error');
	}
	$name = trim($_GPC['name']);
	$module = pdo_get('modules', array('name' => $name), array('name', 'isrulefields', 'issystem', 'version'));
	if (empty($module)) {
		message('模块已经被卸载或是不存在！', '', 'error');
	}
	if (!empty($module['issystem'])) {
		message('系统模块不能卸载！', '', 'error');
	}
	if ($module['isrulefields'] && !isset($_GPC['confirm'])) {
		message('卸载模块时同时删除规则数据吗, 删除规则数据将同时删除相关规则的统计分析数据？<div><a class="btn btn-primary" style="width:80px;" href="' . url('system/module/uninstall', array('name' => $name, 'confirm' => 1)) . '">是</a> &nbsp;&nbsp;<a class="btn btn-default" style="width:80px;" href="' . url('system/module/uninstall', array('name' => $name, 'confirm' => 0)) . '">否</a></div>', '', 'tips');
	} else {
		$modulepath = IA_ROOT . '/addons/' . $name . '/';
		$manifest = ext_module_manifest($module['name']);
		if (empty($manifest)) {
			$r = cloud_prepare();
			if (is_error($r)) {
				message($r['message'], url('cloud/profile'), 'error');
			}
			$packet = cloud_m_build($module['name'], $do);
			if ($packet['sql']) {
				pdo_run(base64_decode($packet['sql']));
			} elseif ($packet['script']) {
				$uninstall_file = $modulepath . TIMESTAMP . '.php';
				file_put_contents($uninstall_file, base64_decode($packet['script']));
				require($uninstall_file);
				unlink($uninstall_file);
			}
		} elseif (!empty($manifest['uninstall'])) {
			if (strexists($manifest['uninstall'], '.php')) {
				if (file_exists($modulepath . $manifest['uninstall'])) {
					require($modulepath . $manifest['uninstall']);
				}
			} else {
				pdo_run($manifest['uninstall']);
			}
		}

		ext_module_clean($name, $_GPC['confirm'] == '1');

		cache_build_account_modules();

		cache_build_module_subscribe_type();

		pdo_insert('modules_recycle', array('modulename' => $module['name']));

		message('模块已放入回收站！', url('system/module'), 'success');
	}
}

if ($do == 'recycle') {
	$operate = $_GPC['operate'];
	$name = trim($_GPC['name']);
	if ($operate == 'delete') {
		pdo_insert('modules_recycle', array('modulename' => $name));
		message('模块已放入回收站', url('system/module/not_installed', array('status' => 'recycle')), 'success');
	} elseif ($operate == 'recover') {
		pdo_delete('modules_recycle', array('modulename' => $name));
		message('模块恢复成功', url('system/module/not_installed', array('m' => $name)), 'success');
	}
	template('system/module');
}

if ($do == 'installed') {
	$_W['page']['title'] = '应用列表';

	$localUninstallModules = get_all_unistalled_module('uninstalled');
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
	$module_list = pdo_fetchall("SELECT * FROM ". tablename('modules'). $condition. " ORDER BY `issystem` DESC, `mid` ASC". " LIMIT ".($pageindex-1)*$pagesize.", ". $pagesize, $params, 'name');
	$pager = pagination($total, $pageindex, $pagesize);
	if (!empty($module_list)) {
		foreach ($module_list as &$module) {
			if (file_exists(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg')) {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg');
			} else {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon.jpg');
			}
			$manifest = ext_module_manifest($module['name']);
			if (is_array($manifest) && ver_compare($module['version'], $manifest['application']['version']) == '-1') {
				$module['upgrade'] = true;
			}
		}
	}
}

load()->model('setting');

if ($do == 'not_installed') {
	$_W['page']['title'] = '安装模块 - 模块 - 扩展';

	$status = $_GPC['status'] == ''? 'uninstalled' : 'recycle';
	$title = $_GPC['title'];
	$letter = $_GPC['letter'];
	$pageindex = max($_GPC['page'], 1);
	$pagesize = 10;

	$recycle_modules = pdo_getall('modules_recycle', array(), array(), 'modulename');
	$recycle_modules = array_keys($recycle_modules);
	$all_uninstalled = get_all_unistalled_module('uninstalled');
	$total_uninstalled = count($all_uninstalled);
	$localUninstallModules = get_all_unistalled_module($status);
	if (!empty($localUninstallModules)) {
		foreach($localUninstallModules as $name => &$module) {
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
			if (file_exists(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg')) {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg');
			} else {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon.jpg');
			}
		}
	}
	$total = count($localUninstallModules);
	$localUninstallModules = array_slice($localUninstallModules, ($pageindex - 1)*$pagesize, $pagesize);
	$pager = pagination($total, $pageindex, $pagesize);
	$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
}
template('system/module');