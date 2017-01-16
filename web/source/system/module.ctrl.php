<?php
/**
 * 模块管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

load()->model('extension');
load()->model('cloud');
load()->model('cache');
load()->model('module');
load()->model('account');

include_once IA_ROOT . '/framework/library/pinyin/pinyin.php';

$dos = array('check_upgrade', 'upgrade', 'install', 'installed', 'not_installed', 'recycle', 'uninstall', 'get_module_info', 'save_module_info', 'module_detail', 'change_receive_ban');
$do = in_array($do, $dos) ? $do : 'installed';

//只有创始人、主管理员、管理员才有权限
if ($_W['role'] != ACCOUNT_MANAGE_NAME_OWNER && $_W['role'] != ACCOUNT_MANAGE_NAME_MANAGER && $_W['role'] != ACCOUNT_MANAGE_NAME_FOUNDER) {
	message('无权限操作！', referer(), 'error');
}

if ($do == 'check_upgrade') {
	$module_list = $_GPC['module_list'];//test
	if (!empty($module_list) && is_array($module_list)) {
		foreach ($module_list as &$module) {
			$module = pdo_get('modules', array('name' => $module));
		}
		unset($module);
	} else {
		message(error(0), '', 'ajax');
	}
	$cloud_prepare_result = cloud_prepare();
	$cloud_m_query_module = cloud_m_query();
	if (is_error($cloud_m_query_module)) {
		$cloud_m_query_module = array();
	}
	foreach ($module_list as &$module) {
		$manifest = ext_module_manifest($module['name']);
		if (!empty($manifest)&& is_array($manifest)) {
			if (ver_compare($module['version'], $manifest['application']['version']) == '-1') {
				$module['upgrade'] = true;
			} else {
				$module['upgrade'] = false;
			}
			$module['from'] = 'local';
		}

		if (empty($manifest)) {
			if (in_array($module['name'], array_keys($cloud_m_query_module))) {
				$cloud_m_upgrade_info = cloud_m_upgradeinfo($module['name']);//获取模块更新信息
				if (!empty($cloud_m_upgrade_info['branches'])) {
					foreach ($cloud_m_upgrade_info['branches'] as &$branch) {
						$branch['version']['description'] = $branch['version']['description'];
					}
					unset($branch);
				}
				$module['upgrade_info'] = array(
					'version' => $cloud_m_upgrade_info['version'],
					'name' => $cloud_m_upgrade_info['name'],
					'branches' => $cloud_m_upgrade_info['branches'],
					'site_branch' => $cloud_m_upgrade_info['branches'][$cloud_m_upgrade_info['version']['branch_id']],
				);
				$module['from'] = 'cloud';
				$site_branch = $cloud_m_upgrade_info['version']['branch_id'];//当前站点模块分之号
				$cloud_branch_version = $cloud_m_upgrade_info['branches'][$site_branch]['version']['version'];//云服务模块分之版本号
				$best_branch = current($cloud_m_upgrade_info['branches']);
				if (ver_compare($module['version'], $cloud_branch_version) == -1 || ($cloud_m_upgrade_info['version']['branch_id'] < $best_branch['id'] && !empty($cloud_m_upgrade_info['version']['branch_id']))) {
					$module['upgrade'] = true;
				} else {
					$module['upgrade'] = false;
				}
			}
		}
	}
	unset($module);
	message(error(0, $module_list), '', 'ajax');
}

if ($do == 'upgrade') {
	$points = ext_module_bindings();
	$module_name = addslashes($_GPC['module_name']);
	//判断模块相关配置和文件是否合法
	$module_info = pdo_get('modules', array('name' => $module_name));
	if (empty($module_info)) {
		message('模块已经被卸载或是不存在！', '', 'error');
	}
	$manifest = ext_module_manifest($module_name);
	// 应用商城下载的模块远程获取XML文件
	if (empty($manifest)) {
		$cloud_prepare = cloud_prepare();
		if (is_error($cloud_prepare)) {
			message($cloud_prepare['message'], '', 'ajax');
		}
		$module_info = cloud_m_upgradeinfo($module_name);
		if (is_error($module_info)) {
			message($module_info, '', 'ajax');
		}
		if (!empty($_GPC['flag'])) {
			define('ONLINE_MODULE', true);
			$packet = cloud_m_build($module_name);
			$manifest = ext_module_manifest_parse($packet['manifest']);
		}
	}
	if (empty($manifest)) {
		message('模块安装配置文件不存在或是格式不正确！', '', 'error');
	}
	$check_manifest_result = manifest_check($module_name, $manifest);
	if (is_error($check_manifest_result)) {
		message($check_manifest_result['message'], '', 'error');
	}
	$module_path = IA_ROOT . '/addons/' . $module_name . '/';
	if (!file_exists($module_path . 'processor.php') && !file_exists($module_path . 'module.php') && !file_exists($module_path . 'receiver.php') && !file_exists($module_path . 'site.php')) {
		message('模块缺失文件，请检查模块文件中site.php, processor.php, module.php, receiver.php 文件是否存在！', '', 'error');
	}

	//处理模块菜单
	$module = ext_module_convert($manifest);
	unset($module['name']);
	unset($module['id']);
	$bindings = array_elements(array_keys($points), $module, false);
	foreach ($points as $point_name => $point_info) {
		unset($module[$point_name]);
		if (is_array($bindings[$point_name]) && !empty($bindings[$point_name])) {
			foreach ($bindings[$point_name] as $entry) {
				$entry['module'] = $manifest['application']['identifie'];
				$entry['entry'] = $point_name;
				if ($entry['title'] && $entry['do']) {
					//保存xml里面包含的do和title,最后删除数据库中废弃的do和title
					$not_delete_do[] = $entry['do'];
					$not_delete_title[] = $entry['title'];
					$module_binding = pdo_get('modules_bindings',array('module' => $manifest['application']['identifie'], 'entry' => $point_name, 'title' => $entry['title'], 'do' => $entry['do']));
					if (!empty($module_binding)) {
						pdo_update('modules_bindings', $entry, array('eid' => $module_binding['eid']));
						continue;
					}
				} elseif ($entry['call']) {
					$not_delete_call[] = $entry['call'];
					$module_binding = pdo_get('modules_bindings',array('module' => $manifest['application']['identifie'], 'entry' => $point_name, 'call' => $entry['call']));
					if (!empty($module_binding)) {
						pdo_update('modules_bindings', $entry, array('eid' => $module_binding['eid']));
						continue;
					}
				}
				pdo_insert('modules_bindings', $entry);
			}
			//删除不存在的do和title
			if (!empty($not_delete_do)) {
				pdo_query('DELETE FROM ' . tablename('modules_bindings') . " WHERE module = :module AND entry = :entry AND `call` = '' AND do NOT IN ('" . implode("','", $not_delete_do) . "')", array(':module' => $manifest['application']['identifie'], ':entry' => $point_name));
				unset($not_delete_do);
			}
			if (!empty($not_delete_title)) {
				pdo_query('DELETE FROM ' . tablename('modules_bindings') . " WHERE module = :module AND entry = :entry AND `call` = '' AND title NOT IN ('" . implode("','", $not_delete_title) . "')", array(':module' => $manifest['application']['identifie'], ':entry' => $point_name));
				unset($not_delete_title);
			}
			if (!empty($not_delete_call)) {
				pdo_query('DELETE FROM ' . tablename('modules_bindings') . " WHERE module = :module AND  entry = :entry AND do = '' AND title = '' AND `call` NOT IN ('" . implode("','", $not_delete_call) . "')", array(':module' => $manifest['application']['identifie'], ':entry' => $point_name));
				unset($not_delete_call);
			}
		}
	}

	//执行模块更新文件
	if (!empty($manifest['upgrade'])) {
		if (strexists($manifest['upgrade'], '.php')) {
			if (file_exists($module_path . $manifest['upgrade'])) {
				include_once $module_path . $manifest['upgrade'];
			}
		} else {
			pdo_run($manifest['upgrade']);
		}
	}

	$module['permissions'] = iserializer($module['permissions']);
	if (!empty($info['version']['cloud_setting'])) {
		$module['settings'] = 2;
	} else {
		$module['settings'] = empty($module['settings']) ? 0 : 1;
	}
	pdo_update('modules', $module, array('name' => $module_name));
	cache_build_account_modules();
	if (!empty($module['subscribes'])) {
		ext_check_module_subscribe($module['name']);
	}
	cache_delete('cloud:transtoken');
	message('模块更新成功！', url('system/module'), 'success');
}

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
				$packet = cloud_m_build($module_name);
				$manifest = ext_module_manifest_parse($packet['manifest']);
			}
		} else {
			message($module_info['message'], '', 'error');
		}
	}
	if (empty($manifest)) {
		message('模块安装配置文件不存在或是格式不正确，请刷新重试！', url('system/module/not_installed'), 'error');
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
		template('system/select-module-group');
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
		cache_build_uninstalled_module('uninstalled');
		cache_build_uninstalled_module('recycle');
		if (empty($module_subscribe_success)) {
			message('模块安装成功！模块订阅消息有错误，系统已禁用该模块的订阅消息，详细信息请查看 <div><a class="btn btn-primary" style="width:80px;" href="' . url('system/module/module_detail', array('name' => $module['name'])) . '">订阅管理</a> &nbsp;&nbsp;<a class="btn btn-default" href="' . url('system/module') . '">返回模块列表</a></div>', '', 'tips');
		} else {
			message('模块安装成功!', url('system/module'), 'success');
		}
	} else {
		message('模块安装失败, 请联系模块开发者！');
	}
}

if ($do == 'change_receive_ban') {
	$modulename = $_GPC['modulename'];
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
	$module_info = $_GPC['moduleinfo'];
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
	$mid = intval($_GPC['mid']);
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
	@$module_obj = WeUtility::createModuleReceiver($module_name);
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
	$pagesize = 20;
	$use_module_account = array();
/*	$uniaccount_list = pdo_getall('uni_account');
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
*/
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
		pdo_insert('modules_recycle', array('modulename' => $module['name']));

		ext_module_clean($name, $_GPC['confirm'] == '1');
		cache_build_account_modules();
		cache_build_module_subscribe_type();
		cache_build_uninstalled_module('uninstalled');
		cache_build_uninstalled_module('recycle');

		message('模块已放入回收站！', url('system/module'), 'success');
	}
}

