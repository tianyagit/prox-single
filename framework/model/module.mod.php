<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 模块类型
 *
 * @return array
 */
function module_types() {
	static $types = array(
		'business' => array(
			'name' => 'business',
			'title' => '主要业务',
			'desc' => ''
		),
		'customer' => array(
			'name' => 'customer',
			'title' => '客户关系',
			'desc' => ''
		),
		'activity' => array(
			'name' => 'activity',
			'title' => '营销及活动',
			'desc' => ''
		),
		'services' => array(
			'name' => 'services',
			'title' => '常用服务及工具',
			'desc' => ''
		),
		'biz' => array(
			'name' => 'biz',
			'title' => '行业解决方案',
			'desc' => ''
		),
		'enterprise' => array(
			'name' => 'enterprise',
			'title' => '企业应用',
			'desc' => ''
		),
		'h5game' => array(
			'name' => 'h5game',
			'title' => 'H5游戏',
			'desc' => ''
		),
		'other' => array(
			'name' => 'other',
			'title' => '其他',
			'desc' => ''
		)
	);
	return $types;
}

/**
 * 获取指定模块的所有入口地址
 *
 * @param string $name 模块名称
 * @param string|array $types 入口类型
 * @param number $rid 规则编号
 * @param string $args 附加参数
 * @return array
 */
function module_entries($name, $types = array(), $rid = 0, $args = null) {
	global $_W;
	$ts = array('rule', 'cover', 'menu', 'home', 'profile', 'shortcut', 'function', 'mine');
	if(empty($types)) {
		$types = $ts;
	} else {
		$types = array_intersect($types, $ts);
	}
	$bindings = pdo_getall('modules_bindings', array('module' => $name, 'entry' => $types));
	$entries = array();
	foreach($bindings as $bind) {
		if(!empty($bind['call'])) {
			$extra = array();
			$extra['Host'] = $_SERVER['HTTP_HOST'];
			load()->func('communication');
			$urlset = parse_url($_W['siteurl']);
			$urlset = pathinfo($urlset['path']);
			$response = ihttp_request($_W['sitescheme'] . '127.0.0.1/'. $urlset['dirname'] . '/' . url('utility/bindcall', array('modulename' => $bind['module'], 'callname' => $bind['call'], 'args' => $args, 'uniacid' => $_W['uniacid'])), array(), $extra);
			if (is_error($response)) {
				continue;
			}
			$response = json_decode($response['content'], true);
			$ret = $response['message'];
			if(is_array($ret)) {
				foreach($ret as $et) {
					if (empty($et['url'])) {
						continue;
					}
					$et['url'] = $et['url'] . '&__title=' . urlencode($et['title']);
					$entries[$bind['entry']][] = array('title' => $et['title'], 'do' => $et['do'], 'url' => $et['url'], 'from' => 'call', 'icon' => $et['icon'], 'displayorder' => $et['displayorder']);
				}
			}
		} else {
			if($bind['entry'] == 'cover') {
				$url = murl('entry', array('eid' => $bind['eid']));
			}
			if($bind['entry'] == 'menu') {
				$url = wurl("site/entry", array('eid' => $bind['eid']));
			}
			if($bind['entry'] == 'mine') {
				$url = $bind['url'];
			}
			if($bind['entry'] == 'rule') {
				$par = array('eid' => $bind['eid']);
				if (!empty($rid)) {
					$par['id'] = $rid;
				}
				$url = wurl("site/entry", $par);
			}
			if($bind['entry'] == 'home') {
				$url = murl("entry", array('eid' => $bind['eid']));
			}
			if($bind['entry'] == 'profile') {
				$url = murl("entry", array('eid' => $bind['eid']));
			}
			if($bind['entry'] == 'shortcut') {
				$url = murl("entry", array('eid' => $bind['eid']));
			}
			if(empty($bind['icon'])) {
				$bind['icon'] = 'fa fa-puzzle-piece';
			}
			$entries[$bind['entry']][] = array('eid' => $bind['eid'], 'title' => $bind['title'], 'do' => $bind['do'], 'url' => $url, 'from' => 'define', 'icon' => $bind['icon'], 'displayorder' => $bind['displayorder'], 'direct' => $bind['direct']);
		}
	}
	return $entries;
}
/**
 * 专属生成APP端的入口地址
 */
