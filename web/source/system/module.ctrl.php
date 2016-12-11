<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/29
 * Time: 18:03
 * 模块管理
 */

defined('IN_IA') or exit('Access Denied');

include_once IA_ROOT . '/framework/library/pinyin/pinyin.php';

$dos = array('install', 'installed', 'not_installed', 'recycle', 'uninstall', 'get_module_info', 'save_module_info', 'module_detail', 'change_receive_ban');
$do = in_array($do, $dos) ? $do : 'installed';

load()->model('extension');
load()->model('cloud');
load()->model('cache');
load()->model('module');
load()->model('account');

if ($do =='install') {
	$points = ext_module_bindings();
	$module_name = trim($_GPC['module_name']);
	$is_recycle_module = pdo_get('modules_recycle', array('modulename' => $module_name));
	if (!empty($is_recycle_module)) {
		pdo_delete('modules_recycle', array('modulename' => $module_name));
	}

	if (empty($_W['isfounder'])) {
		message('您没有安装模块的权限', '', 'error');
	}
	if (pdo_getcolumn('modules', array('name' => $module_name), 'mid')) {
		message('模块已经安装或是唯一标识已存在！', '', 'error');
	}

	$manifest = ext_module_manifest($module_name);
	if (!empty($manifest)) {
		$result = cloud_m_prepare($module_name);
		if (is_error($result)) {
			message($result['message'], url('system/module/not_installed'), 'error');
		}
	} else {
		$result = cloud_prepare();
		if (is_error($result)) {
			message($result['message'], url('cloud/profile'), 'error');
		}
		$module_info = cloud_m_info($module_name);
		if (!is_error($module_info)) {
			if (empty($_GPC['flag'])) {
				header('location: ' . url('cloud/process', array('m' => $module_name)));
				exit;
			} else {
				define('ONLINE_MODULE', true);
				$packet = cloud_m_build($modulename);
				$manifest = ext_module_manifest_parse($packet['manifest']);
			}
		} else {
			message($module_info['message'], '', 'error');
		}
	}
	if (empty($manifest)) {
		message('模块安装配置文件不存在或是格式不正确，请刷新重试！', '', 'error');
	}
	$check_manifest_result = manifest_check($module_name, $manifest);
	if (is_error($check_manifest_result)) {
		message($check_manifest_result['message'], '', 'error');
	}
	$module_path = IA_ROOT . '/addons/' . $module_name . '/';
	if (!file_exists($module_path . 'processor.php') && !file_exists($module_path . 'module.php') && !file_exists($module_path . 'receiver.php') && !file_exists($module_path . 'site.php')) {
		message('模块缺失文件，请检查模块文件中site.php, processor.php, module.php, receiver.php 文件是否存在！', '', 'error');
	}

	$module = ext_module_convert($manifest);
	$module_group = uni_groups();
	if (!$_W['ispost'] || empty($_GPC['flag'])) {
		template('system/select_module_group');
		exit;
	}
	$post_groups = $_GPC['group'];
	ext_module_clean($module_name);
	$bindings = array_elements(array_keys($points), $module, false);
	if (!empty($points)) {
		foreach ($points as $name => $point) {
			unset($module[$name]);
			if (is_array($bindings[$name]) && !empty($bindings[$name])) {
				foreach ($bindings[$name] as $entry) {
					$entry['module'] = $manifest['application']['identifie'];
					$entry['entry'] = $name;
					pdo_insert('modules_bindings', $entry);
				}
			}
		}
	}
	$module['permissions'] = iserializer($module['permissions']);
	$module_subscribe_success = true;
	if (!empty($module['subscribes'])) {
		$subscribes = iunserializer($module['subscribes']);
		if (!empty($subscribes)) {
			$module_subscribe_success = ext_check_module_subscribe($module['name']);
		}
	}
	if (!empty($module_info['version']['cloud_setting'])) {
		$module['settings'] = 2;
	}
	if (pdo_insert('modules', $module)) {
		if (strexists($manifest['install'], '.php')) {
			if (file_exists($module_path . $manifest['install'])) {
				include_once $module_path . $manifest['install'];
			}
		} else {
			pdo_run($manifest['install']);
		}
		update_handle($module['name']);
		// 如果模块来自应用商城，删除对应文件
		if (defined('ONLINE_MODULE')) {
			ext_module_script_clean($module['name'], $manifest);
		}
		if ($_GPC['flag'] && !empty($post_groups) && $module['name']) {
			foreach ($post_groups as $groupid) {
				$group_info = pdo_get('uni_group', array('id' => intval($groupid)), array('id', 'name', 'modules'));
				if (empty($group_info)) {
					continue;
				}
				$group_info['modules'] = iunserializer($group_info['modules']);
				if (in_array($module['name'], $group_info['modules'])) {
					continue;
				}
				$group_info['modules'][] = $module['name'];
				$group_info['modules'] = iserializer($group_info['modules']);
				pdo_update('uni_group', $group_info, array('id' => $groupid));
			}
		}
		module_build_privileges();
		cache_build_module_subscribe_type();
		cache_build_account_modules();
		if (empty($module_subscribe_success)) {
			message('模块安装成功, 请按照【公众号服务套餐】【用户组】来分配权限！模块订阅消息有错误，系统已禁用该模块的订阅消息，详细信息请查看 <div><a class="btn btn-primary" style="width:80px;" href="' . url('system/module/module_detail', array('name' => $module['name'])) . '">订阅管理</a> &nbsp;&nbsp;<a class="btn btn-default" href="' . url('extension/module') . '">返回模块列表</a></div>', '', 'tips');
		} else {
			message('模块安装成功, 请按照【公众号服务套餐】【用户组】来分配权限！', url('system/module'), 'success');
		}
	} else {
		message('模块安装失败, 请联系模块开发者！');
	}
}