if ($do == 'recycle') {
	$operate = $_GPC['operate'];
	$name = trim($_GPC['name']);
	cache_build_uninstalled_module('uninstalled');
	cache_build_uninstalled_module('recycle');
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
	$total_uninstalled = module_count_unistalled_module('uninstalled');
	$pageindex = max($_GPC['page'], 1);
	$pagesize = 10;
	$letter = $_GPC['letter'];
	$title = $_GPC['title'];
	$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

	$condition = " WHERE issystem = 0 OR name = 'we7_coupon' ";
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
	if (empty($_W['isfounder'])) {
		$user_info = pdo_get('users', array('uid' => $_W['uid']));
		$user_group = pdo_get('users_group', array('id' => $user_info['groupid']));
		$user_group['package'] = iunserializer($user_group['package']);
		if (!empty($user_group['package']) && is_array($user_group['package']) && !in_array('-1', $user_group)) {
			$user_have_group = array();
			foreach ($user_group['package'] as $groupid) {
				$group = pdo_get('uni_group', array('id' => $groupid));
				$group['modules'] = iunserializer($group['modules']);
				if (!empty($group['modules']) && is_array($group['modules'])) {
					$user_have_group = array_merge($user_have_group, $group['modules']);
				}
			}
			unset($group);
			if (!empty($user_have_group) && is_array($user_have_group)) {
				$condition .= " AND name in ". "('". implode("','", $user_have_group). "')";
			} else {
				message('没有可用模块', referer(), 'info');
			}
		}
	}
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ". tablename('modules'). $condition, $params);
	$module_list = pdo_fetchall("SELECT * FROM ". tablename('modules'). $condition. " ORDER BY `issystem` DESC, `mid` DESC". " LIMIT ".($pageindex-1)*$pagesize.", ". $pagesize, $params, 'name');
	$pager = pagination($total, $pageindex, $pagesize);
	if (!empty($module_list)) {
//		$account_list = pdo_getall('uni_account');
		foreach ($module_list as &$module) {
			$module['use_account'] = 0;
			$module['enabled_use_account'] = 0;
//			foreach ($account_list as $account) {
//				$account_have_module = pdo_get('uni_account_modules', array('uniacid' => $_W['uniacid'], 'module' => $module['name']));
//				$module['use_account'] = empty($account_have_module) ? $module['use_account'] : $module['use_account'] + 1;
//				$module['enabled_use_account'] = empty($account_have_module['enabled']) ? $module['enabled_use_account'] : $module['enabled_use_account'] +1;
//			}
			if (file_exists(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg')) {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg'). "?v=". time();
			} else {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon.jpg'). "?v=". time();
			}
		}
		unset($module);
	}
}