function module_app_entries($name, $types = array(), $args = null) {
	global $_W;
	$ts = array('rule', 'cover', 'menu', 'home', 'profile', 'shortcut', 'function');
	if(empty($types)) {
		$types = $ts;
	} else {
		$types = array_intersect($types, $ts);
	}
	$bindings = pdo_getall('modules_bindings', array('module' => $name, 'entry' => $types));
	$entries = array();
	foreach($bindings as $bind) {
		if(!empty($bind['call'])) {
			$extra = array();
			$extra['Host'] = $_SERVER['HTTP_HOST'];
			load()->func('communication');
			$urlset = parse_url($_W['siteurl']);
			$urlset = pathinfo($urlset['path']);
			$response = ihttp_request($_W['sitescheme'] . '127.0.0.1/'. $urlset['dirname'] . '/' . url('utility/bindcall', array('modulename' => $bind['module'], 'callname' => $bind['call'], 'args' => $args, 'uniacid' => $_W['uniacid'])), array('W'=>base64_encode(iserializer($_W))), $extra);
			if (is_error($response)) {
				continue;
			}
			$response = json_decode($response['content'], true);
			$ret = $response['message'];
			if(is_array($ret)) {
				foreach($ret as $et) {
					$et['url'] = $et['url'] . '&__title=' . urlencode($et['title']);
					$entries[$bind['entry']][] = array('title' => $et['title'], 'url' => $et['url'], 'from' => 'call');
				}
			}
		} else {
			if($bind['entry'] == 'cover') {
				$url = murl("entry", array('eid' => $bind['eid']));
			}
			if($bind['entry'] == 'home') {
				$url = murl("entry", array('eid' => $bind['eid']));
			}
			if($bind['entry'] == 'profile') {
				$url = murl("entry", array('eid' => $bind['eid']));
			}
			if($bind['entry'] == 'shortcut') {
				$url = murl("entry", array('eid' => $bind['eid']));
			}
			$entries[$bind['entry']][] = array('title' => $bind['title'], 'do' => $bind['do'], 'url' => $url, 'from' => 'define');
		}
	}
	return $entries;
}

function module_entry($eid) {
	$sql = 'SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `eid`=:eid';
	$pars = array();
	$pars[':eid'] = $eid;
	$entry = pdo_fetch($sql, $pars);
	if(empty($entry)) {
		return error(1, '模块菜单不存在');
	}
	$module = module_fetch($entry['module']);
	if(empty($module)) {
		return error(2, '模块不存在');
	}
	$querystring = array(
		'do' => $entry['do'],
		'm' => $entry['module'],
	);
	if (!empty($entry['state'])) {
		$querystring['state'] = $entry['state'];
	}
	
	$entry['url'] = murl('entry', $querystring);
	$entry['url_show'] = murl('entry', $querystring, true, true);
	return $entry;
}

/**
 * 显示模块设置表单
 *
 * @param string $name
 * @param number $rid
 * @param array $option 模块显示隐藏设置
 * @return string
 */
function module_build_form($name, $rid, $option = array()) {
	$rid = intval($rid);
	$m = WeUtility::createModule($name);
	if(!empty($m)) {
		return $m->fieldsFormDisplay($rid, $option);
	}else {
		return null;
	}

}

/**
 * 获取指定模块及模块信息
 *
 * @param string $name 模块名称
 * @return array 模块信息
 */
