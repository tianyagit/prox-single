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
	load()->func('communication');

	global $_W;
	/* sxstart */
	if (IMS_FAMILY == 's' || IMS_FAMILY == 'x') {
		$ts = array('rule', 'cover', 'menu', 'home', 'profile', 'shortcut', 'function', 'mine', 'system_welcome');
	}
	/* sxend */
	/* vstart */
	if (IMS_FAMILY == 'v') {
		$ts = array('rule', 'cover', 'menu', 'home', 'profile', 'shortcut', 'function', 'mine');
	}
	/* vend */
	if(empty($types)) {
		$types = $ts;
	} else {
		$types = array_intersect($types, $ts);
	}
	$bindings = pdo_getall('modules_bindings', array('module' => $name, 'entry' => $types), array(), '', 'displayorder DESC, eid ASC');
	$entries = array();
	foreach($bindings as $bind) {
		if(!empty($bind['call'])) {
			$response = ihttp_request(url('utility/bindcall', array('modulename' => $bind['module'], 'callname' => $bind['call'], 'args' => $args, 'uniacid' => $_W['uniacid'])), array(), $extra);
			if (is_error($response)) {
				continue;
			}
			$response = json_decode($response['content'], true);
			$ret = $response['message']['message'];
			if(is_array($ret)) {
				foreach($ret as $i => $et) {
					if (empty($et['url'])) {
						continue;
					}
					$et['url'] = $et['url'] . '&__title=' . urlencode($et['title']);
					$entries[$bind['entry']][] = array('eid' => 'user_' . $i, 'title' => $et['title'], 'do' => $et['do'], 'url' => $et['url'], 'from' => 'call', 'icon' => $et['icon'], 'displayorder' => $et['displayorder']);
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
			if($bind['entry'] == 'system_welcome') {
				$url = wurl("site/entry", array('eid' => $bind['eid']));
			}

			if(empty($bind['icon'])) {
				$bind['icon'] = 'fa fa-puzzle-piece';
			}
			if (!defined('SYSTEM_WELCOME_MODULE') && $bind['entry'] == 'system_welcome') {
				continue;
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
	$sql = "SELECT * FROM " . tablename('modules_bindings') . " WHERE `eid`=:eid";
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
 * 添加应用权限组
 * @param $package
 * @return bool
 */
function module_save_group_package($package) {
	global $_W;
	load()->model('user');
	load()->model('cache');

	if (empty($package['name'])) {
		return error(-1, '请输入套餐名');
	}

	if (user_is_vice_founder()) {
		$package['owner_uid'] = $_W['uid'];
	}
	if (!empty($package['modules'])) {
		$package['modules'] = iserializer($package['modules']);
	}

	if (!empty($package['templates'])) {
		$templates = array();
		foreach ($package['templates'] as $template) {
			$templates[] = $template['id'];
		}
		$package['templates'] = iserializer($templates);
	}

	if (!empty($package['id'])) {
		$name_exist = pdo_get('uni_group', array('uniacid' => 0, 'id <>' => $package['id'], 'name' => $package['name']));
	} else {
		$name_exist = pdo_get('uni_group', array('uniacid' => 0, 'name' => $package['name']));
	}

	if (!empty($name_exist)) {
		return error(-1, '套餐名已存在');
	}

	if (!empty($package['id'])) {
		pdo_update('uni_group', $package, array('id' => $package['id']));
		cache_build_account_modules();
	} else {
		pdo_insert('uni_group', $package);
	}

	cache_build_uni_group();
	return error(0, '添加成功');
}
/**
 * 获取指定模块及模块信息
 *
 * @param string $name 模块名称
 * @return array 模块信息
 */
function module_fetch($name) {
	global $_W;
	$cachekey = create_cache_key('module_info', array('module_name' => $name));
	$module = cache_load($cachekey);
	if (empty($module)) {
		$module_table = table('module');
		$module_info = $module_table->getInstalledModuleInfo($name);
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
		if ($module_info['app_support'] != MODULE_SUPPORT_ACCOUNT && $module_info['wxapp_support'] != MODULE_SUPPORT_WXAPP && $module_info['webapp_support'] != MODULE_SUPPORT_WEBAPP && $module_info['welcome_support'] != MODULE_SUPPORT_SYSTEMWELCOME) {
			$module_info['app_support'] = MODULE_SUPPORT_ACCOUNT;
		}
		$module_info['is_relation'] = $module_info['app_support'] ==2 && $module_info['wxapp_support'] == 2 ? true : false;
		$module_ban = (array)setting_load('module_ban');
		if (in_array($name, $module_ban['module_ban'])) {
			$module_info['is_ban'] = true;
		}
		$module_upgrade = (array)setting_load('module_upgrade');
		if (in_array($name, array_keys($module_upgrade['module_upgrade']))) {
			$module_info['is_upgrade'] = true;
		}
		$module = $module_info;
		cache_write($cachekey, $module_info);
	}
	//有公众号时，附加模块配置信息
	if (!empty($module) && !empty($_W['uniacid'])) {
		$setting_cachekey = create_cache_key('module_setting', array('module_name' => $name, 'uniacid' => $_W['uniacid']));
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
 * 获取所有未安装的模块
 * @param string $status 模块状态，unistalled : 未安装模块, recycle : 回收站模块;
 * @param string $cache 模块类型;
 */
function module_get_all_uninstalled($status, $module_type = '')  {
	$status = $status == 'recycle' ? 'recycle' : 'uninstalled';

	if (ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL) {
		$account_type = 'wxapp';
	} elseif (ACCOUNT_TYPE == ACCOUNT_TYPE_OFFCIAL_NORMAL) {
		$account_type = 'app';
	} elseif (ACCOUNT_TYPE == ACCOUNT_TYPE_WEBAPP_NORMAL) {
		$account_type = 'webapp';
	} elseif (ACCOUNT_TYPE == ACCOUNT_TYPE_PHONEAPP_NORMAL) {
		$account_type = 'phoneapp';
	}
	if (!empty($module_type)) {
		$account_type = $module_type;
	}
	load()->classs('cloudapi');
	$cloud_api = new CloudApi();
	$get_cloud_m_count = $cloud_api->get('site', 'stat', array('module_quantity' => 1), 'json');

	$modules_table = table('module');
	$modules_local = $modules_table->getModulesLocalList();
	$status_text = array('recycle', 'uninstalled');
	$all_modules = array();
	if (!empty($modules_local) && is_array($modules_local)) {
		foreach ($modules_local as $name => $value) {
			foreach ($status_text as $text) {
				if ($value['status'] != $text) {
					continue;
				}
				if ($value['app_support'] == 2) {
					$all_modules[$text]['app'][$name] = $value;
				}
				if ($value['wxapp_support'] == 2) {
					$all_modules[$text]['wxapp'][$name] = $value;
				}
				if ($value['webapp_support'] == 2) {
					$all_modules[$text]['webapp'][$name] = $value;
				}
				if ($value['phoneapp_support'] == 2) {
					$all_modules[$text]['phoneapp'][$name] = $value;
				}
				if ($value['welcome_support'] == 2) {
					$all_modules[$text]['system_welcome'][$name] = $value;
				}
			}
		}
	}
	$uninstall_modules = array(
		'cloud_m_count' => $get_cloud_m_count['module_quantity'],
		'modules' => $all_modules,
		'app_count' => count($all_modules['uninstalled']['app']),
		'wxapp_count' => count($all_modules['uninstalled']['wxapp']),
		'webapp_count' => count($all_modules['uninstalled']['webapp']),
		'phoneapp_count' => count($all_modules['uninstalled']['phoneapp']),
		'welcome_count' => count($all_modules['uninstalled']['welcome'])
	);
	if (!empty($account_type)) {
		$uninstall_modules['modules'] = (array)$uninstall_modules['modules'][$status][$account_type];
		$uninstall_modules['module_count'] = $uninstall_modules[$account_type . '_count'];
	}
	return $uninstall_modules;
}
/**
 * 通过本地modules_local获取所有未安装的模块
 * @return array $modules 未安装和回收站模块数据列表
 */
function module_build_uninstalled_module_by_local() {
	load()->classs('cloudapi');
	$cloud_api = new CloudApi();
	$get_cloud_m_count = $cloud_api->get('site', 'stat', array('module_quantity' => 1), 'json');
	$modules_table = table('module');
	$modules_local = $modules_table->getModulesLocalList();
	$status_text = array('recycle', 'uninstalled');
	$all_modules = array();
	if (!empty($modules_local) && is_array($modules_local)) {
		foreach ($modules_local as $name => $value) {
			foreach ($status_text as $text) {
				if ($value['status'] != $text) {
					continue;
				}
				if (empty($account_type)) {
					if ($value['app_support'] == 2) {
						$all_modules[$text]['app'][$name] = $value;
					}
					if ($value['wxapp_support'] == 2) {
						$all_modules[$text]['wxapp'][$name] = $value;
					}
					if ($value['webapp_support'] == 2) {
						$all_modules[$text]['webapp'][$name] = $value;
					}
					if ($value['phoneapp_support'] == 2) {
						$all_modules[$text]['phoneapp'][$name] = $value;
					}
					if ($value['welcome_support'] == 2) {
						$all_modules[$text]['system_welcome'][$name] = $value;
					}
				}
			}
		}
	}
	$modules['cloud_m_count'] = $get_cloud_m_count['module_quantity'];
	$modules['modules'] = $all_modules;
	$modules['app_count'] = count($all_modules['uninstalled']['app']);
	$modules['wxapp_count'] = count($all_modules['uninstalled']['wxapp']);
	$modules['webapp_count'] = count($all_modules['uninstalled']['webapp']);
	$modules['phoneapp_count'] = count($all_modules['uninstalled']['phoneapp']);
	$modules['welcome_count'] = count($all_modules['uninstalled']['welcome']);
	return $modules;
}

/**
 * 获取某个模块的权限列表
 * @param string $name 模块标识
 */
function module_permission_fetch($name) {
	$module = module_fetch($name);
	$data = array();
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
	load()->object('cloudapi');
	if (empty($_W['isfounder'])) {
		return error(1, '您没有卸载模块的权限！');
	}
	$module_name = trim($module_name);
	$module = pdo_get('modules', array('name' => $module_name));
	if (empty($module)) {
		return error(1, '模块已经被卸载或是不存在！');
	}
	if (!empty($module['issystem'])) {
		return error(1, '系统模块不能卸载！');
	}
	pdo_delete('modules_plugin', array('main_module' => $module_name));

	pdo_delete('uni_account_modules', array('module' => $module_name));
	cache_delete_cache_name('module_all_uninstall');
	ext_module_clean($module_name, $is_clean_rule);
	cache_build_module_subscribe_type();
	cache_build_uninstalled_module();
	cache_build_module_info($module_name);

	return true;
}

/**
 *  执行模块的卸载脚本
 * @param string $module_name 模块标识
 */
function module_execute_uninstall_script($module_name) {
	global $_W;
	load()->object('cloudapi');
	load()->model('cloud');
	if (empty($_W['isfounder'])) {
		return error(1, '您没有卸载模块的权限！');
	}
	$modulepath = IA_ROOT . '/addons/' . $module_name . '/';
	$manifest = ext_module_manifest($module_name);
	if (empty($manifest)) {
		$result = cloud_prepare();
		if (is_error($result)) {
			return error(1, $result['message']);
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
	} else {
		if (!empty($manifest['uninstall'])) {
			if (strexists($manifest['uninstall'], '.php')) {
				if (file_exists($modulepath . $manifest['uninstall'])) {
					require($modulepath . $manifest['uninstall']);
				}
			} else {
				pdo_run($manifest['uninstall']);
			}
		}
	}
	pdo_delete('modules_recycle', array('modulename' => $module_name));
	$cloudapi = new CloudApi();
	$recycle_module = $cloudapi->post('cache', 'get', array('key' => create_cache_key('recycle_module')));
	$recycle_module = !empty($recycle_module['data']) ? $recycle_module['data'] : array();
	unset($recycle_module[$module_name]);
	$cloudapi->post('cache', 'set', array('key' => create_cache_key('recycle_module'), 'value' => $recycle_module));
	cache_delete_cache_name('module_all_uninstall');
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
	$cloud_m_query['pirate_apps'] = is_array($cloud_m_query['pirate_apps']) ? $cloud_m_query['pirate_apps'] : array();
	$module_status['ban'] = in_array($module, $cloud_m_query['pirate_apps']) ? 1 : 0;

	$cloud_m_info = cloud_m_info($module);
	$module_info = module_fetch($module);
	if (!empty($cloud_m_info) && !empty($cloud_m_info['version']['version'])) {
		if (version_compare($module_info['version'], $cloud_m_info['version']['version'])) {
			$module_status['upgrade'] = array('name' => $module_info['title'], 'version' => $cloud_m_info['version']['version'], 'upgrade' => 1);
		}
	} else {
		$manifest = ext_module_manifest($module);
		if (!empty($manifest)) {
			if (version_compare($module_info['version'], $manifest['application']['version'])) {
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

/**
 * 过滤传入的模块返回其中有更新的模块及模块信息
 * @param array $module_list 模块标识
 * @return array $modules 有升级的模块及升级信息
 */
function module_filter_upgrade($module_list) {
	$modules = array();
	$modules_table = table('module');
	$modules_local = $modules_table->getModulesLocalList();
	$installed_module = pdo_getall('modules', array('name' => $module_list), array('version', 'name'), 'name');
	if (!empty($module_list) && is_array($module_list) && !empty($installed_module)) {
		foreach ($module_list as $key => $module) {
			if (empty($installed_module[$module])) {
				continue;
			}
			$manifest = ext_module_manifest($module);
			if (!empty($manifest) && is_array($manifest)) {
				$module = array('name' => $module);
				$module['from'] = 'local';
				if (version_compare($installed_module[$module['name']]['version'], $manifest['application']['version']) == '-1') {
					$module['upgrade'] = true;
					$module['upgrade_branch'] = true;
					$modules[$module['name']] = $module;
				}
			} else {
				if (is_array($modules_local) && !empty($modules_local[$module])) {
					$modules[$module] = $modules_local[$module];
				}
			}
		}
	}
	return $modules;
}
/**
 * 得到最新可升级应用
 * @param type account/wxapp
 * @return array 升级的模块列表
 */
function module_upgrade_new($type = 'account') {
	if ($type == 'wxapp') {
		$module_list = user_module_by_account_type('wxapp');
	} else {
		$module_list = user_module_by_account_type('account');
	}
	$modules_table = table('module');
	$modules_ignore = $modules_table->getModulesIgnoreList();
	$upgrade_modules = module_filter_upgrade(array_keys($module_list));
	if (!empty($upgrade_modules)) {
		foreach ($upgrade_modules as $key => &$module) {
			if (empty($module['is_upgrade'])) {
				unset($upgrade_modules[$key]);
			}
			$module['is_ignore'] = 0;
			if (!empty($modules_ignore[$key])) {
				$ignore_version = $modules_ignore[$key]['version'];
				$upgrade_version = $module['version'];
				if (ver_compare($ignore_version, $upgrade_version) >= 0) {
					$module['is_ignore'] = 1;
				}
			}
			$module_fetch = module_fetch($key);
			$module['logo'] = $module_fetch['logo'];
			$module['link'] = url('module/manage-system/module_detail', array('name' => $module['name'], 'show' => 'upgrade'));
		}
		unset($module);
	}
	return $upgrade_modules;
}

/**
 * 判断某一模块是否在公众号模块权限内
 * @param string $module_name
 * @param int $uniacid
 * @return boolean
 */
function module_exist_in_account($module_name, $uniacid) {
	global $_W;
	$result = false;
	$module_name = trim($module_name);
	$uniacid = intval($uniacid);
	if (empty($module_name) || empty($uniacid)) {
		return $result;
	}
	$founders = explode(',', $_W['config']['setting']['founder']);
	$owner_uid = pdo_getcolumn('uni_account_users',  array('uniacid' => $uniacid, 'role' => 'owner'), 'uid');
	if (!empty($owner_uid) && !in_array($owner_uid, $founders)) {
		if (IMS_FAMILY == 'x') {
			$site_store_buy_goods = uni_site_store_buy_goods($uniacid);
		} else {
			$site_store_buy_goods = array();
		}
		$account_table = table('account');
		$uni_modules = $account_table->accountGroupModules($uniacid);
		$user_modules = user_modules($owner_uid);
		$modules = array_merge(array_keys($user_modules), $uni_modules, $site_store_buy_goods);
		$result = in_array($module_name, $modules) ? true : false;
	} else {
		$result = true;
	}
	return $result;
}


/**
 * 获取操作员有某一模块权限的所有公众号和小程序
 * @param int $uid 用户UID
 * @param string $module_name 模块name
 * @return array()
 */
function module_get_user_account_list($uid, $module_name) {
	$accounts_list = array();
	$uid = intval($uid);
	$module_name = trim($module_name);
	if (empty($uid) || empty($module_name)) {
		return $accounts_list;
	}
	$module_info = module_fetch($module_name);
	if (empty($module_info)) {
		return $accounts_list;
	}

	$account_users_info = table('account')->userOwnedAccount($uid);
	if (empty($account_users_info)) {
		return $accounts_list;
	}
	$accounts = array();
	foreach ($account_users_info as $account) {
		if (empty($account['uniacid'])) {
			continue;
		}
		$uniacid = 0;
		if (($account['type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL || $account['type'] == ACCOUNT_TYPE_OFFCIAL_AUTH) && $module_info['app_support'] == MODULE_SUPPORT_ACCOUNT) {
			$uniacid = $account['uniacid'];
		} elseif ($account['type'] == ACCOUNT_TYPE_APP_NORMAL && $module_info['wxapp_support'] == MODULE_SUPPORT_WXAPP) {
			$uniacid = $account['uniacid'];
		}
		if (!empty($uniacid)) {
			if (module_exist_in_account($module_name, $uniacid)) {
				$accounts_list[$uniacid] = $account;
			}
		}
	}

	return $accounts_list;
}

/**
 * 获取操作员对某一模块，公众号与小程序关联信息
 */
function module_link_uniacid_fetch($uid, $module_name) {
	$result = array();
	$uid = intval($uid);
	$module_name = trim($module_name);
	if (empty($uid) || empty($module_name)) {
		return $result;
	}
	$accounts_list = module_get_user_account_list($uid, $module_name);
	if (empty($accounts_list)) {
		return $result;
	}
	$accounts_link_result = array();
	foreach ($accounts_list as $key => $account_value) {
		if ($account_value['type'] == ACCOUNT_TYPE_APP_NORMAL) {
			$account_value['versions'] = wxapp_version_all($account_value['uniacid']);
			if (empty($account_value['versions'])) {
				$accounts_link_result[$key] = $account_value;
				continue;
			}
			foreach ($account_value['versions'] as $version_key => $version_value) {
				if (empty($version_value['modules'])) {
					continue;
				}
				if ($version_value['modules'][0]['name'] != $module_name) {
					continue;
				}
				if (empty($version_value['modules'][0]['account']) || !is_array($version_value['modules'][0]['account'])) {
					$accounts_link_result[$key] = $account_value;
					continue;
				}
				if (!empty($version_value['modules'][0]['account']['uniacid'])) {
					$accounts_link_result[$version_value['modules'][0]['account']['uniacid']][] = array(
						'uniacid' => $key,
						'version' => $version_value['version'],
						'version_id' => $version_value['id'],
						'name' => $account_value['name'],
					);
					unset($account_value['versions'][$version_key]);
				}

			}
		}
		if ($account_value['type'] == ACCOUNT_TYPE_OFFCIAL_NORMAL || $account_value['type'] == ACCOUNT_TYPE_OFFCIAL_AUTH) {
			if (empty($accounts_link_result[$key])) {
				$accounts_link_result[$key] = $account_value;
			} else {
				$link_wxapp = $accounts_link_result[$key];
				$accounts_link_result[$key] = $account_value;
				$accounts_link_result[$key]['link_wxapp'] = $link_wxapp;
			}
		}
	}
	if (!empty($accounts_link_result)) {
		foreach ($accounts_link_result as $link_key => $link_value) {
			if (in_array($link_value['type'], array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH)) && !empty($link_value['link_wxapp']) && is_array($link_value['link_wxapp'])) {
				foreach ($link_value['link_wxapp'] as $value) {
					$result[] = array(
						'app_name' => $link_value['name'],
						'wxapp_name' => $value['name'] . ' ' . $value['version'],
						'uniacid' => $link_value['uniacid'],
						'version_id' => $value['version_id'],
					);
				}
			} elseif ($link_value['type'] == ACCOUNT_TYPE_APP_NORMAL && !empty($link_value['versions']) && is_array($link_value['versions'])) {
				foreach ($link_value['versions'] as $value) {
					$result[] = array(
						'app_name' => '',
						'wxapp_name' => $link_value['name'] . ' ' . $value['version'],
						'uniacid' => $link_value['uniacid'],
						'version_id' => $value['id'],
					);
				}
			} else {
				$result[] = array(
					'app_name' => $link_value['name'],
					'wxapp_name' => '',
					'uniacid' => $link_value['uniacid'],
					'version_id' => '',
				);
			}
		}
	}

	return $result;
}

/**
 * 对某一模块，保留最后一次进入的小程序OR公众号，以便点进入列表页时可以默认进入
 * @param unknown $uniacid
 * @return boolean
 */
function module_save_switch($module_name, $uniacid = 0, $version_id = 0) {
	global $_W, $_GPC;
	load()->model('visit');
	if (empty($_GPC['__switch'])) {
		$_GPC['__switch'] = random(5);
	}
	$cache_key = create_cache_key('last_account', array('switch' => $_GPC['__switch']));
	$cache_lastaccount = (array)cache_load($cache_key);
	if (empty($cache_lastaccount)) {
		$cache_lastaccount = array(
			$module_name => array(
				'module_name' => $module_name,
				'uniacid' => $uniacid,
				'version_id' => $version_id
			)
		);
	} else {
		$cache_lastaccount[$module_name] = array(
			'module_name' => $module_name,
			'uniacid' => $uniacid,
			'version_id' => $version_id
		);
	}
	visit_system_update(array('modulename' => $module_name, 'uid' => $_W['uid']));
	cache_write($cache_key, $cache_lastaccount);
	isetcookie('__switch', $_GPC['__switch'], 7 * 86400);
	return true;
}

/**
 * 获取用户上一次进入模块的公众号OR小程序信息
 */
function module_last_switch($module_name) {
	global $_GPC;
	$module_name = trim($module_name);
	if (empty($module_name)) {
		return array();
	}
	$cache_key = create_cache_key('last_account', array('switch' => $_GPC['__switch']));
	$cache_lastaccount = (array)cache_load($cache_key);
	return $cache_lastaccount[$module_name];
}

/**
 * 获取模块店员信息
 */
function module_clerk_info($module_name) {
	$user_permissions = array();
	$module_name = trim($module_name);
	if (empty($module_name)) {
		return $user_permissions;
	}
	$user_permissions = table('userspermission')->moduleClerkPermission($module_name);
	if (!empty($user_permissions)) {
		foreach ($user_permissions as $key => $value) {
			$user_permissions[$key]['user_info'] = user_single($value['uid']);
		}
	}
	return $user_permissions;
}

/**
 * 将应用列表页的模块置顶
 */
function module_rank_top($module_name) {
	global $_W;
	$result = table('module')->moduleSetRankTop($module_name);
	return empty($result) ? true : false;
}

function module_installed_list($type = '') {
	global $_W;
	$module_list = array();
	$user_has_module = user_modules($_W['uid']);
	if (empty($user_has_module)) {
		return $module_list;
	}
	//根据模块类型分类
	$module_support_type = array(
		'wxapp_support' => array(
			'type' => WXAPP_TYPE_SIGN,
			'support' => MODULE_SUPPORT_WXAPP,
		),
		'account_support' => array(
			'type' => ACCOUNT_TYPE_SIGN,
			'support' => MODULE_SUPPORT_ACCOUNT,
		),
		'welcome_support' => array(
			'type' => WELCOMESYSTEM_TYPE_SIGN,
			'support' => MODULE_SUPPORT_SYSTEMWELCOME,
		),
		'webapp_support' => array(
			'type' => WEBAPP_TYPE_SIGN,
			'support' => MODULE_SUPPORT_WEBAPP,
		),
		'phoneapp_support' => array(
			'type' => PHONEAPP_TYPE_SIGN,
			'support' => MODULE_SUPPORT_PHONEAPP,
		),
	);
	
	foreach ($user_has_module as $modulename => $module) {
		foreach ($module_support_type as $support_name => $support) {
			
			if ($module[$support_name] == $support['support']) {
				$module_list[$support['type']][$modulename] = $module;
			}
		}
	}
	
	if (!empty($type)) {
		return $module_list[$type];
	} else {
		return $module_list;
	}
}

/**
 * 更新本地模块到modules_cloud表
 */
function module_local_upgrade_info() {
	$modulelist = table('modules')->getall('name');
	
	$module_root = IA_ROOT . '/addons/';
	$module_path_list = glob($module_root . '/*');
	if (empty($module_path_list)) {
		return true;
	}
	
	foreach ($module_path_list as $path) {
		$modulename = pathinfo($path, PATHINFO_BASENAME);
		if (!empty($modulelist[$modulename])) {
			continue;
		}
		
		if (!file_exists($path . '/manifest.xml')) {
			continue;
		}
		
		$module_upgrade_data = array(
			'name' => $modulename,
			'has_new_version' => 0,
			'has_new_branch' => 0,
			'install_status' => MODULE_LOCAL_UNINSTALL,
		);
		$manifest = ext_module_manifest($modulename);
		if (!empty($manifest['platform']['supports'])) {
			foreach (array('app', 'wxapp', 'webapp', 'phoneapp', 'system_welcome') as $support) {
				if (in_array($support, $manifest['platform']['supports'])) {
					//纠正支持类型名字，统一
					if ($support == 'app') {
						$support = 'account';
					}
					$module_upgrade_data["{$support}_support"] = MODULE_SUPPORT_ACCOUNT;
				}
			}
		}
		$module_cloud_upgrade = table('modules_cloud')->getByName($modulename);
		
		if (empty($module_cloud_upgrade)) {
			table('modules_cloud')->fill($module_upgrade_data)->save();
		} else {
			table('modules_cloud')->fill($module_upgrade_data)->where('name', $modulename)->save();
		}
	}
	return true;
}
/**
 * 检查传入的模块是否有更新
 * 优先检查本地是否包含Manifest.xml
 * 否则通过modules_cloud表先查询模块云端缓存信息
 * 如果记录过期则通过接口更新modules_cloud表中的数据
 * @param array $modulelist
 */
function module_upgrade_info($modulelist = array()) {
	$result = array();
	
	//没有指定查询模块列表，则获取全部模块查询
	if (empty($modulelist)) {
		$modulelist = table('modules')->getall('name');
	}
	
	if (empty($modulelist)) {
		return array();
	}
	
	cloud_prepare();
	$cloud_m_query_module = cloud_m_query($cloud_module_check_upgrade);
	$cloud_m_query_module = include IA_ROOT . '/web/cloud.php';
	unset($cloud_m_query_module['pirate_apps']);
	
	foreach ($modulelist as $modulename => $module) {
		if (!empty($module['issystem'])) {
			unset($modulelist[$modulename]);
			continue;
		}
	
		$module_upgrade_data = array(
			'name' => $modulename,
			'has_new_version' => 0,
			'has_new_branch' => 0,
		);
	
		$manifest = ext_module_manifest($modulename);
		if (!empty($manifest)) {
			$module_upgrade_data['install_status'] = MODULE_LOCAL_INSTALL;
		} elseif ($cloud_m_query_module[$modulename]) {
			$module_upgrade_data['install_status'] = MODULE_CLOUD_INSTALL;
			$manifest_cloud = $cloud_m_query_module[$modulename];
			$manifest = array(
				'application' => array(
					'name' => $modulename,
					'version' => $manifest_cloud['version'],
				),
				'platform' => array(
					'supports' => array()
				),
			);
			if ($manifest_cloud['site_branch']['app_aupport'] == MODULE_SUPPORT_ACCOUNT) {
				$manifest['platform']['supports'][] = 'app';
			}
			if ($manifest_cloud['site_branch']['wxapp_aupport'] == MODULE_SUPPORT_WXAPP) {
				$manifest['platform']['supports'][] = 'wxapp';
			}
			if ($manifest_cloud['site_branch']['webapp_aupport'] == MODULE_SUPPORT_WEBAPP) {
				$manifest['platform']['supports'][] = 'webapp';
			}
			if ($manifest_cloud['site_branch']['android_aupport'] == MODULE_SUPPORT_PHONEAPP ||
				$manifest_cloud['site_branch']['ios_aupport'] == MODULE_SUPPORT_PHONEAPP) {
				$manifest['platform']['supports'][] = 'phoneapp';
			}
			if ($manifest_cloud['site_branch']['system_welcome_support'] == MODULE_SUPPORT_SYSTEMWELCOME) {
				$manifest['platform']['supports'][] = 'system_welcome';
			}
			$manifest['branches'] = !empty($manifest_cloud['branches']);
		} else {
			//本地已安装没有manifest也没有cloud信息，默认为本地安装 
			$module_upgrade_data['install_status'] = MODULE_LOCAL_INSTALL;
		}
		//云服务模块已在本地安装，unset后方便后面排查未安装模块
		//云上模块，如果在本地有manifest.xml，以本地模块为主
		unset($cloud_m_query_module[$modulename]);
		
		if (version_compare($module['version'], $manifest['application']['version']) == '-1') {
			$module_upgrade_data['has_new_version'] = 1;
		
			$result[$modulename] = array(
				'name' => $modulename,
				'new_version' => 1,
				'best_version' => $manifest['application']['version'],
			);
		}
		
		if (!empty($manifest['branches'])) {
			$module_upgrade_data['has_new_branch'] = 1;
			$result[$modulename]['new_branch'] = 1;
		}
		if (!empty($manifest['platform']['supports'])) {
			foreach (array('app', 'wxapp', 'webapp', 'phoneapp', 'system_welcome') as $support) {
				if (in_array($support, $manifest['platform']['supports'])) {
					//纠正支持类型名字，统一
					if ($support == 'app') {
						$support = 'account';
					}
					$module_upgrade_data["{$support}_support"] = MODULE_SUPPORT_ACCOUNT;
				}
			}
		}
		$module_cloud_upgrade = table('modules_cloud')->getByName($modulename);
		
		if (empty($module_cloud_upgrade)) {
			table('modules_cloud')->fill($module_upgrade_data)->save();
		} else {
			table('modules_cloud')->fill($module_upgrade_data)->where('name', $modulename)->save();
		}
	}
		
	if (!empty($cloud_m_query_module)) {
		foreach ($cloud_m_query_module as $modulename => $module) {
			$module_upgrade_data = array(
				'name' => $modulename,
				'has_new_version' => 0,
				'has_new_branch' => 0,
				'install_status' => MODULE_CLOUD_UNINSTALL,
			);
			foreach (array('app', 'wxapp', 'webapp', 'ios', 'android', 'system_welcome') as $support) {
				if ($module['site_branch']["{$support}_support"] == MODULE_SUPPORT_ACCOUNT) {
					//纠正支持类型名字，统一
					if ($support == 'app') {
						$support = 'account';
					}
					if ($support == 'ios' || $support == 'android') {
						$support = 'phoneapp';
					}
					$module_upgrade_data["{$support}_support"] = MODULE_SUPPORT_ACCOUNT;
				}
			}
			$module_cloud_upgrade = table('modules_cloud')->getByName($modulename);
			if (empty($module_cloud_upgrade)) {
				table('modules_cloud')->fill($module_upgrade_data)->save();
			} else {
				table('modules_cloud')->fill($module_upgrade_data)->where('name', $modulename)->save();
			}
		}
	}
	return $result;
}

function module_uninstall_total($type) {
	$type_list = array(ACCOUNT_TYPE_SIGN, WXAPP_TYPE_SIGN, WEBAPP_TYPE_SIGN, PHONEAPP_TYPE_SIGN);
	if (!in_array($type, $type_list)) {
		return 0;
	}
	return call_user_func_array(array(table('modules_cloud'), "get{$type}UninstallTotal"), array());
}