if ($do == 'change_receive_ban') {
	$modulename = $_GPC['__input']['modulename'];
	if (!is_array($_W['setting']['module_receive_ban'])) {
		$_W['setting']['module_receive_ban'] = array();
	}
	if (in_array($modulename, $_W['setting']['module_receive_ban'])) {
		unset($_W['setting']['module_receive_ban'][$modulename]);
	} else {
		$_W['setting']['module_receive_ban'][$modulename] = $modulename;
	}
	setting_save($_W['setting']['module_receive_ban'], 'module_receive_ban');
	cache_build_module_subscribe_type();
	message(error(0), '', 'ajax');
}

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

if ($do == 'module_detail') {
	load()->classs('account');
	$_W['page']['title'] = '模块详情';
	$module_name = trim($_GPC['name']);
	$module_info = pdo_get('modules', array('name' => $module_name));
	$module_info['logo'] = file_exists(IA_ROOT. "/addons/". $module_info['name']. "/icon-custom.jpg") ? IA_ROOT. "/addons/". $module_info['name']. "/icon-custom.jpg" : IA_ROOT. "/addons/". $module_info['name']. "/icon.jpg";
	$module_group_list = pdo_getall('uni_group', array('uniacid' => 0));
	$module_group = array();
	if (!empty($module_group_list)) {
		foreach ($module_group_list as $group) {
			$group['modules'] = iunserializer($group['modules']);
			if (in_array($module_name, $group['modules'])) {
				$module_group[] = $group;
			}
		}
	}

	//模块订阅消息
	$module_subscribes = array();
	$module['subscribes'] = iunserializer($module_info['subscribes']);
	if (!empty($module['subscribes'])) {
		foreach ($module['subscribes'] as $event) {
			if ($event == 'text' || $event == 'enter') {
				continue;
			}
			$module_subscribes = $module['subscribes'];
		}
	}
	$mtypes = ext_module_msg_types();
	$module_ban = $_W['setting']['module_receive_ban'];
	if (!is_array($module_ban)) {
		$module_ban = array();
	}
	$receive_ban = in_array($module_info['name'], $module_ban) ? 1 : 2;
	$modulename = $_GPC['modulename'];

	//验证订阅消息是否成功
	$check_subscribe = 0;
	$module_obj = WeUtility::createModuleReceiver($module_name);
	if (!empty($module_obj)) {
		$module_obj->uniacid = $_W['uniacid'];
		$module_obj->acid = $_W['acid'];
		$module_obj->message = array(
			'event' => 'subscribe'
		);
		if(method_exists($module_obj, 'receive')) {
			$module_obj->receive();
			$check_subscribe = 1;
		}
	}

	//可以使用此模块的公众号
	$pageindex = max(1, $_GPC['page']);
	$pagesize = 10;
	$use_module_account = array();
	$uniaccount_list = pdo_getall('uni_account');
	if (!empty($uniaccount_list)) {
		foreach($uniaccount_list as $uniaccount) {
			$uniaccount_have_module = pdo_getall('uni_account_modules', array('uniacid' => $_W['uniacid']), array(), 'module');
			$uniaccount_have_module = array_keys($uniaccount_have_module);
			if (in_array($module_info['name'], $uniaccount_have_module)) {
				$uniaccount_info = account_fetch($uniaccount['default_acid']);
				$use_module_account[] = $uniaccount_info;
			}
		}
	}
	$total = count($use_module_account);
	$use_module_account = array_slice($use_module_account, ($pageindex - 1) * $pagesize, $pagesize);
	$pager = pagination($total, $pageindex, $pagesize);
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
	$module_list = pdo_fetchall("SELECT * FROM ". tablename('modules'). $condition. " ORDER BY `issystem` DESC, `mid` DESC". " LIMIT ".($pageindex-1)*$pagesize.", ". $pagesize, $params, 'name');
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

if ($do == 'not_installed') {
	$_W['page']['title'] = '安装模块 - 模块 - 扩展';

	$status = $_GPC['status'] == ''? 'uninstalled' : 'recycle';
	$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
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
				$pinyin = new Pinyin_Pinyin();
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
}

template('system/module');