function module_fetch($name) {
	global $_W;
	$cachekey = cache_system_key(CACHE_KEY_MODULE_INFO, $name);
	$module = cache_load($cachekey);
	if (empty($module)) {
		$module_info = pdo_get('modules', array('name' => $name));
		if (empty($module_info)) {
			return array();
		}
		if (!empty($module_info['subscribes'])) {
			$module_info['subscribes'] = (array)unserialize ($module_info['subscribes']);
		}
		if (!empty($module_info['handles'])) {
			$module_info['handles'] = (array)unserialize ($module_info['handles']);
		}
		$module_info['isdisplay'] = 1;

		if (file_exists (IA_ROOT . '/addons/' . $module_info['name'] . '/icon-custom.jpg')) {
			$module_info['logo'] = tomedia (IA_ROOT . '/addons/' . $module_info['name'] . '/icon-custom.jpg') . "?v=" . time ();
		} else {
			$module_info['logo'] = tomedia (IA_ROOT . '/addons/' . $module_info['name'] . '/icon.jpg') . "?v=" . time ();
		}

		$module_info['main_module'] = pdo_getcolumn ('modules_plugin', array ('name' => $module_info['name']), 'main_module');
		if (!empty($module_info['main_module'])) {
			$main_module_info = module_fetch ($module_info['main_module']);
			$module_info['main_module_logo'] = $main_module_info['logo'];
		} else {
			$module_info['plugin_list'] = pdo_getall ('modules_plugin', array ('main_module' => $module_info['name']), array (), 'name');
			if (!empty($module_info['plugin_list'])) {
				$module_info['plugin_list'] = array_keys ($module_info['plugin_list']);
			}
		}
		if ($module_info['app_support'] != 2 && $module_info['wxapp_support'] != 2) {
			$module_info['app_support'] = 2;
		}
		$module_ban = setting_load('module_ban');
		if (in_array($name, $module_ban['module_ban'])) {
			$module_info['is_ban'] = true;
		}
		$module_upgrade = setting_load('module_upgrade');
		if (in_array($name, array_keys($module_upgrade['module_upgrade']))) {
			$module_info['is_upgrade'] = true;
		}
		$module = $module_info;
		cache_write($cachekey, $module_info);
	}
	//有公众号时，附加模块配置信息
	if (!empty($module) && !empty($_W['uniacid'])) {
		$setting_cachekey = cache_system_key(CACHE_KEY_MODULE_SETTING, $_W['uniacid'], $name);
		$setting = cache_load($setting_cachekey);
		if (empty($setting)) {
			$setting = pdo_get('uni_account_modules', array('module' => $name, 'uniacid' => $_W['uniacid']));
			if (!empty($setting)) {
				cache_write($setting_cachekey, $setting);
			}
		}
		$module['config'] = !empty($setting['settings']) ? iunserializer($setting['settings']) : array();
		$module['enabled'] = $module['issystem'] || !isset($setting['enabled']) ? 1 : $setting['enabled'];
		$module['shortcut'] = $setting['shortcut'];
	}
	return $module;
}

/**
 * 检验并完善公众号的模块设置信息
 * 安装模块或添加公众号时调用.
 */
function module_build_privileges() {
	load()->model('account');
	$uniacid_arr = pdo_fetchall('SELECT uniacid FROM ' . tablename('uni_account'));
	foreach($uniacid_arr as $row){
		$modules = uni_modules(false);
		//得到模块标识
		$mymodules = pdo_getall('uni_account_modules', array('uniacid' => $row['uniacid']), array('module'), 'module');
		$mymodules = array_keys($mymodules);
		foreach($modules as $module){
			if(!in_array($module['name'], $mymodules) && empty($module['issystem'])) {
				$data = array();
				$data['uniacid'] = $row['uniacid'];
				$data['module'] = $module['name'];
				$data['enabled'] = 1;
				$data['settings'] = '';
				pdo_insert('uni_account_modules', $data);
			}
		}
	}
	return true;
}


/**
 * 获取所有未安装的模块
 * @param string $status 模块状态，unistalled : 未安装模块, recycle : 回收站模块;
 */