if ($do == 'not_installed') {
	if (empty($_W['isfounder'])) {
		message('非法访问！', referer(), 'info');
	}
	$_W['page']['title'] = '安装模块 - 模块 - 扩展';

	$status = $_GPC['status'] == 'recycle'? 'recycle' : 'uninstalled';
	$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	$title = $_GPC['title'];
	$letter = $_GPC['letter'];
	$pageindex = max($_GPC['page'], 1);
	$pagesize = 10;

	$recycle_modules = pdo_getall('modules_recycle', array(), array(), 'modulename');
	$recycle_modules = array_keys($recycle_modules);
	$total_uninstalled = module_count_unistalled_module();
	$uninstallModules = module_get_all_unistalled($status);
	if (!empty($uninstallModules)) {
		foreach($uninstallModules as $name => &$module) {
			if (!empty($letter) && strlen($letter) == 1) {
				$pinyin = new Pinyin_Pinyin();
				$first_char = $pinyin->get_first_char($module['title']);
				if ($letter != $first_char) {
					unset($uninstallModules[$name]);
					continue;
				}
			}
			if (!empty($title)) {
				if (!strexists($module['title'], $title)) {
					unset($uninstallModules[$name]);
					continue;
				}
			}
			if (file_exists(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg')) {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg');
			} elseif (file_exists(IA_ROOT.'/addons/'.$module['name'].'/icon.jpg')) {
				$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon.jpg');
			} else {
				$module['logo'] = tomedia($module['thumb']);
			}
		}
	}
	$total = count($uninstallModules);
	$uninstallModules = array_slice($uninstallModules, ($pageindex - 1)*$pagesize, $pagesize);
	$pager = pagination($total, $pageindex, $pagesize);
}

template('system/module');