function module_get_all_unistalled($status)  {
	global $_GPC;
	load()->func('communication');
	load()->model('cloud');
	load()->classs('cloudapi');
	$status = $status == 'recycle' ? 'recycle' : 'uninstalled';
	$uninstallModules =  cache_load(cache_system_key('module:all_uninstall'));
	if ($_GPC['c'] == 'system' && $_GPC['a'] == 'module' && $_GPC['do'] == 'not_installed' && $status == 'uninstalled') {
		$cloud_api = new CloudApi();
		$get_cloud_m_count = $cloud_api->get('site', 'stat', array('module_quantity' => 1), 'json');
		$cloud_m_count = $get_cloud_m_count['module_quantity'];
	} else {
		if(is_array($uninstallModules)){
			$cloud_m_count = $uninstallModules['cloud_m_count'];
		}
	}
	if (empty($uninstallModules['modules']) || intval($uninstallModules['cloud_m_count']) !== intval($cloud_m_count) || is_error($get_cloud_m_count)) {
		$uninstallModules = cache_build_uninstalled_module();
	}
	if (ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL) {
		$uninstallModules['modules'] = (array)$uninstallModules['modules'][$status]['wxapp'];
		$uninstallModules['module_count'] = $uninstallModules['wxapp_count'];
		return $uninstallModules;
	} else {
		$uninstallModules['modules'] = (array)$uninstallModules['modules'][$status]['app'];
		$uninstallModules['module_count'] = $uninstallModules['app_count'];
		return $uninstallModules;
	}
}

/**
 * 获取某个模块的权限列表
 * @param string $name 模块标识
 */
function module_permission_fetch($name) {
	$module = module_fetch($name);
	$data = array();
	if ($module['permissions']) {
		$data[] = array('title' => '权限设置', 'permission' => $name.'_permissions');
	}
	if($module['settings']) {
		$data[] = array('title' => '参数设置', 'permission' => $name.'_settings');
	}
	if($module['isrulefields']) {
		$data[] = array('title' => '回复规则列表', 'permission' => $name.'_rule');
	}
	$entries = module_entries($name);
	if(!empty($entries['home'])) {
		$data[] = array('title' => '微站首页导航', 'permission' => $name.'_home');
	}
	if(!empty($entries['profile'])) {
		$data[] = array('title' => '个人中心导航', 'permission' => $name.'_profile');
	}
	if(!empty($entries['shortcut'])) {
		$data[] = array('title' => '快捷菜单', 'permission' => $name.'_shortcut');
	}
	if(!empty($entries['cover'])) {
		foreach($entries['cover'] as $cover) {
			$data[] = array('title' => $cover['title'], 'permission' => $name.'_cover_'.$cover['do']);
		}
	}
	if(!empty($entries['menu'])) {
		foreach($entries['menu'] as $menu) {
			$data[] = array('title' => $menu['title'], 'permission' => $name.'_menu_'.$menu['do']);
		}
	}
	unset($entries);
	if(!empty($module['permissions'])) {
		$module['permissions'] = (array)iunserializer($module['permissions']);
		foreach ($module['permissions'] as $permission) {
			$data[] = array('title' => $permission['title'], 'permission' => $name . '_permission_' . $permission['permission']);
		}
	}
	return $data;
}

/**
 *  卸载模块
 * @param string $module_name 模块标识
 * @param bool $is_clean_rule 是否删除相关的统计数据和回复规则
 */
function module_uninstall($module_name, $is_clean_rule = false) {
	global $_W;
	load()->model('cloud');
	if (empty($_W['isfounder'])) {
		return error(1, '您没有卸载模块的权限！');
	}
	$module_name = trim($module_name);
	$module = module_fetch($module_name);
	if (empty($module)) {
		return error(1, '模块已经被卸载或是不存在！');
	}
	if (!empty($module['issystem'])) {
		return error(1, '系统模块不能卸载！');
	}
	if (!empty($module['plugin'])) {
		pdo_delete('modules_plugin', array('main_module' => $module_name));
	}
	$modulepath = IA_ROOT . '/addons/' . $module_name . '/';
	$manifest = ext_module_manifest($module_name);
	if (empty($manifest)) {
		$r = cloud_prepare();
		if (is_error($r)) {
			itoast($r['message'], url('cloud/profile'), 'error');
		}
		$packet = cloud_m_build($module_name, 'uninstall');
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
	pdo_insert('modules_recycle', array('modulename' => $module_name));
	pdo_delete('uni_account_modules', array('module' => $module_name));
	ext_module_clean($module_name, $is_clean_rule);
	cache_build_module_subscribe_type();
	cache_build_uninstalled_module();
	cache_build_module_info($module_name);

	return true;
}

/**
 *  获取指定模块在当前公众号安装的插件
 * @param string $module_name 模块标识
 * @param array() $plugin_list 插件列表
 */
function module_get_plugin_list($module_name) {
	$module_info = module_fetch($module_name);
	if (!empty($module_info['plugin_list']) && is_array($module_info['plugin_list'])) {
		$plugin_list = array();
		foreach ($module_info['plugin_list'] as $plugin) {
			$plugin_info = module_fetch($plugin);
			if (!empty($plugin_info)) {
				$plugin_list[$plugin] = $plugin_info;
			}
		}
		return $plugin_list;
	} else {
		return array();
	}
}

/**
 *  返回模块的盗版信息与升级信息
 * @param string $module 模块标识
 * @return array
 */
function module_status($module) {
	load()->model('cloud');
	$module_status = array('upgrade' => array('upgrade' => 0), 'ban' => 0);

	$cloud_m_query = cloud_m_query($module);
	$module_status['ban'] = !empty($cloud_m_query['pirate_apps'][$module]) ? 1 : 0;

	$cloud_m_info = cloud_m_info($module);
	$module_info = module_fetch($module);
	if (!empty($cloud_m_info) && !empty($cloud_m_info['version']['version'])) {
		if (ver_compare($module_info['version'], $cloud_m_info['version']['version'])) {
			$module_status['upgrade'] = array('name' => $module_info['title'], 'version' => $cloud_m_info['version']['version'], 'upgrade' => 1);
		}
	} else {
		$manifest = ext_module_manifest($module);
		if (!empty($manifest)) {
			if (ver_compare($module_info['version'], $manifest['application']['version'])) {
				$module_status['upgrade'] = array('name' => $module_info['title'], 'version' => $manifest['application']['version'], 'upgrade' => 1);
			}
		}
	}

	$cache_build_module = false;
	$module_ban_setting = setting_load('module_ban');
	$module_ban_setting = is_array($module_ban_setting['module_ban']) ? $module_ban_setting['module_ban'] : array();
	if (!in_array($module, $module_ban_setting) && !empty($module_status['ban'])) {
		$module_ban_setting[] = $module;
		$cache_build_module = true;
		setting_save($module_ban_setting, 'module_ban');
	}
	if (in_array($module, $module_ban_setting) && empty($module_status['ban'])) {
		$key = array_search($module, $module_ban_setting);
		unset($module_ban_setting[$key]);
		$cache_build_module = true;
		setting_save($module_ban_setting, 'module_ban');
	}

	$module_upgrade_setting = setting_load('module_upgrade');
	$module_upgrade_setting = is_array($module_upgrade_setting['module_upgrade']) ? $module_upgrade_setting['module_upgrade'] : array();
	if (!in_array($module, array_keys($module_upgrade_setting)) && !empty($module_status['upgrade']['upgrade'])) {
		$module_upgrade_setting[$module] = $module_status['upgrade'];
		$cache_build_module = true;
		setting_save($module_upgrade_setting, 'module_upgrade');
	}
	if (in_array($module, array_keys($module_upgrade_setting)) && empty($module_status['upgrade']['upgrade'])) {
		unset($module_upgrade_setting[$module]);
		$cache_build_module = true;
		setting_save($module_upgrade_setting, 'module_upgrade');
	}

	if ($cache_build_module) {
		cache_build_module_info($module);
	}
	return $module_status;
}