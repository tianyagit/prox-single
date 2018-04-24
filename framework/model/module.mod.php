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

/**
 * 重新计算 未安装小程序的数量
 * @param $uninstall_modules
 * @param $recycle_modules
 * @return int
 */
function recount_total_uninstalled($uninstall_modules, $recycle_modules, $status = 'uninstalled') {
	if ($status == 'recycle') {
		$uninstall_modules = module_get_all_uninstalled('uninstalled');
	}
	if (array_key_exists('modules', $uninstall_modules)) {
		$uninstall_modules_keys = array_keys($uninstall_modules['modules']);
		if (count($uninstall_modules_keys) <= 0) {
			return count($uninstall_modules_keys);
		}
	} else {
		$uninstall_modules_keys = array_keys($uninstall_modules);
	}
	$dif_keys = array_diff($uninstall_modules_keys, $recycle_modules);
	return count($dif_keys);
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
	foreach ($modulelist as $modulename => $module) {
		if (!empty($module['issystem'])) {
			unset($modulelist[$modulename]);
			continue;
		}
		$module_upgrade_data = array(
			'name' => $modulename,
			'has_new_version' => 0,
			'has_new_branch' => 0,
			'lastupdatetime' => TIMESTAMP,
		);
		
		$manifest = ext_module_manifest($modulename);
		if (!empty($manifest)&& is_array($manifest)) {
			if (version_compare($module['version'], $manifest['application']['version']) == '-1') {
				$module_upgrade_data['has_new_version'] = 1;
				
				$result[$modulename] = array(
					'name' => $modulename,
					'new_version' => 1,
					'best_version' => $manifest['application']['version'],
					'from' => 'local',
				);
			}
			
			$module_cloud_upgrade = table('modules_cloud')->getByName($modulename);
			if (empty($module_cloud_upgrade)) {
				table('modules_cloud')->fill($module_upgrade_data)->save();
			} else {
				table('modules_cloud')->fill($module_upgrade_data)->where('name', $modulename)->save();
			}
			
			//过滤掉本地存在manifest.xml的模块，剩余在通过cloud检测
			unset($modulelist[$modulename]);
		}
	}
	unset($modulename);

	cloud_prepare();
	$cloud_m_query_module = cloud_m_query($cloud_module_check_upgrade);
	$cloud_m_query_module = array (
  'appsplus_index' => 
  array (
    'id' => '9352',
    'uid' => '216630',
    'name' => 'appsplus_index',
    'title' => 'APPSPlus首页',
    'status' => '1',
    'description' => '这是微擎首页应用，需微擎商业版支持！免费版仅做体验，支持首页参数修改。交流群：720995803演示地址：https://www.appsplus.cn1.8版本新增代理功能：后台添加代理站点，根据代...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/01/23/15166915245a66e044de71a_Y02Ca3vksssC.png',
    'author' => 'Mob796110359651',
    'trade' => 1,
    'branch' => '11425',
    'site_branch' => 
    array (
      'id' => '11425',
      'name' => '免费版',
      'aid' => '9352',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '2',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.5',
      'bought' => 
      array (
        0 => 'system_welcome',
      ),
    ),
    'version' => '1.5',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      11425 => 
      array (
        'id' => '11425',
        'name' => '免费版',
        'aid' => '9352',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '2',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.5',
      ),
      12625 => 
      array (
        'id' => '12625',
        'name' => '商业版',
        'aid' => '9352',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '2',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.8.7',
      ),
      11597 => 
      array (
        'id' => '11597',
        'name' => '普通版',
        'aid' => '9352',
        'displayorder' => 0,
        'status' => 1,
        'show' => 0,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '2',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.6.1',
      ),
    ),
  ),
  'bowen_site' => 
  array (
    'id' => '8180',
    'uid' => '106215',
    'name' => 'bowen_site',
    'title' => '玖祺企业官网',
    'status' => '1',
    'description' => '单买微擎首页版没用，测试期间买到免费的用户可补差价升级。单买系统首页无法使用，且该模块不议价，谢谢合作支持 公众号 手机 PC 微信 小程序（小程序需自己打包）近期已经做好了的亮点，如下：1.支持伪静...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/12/11/15129949805a2e78a53b7e7_R797735qZi79.jpg',
    'author' => 'Bowen',
    'service_expiretime' => '1547287111',
    'trade' => 1,
    'branch' => '10076',
    'site_branch' => 
    array (
      'id' => '10076',
      'name' => 'v1',
      'aid' => '8180',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '1',
      'webapp_support' => '2',
      'system_welcome_support' => '2',
      'android_support' => '1',
      'ios_support' => '1',
      'service_price' => '800',
      'version' => '0.1.3',
      'bought' => 
      array (
        0 => 'webapp',
        1 => 'system_welcome',
      ),
    ),
    'version' => '0.1.3',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      10076 => 
      array (
        'id' => '10076',
        'name' => 'v1',
        'aid' => '8180',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '1',
        'webapp_support' => '2',
        'system_welcome_support' => '2',
        'android_support' => '1',
        'ios_support' => '1',
        'service_price' => '800',
        'version' => '0.1.3',
      ),
    ),
  ),
  'dabaomi_painter' => 
  array (
    'id' => '9722',
    'uid' => '153789',
    'name' => 'dabaomi_painter',
    'title' => '画皮匠',
    'status' => '1',
    'description' => '售后群号：247964697画皮匠，在吸粉的同时增加游戏乐趣，增加品牌曝光！活动使用说明以及介绍',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/02/07/15179757395a7a78bb39f9f_FjMQq81jjQMK.png',
    'author' => 'dabaomi',
    'trade' => 1,
    'branch' => '11868',
    'site_branch' => 
    array (
      'id' => '11868',
      'name' => '普通版',
      'aid' => '9722',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.0.3',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.0.3',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      11868 => 
      array (
        'id' => '11868',
        'name' => '普通版',
        'aid' => '9722',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.0.3',
      ),
    ),
  ),
  'ddrj_sun' => 
  array (
    'id' => '10398',
    'uid' => '112857',
    'name' => 'ddrj_sun',
    'title' => '好物商店',
    'status' => '1',
    'description' => '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;了解详情请Q:5717...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/13/15209164105aa757ba3ec40_P269r8o661fG.png',
    'author' => '凌之云',
    'trade' => 1,
    'branch' => '12650',
    'site_branch' => 
    array (
      'id' => '12650',
      'name' => '好物商店',
      'aid' => '10398',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.2.8',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.2.8',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      12650 => 
      array (
        'id' => '12650',
        'name' => '好物商店',
        'aid' => '10398',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.2.8',
      ),
    ),
  ),
  'dg_chat' => 
  array (
    'id' => '2467',
    'uid' => '114994',
    'name' => 'dg_chat',
    'title' => '直播教室-微课神器',
    'status' => '1',
    'description' => 'V4.4.81.添加新版阿里大鱼短信；2.修复后台创建话题问题；3.修复邀请卡问题；4.优化视频直播、视频录播安卓手机支持小窗口播放 ；视频直播、视频录播 安卓手机已经支持小窗口播放！！！视频直播、视...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/07/20/15005494055970911dab491_F8y9HegJuYv8.jpg',
    'author' => '夺冠科技',
    'trade' => 1,
    'branch' => '6152',
    'site_branch' => 
    array (
      'id' => '6152',
      'name' => '标准版',
      'aid' => '2467',
      'displayorder' => 4,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '4.5.4',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '4.5.4',
    'package_version' => NULL,
    'displayorder' => 4,
    'branches' => 
    array (
      6152 => 
      array (
        'id' => '6152',
        'name' => '标准版',
        'aid' => '2467',
        'displayorder' => 4,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '4.5.4',
      ),
      5247 => 
      array (
        'id' => '5247',
        'name' => '语音分佣版',
        'aid' => '2467',
        'displayorder' => 3,
        'status' => 1,
        'show' => 0,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '3.3.0',
      ),
      4041 => 
      array (
        'id' => '4041',
        'name' => '语音基础版',
        'aid' => '2467',
        'displayorder' => 2,
        'status' => 1,
        'show' => 0,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '3.1.1.4',
      ),
      3322 => 
      array (
        'id' => '3322',
        'name' => '此版本已关闭',
        'aid' => '2467',
        'displayorder' => 0,
        'status' => 1,
        'show' => 0,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.3.3',
      ),
    ),
  ),
  'dy_diancan' => 
  array (
    'id' => '10198',
    'uid' => '163309',
    'name' => 'dy_diancan',
    'title' => '微信扫码点餐',
    'status' => '1',
    'description' => '功能亮点:用手机微信扫描餐桌上的二维码，即可点菜下单。（这不是亮点）亮点是一个桌上多人可以同时扫描二维码点餐，数据实时同步。 二、界面简洁大气，低调奢华，又不失功能。&nbsp;手机屏幕就那么大，我们...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/06/15203234845a9e4b9d0a878_qZ3g00dGtf30.png',
    'author' => 'cjzy558',
    'trade' => 1,
    'branch' => '12420',
    'site_branch' => 
    array (
      'id' => '12420',
      'name' => '普通版',
      'aid' => '10198',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '2.1',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '2.1',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      12420 => 
      array (
        'id' => '12420',
        'name' => '普通版',
        'aid' => '10198',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.1',
      ),
    ),
  ),
  'exiaoer_welcome' => 
  array (
    'id' => '8686',
    'uid' => '30421',
    'name' => 'exiaoer_welcome',
    'title' => '自定义系统首页',
    'status' => '1',
    'description' => '',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/28/15222078985abb0c9abccba_uehjNGm7U4eZ.png',
    'author' => 'tudou517',
    'service_expiretime' => '1549786647',
    'trade' => 1,
    'branch' => '10655',
    'site_branch' => 
    array (
      'id' => '10655',
      'name' => '普通版',
      'aid' => '8686',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '2',
      'android_support' => '1',
      'ios_support' => '1',
      'service_price' => '200',
      'version' => '1.11',
      'bought' => 
      array (
        0 => 'system_welcome',
      ),
    ),
    'version' => '1.11',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      10655 => 
      array (
        'id' => '10655',
        'name' => '普通版',
        'aid' => '8686',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '2',
        'android_support' => '1',
        'ios_support' => '1',
        'service_price' => '200',
        'version' => '1.11',
      ),
    ),
  ),
  'fm_jiaoyu' => 
  array (
    'id' => '1102',
    'uid' => '181399',
    'name' => 'fm_jiaoyu',
    'title' => '微教育-多校版',
    'status' => '1',
    'description' => '1、微教育官方客服QQ：332035136；2、请访问我们的官方网站了解更多详情&nbsp; 微教育官方网站为：http://www.daren007.com请手动复制五一活动：2018.4月23日-...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2016/11/21/14797264915832d59c1a8ef_Bx63kbKHwB3B.jpg',
    'author' => '微美科技',
    'trade' => 1,
    'branch' => '1641',
    'site_branch' => 
    array (
      'id' => '1641',
      'name' => '多校版',
      'aid' => '1102',
      'displayorder' => 3,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '3.11',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '3.11',
    'package_version' => NULL,
    'displayorder' => 3,
    'branches' => 
    array (
      1641 => 
      array (
        'id' => '1641',
        'name' => '多校版',
        'aid' => '1102',
        'displayorder' => 3,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '3.11',
      ),
      9312 => 
      array (
        'id' => '9312',
        'name' => '开发版',
        'aid' => '1102',
        'displayorder' => 3,
        'status' => 1,
        'show' => 0,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.1',
      ),
      8721 => 
      array (
        'id' => '8721',
        'name' => '多校(年费)',
        'aid' => '1102',
        'displayorder' => 2,
        'status' => 1,
        'show' => 0,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.92',
      ),
      5765 => 
      array (
        'id' => '5765',
        'name' => '单校版',
        'aid' => '1102',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.2',
      ),
    ),
  ),
  'gs_qd' => 
  array (
    'id' => '10254',
    'uid' => '85859',
    'name' => 'gs_qd',
    'title' => '签到红包全新涨粉利器',
    'status' => '1',
    'description' => '本模块目前只适用于服务号，没有服务号的请先注册服务号哦~&nbsp;&nbsp;&nbsp;功能1：&nbsp;签到即可领取现金，活动转播极快&nbsp;参与活动只需进入活动页面，点击“签到领现金”即...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/19/15214245605aaf18b08a148_fn7l7ijJjhPH.jpg',
    'author' => '盖世',
    'trade' => 1,
    'branch' => '12481',
    'site_branch' => 
    array (
      'id' => '12481',
      'name' => '普通版',
      'aid' => '10254',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.0.7',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.0.7',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      12481 => 
      array (
        'id' => '12481',
        'name' => '普通版',
        'aid' => '10254',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.0.7',
      ),
    ),
  ),
  'haoman_xxx' => 
  array (
    'id' => '2499',
    'uid' => '111609',
    'name' => 'haoman_xxx',
    'title' => '咻一咻抽奖',
    'status' => '1',
    'description' => '咻一咻，把支付宝的玩法搬到微信上面；咻咻咻，咻奖品，咻红包，咻卡券，玩法多多；还支持输入口令抽奖码，增加咻次数哦；非常通用实用的抽奖模块，任何业务场景都适用；&nbsp;前15限时优惠499一套，后直...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2016/09/10/jn7rcGZ4WC78o82457d3794c539ad.jpg',
    'author' => '好男人',
    'trade' => 1,
    'branch' => '3360',
    'site_branch' => 
    array (
      'id' => '3360',
      'name' => '红包咻一咻',
      'aid' => '2499',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '4.2.6',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '4.2.6',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      3360 => 
      array (
        'id' => '3360',
        'name' => '红包咻一咻',
        'aid' => '2499',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '4.2.6',
      ),
    ),
  ),
  'hc_article' => 
  array (
    'id' => '1862',
    'uid' => '48397',
    'name' => 'hc_article',
    'title' => '文章模块',
    'status' => '1',
    'description' => '比模块版本比较早。适合自己购买玩玩，不建议商用。免费版不提供技术支持。谢谢。此前我们共享了这个模块出来，因为没有时间去维护更新，所以免费赠送了。这段时间，我们把用户之前反馈的问题，已经修复了。并且统一...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2016/05/30/1464600189574c067d4435b_GyEKKGKspd4D.jpg',
    'author' => '火池网络',
    'trade' => 1,
    'branch' => '2571',
    'site_branch' => 
    array (
      'id' => '2571',
      'name' => '基础版',
      'aid' => '1862',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.9',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.9',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      2571 => 
      array (
        'id' => '2571',
        'name' => '基础版',
        'aid' => '1862',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.9',
      ),
      4533 => 
      array (
        'id' => '4533',
        'name' => '应用版',
        'aid' => '1862',
        'displayorder' => 3,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.41',
      ),
    ),
  ),
  'hd_category' => 
  array (
    'id' => '10717',
    'uid' => '228818',
    'name' => 'hd_category',
    'title' => '小程序分类',
    'status' => '1',
    'description' => '售后QQ群为：377065995。加的时候记得注明是：小程序分类，否则我可能会拒绝。有任何疑问可以加群咨询小程序分类，是把所有小程序归类。有时候我们打开附近的小程序，瞬间就不知道自己想找什么样的小程序...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/23/VB2qgQ5gTJiVGwGp5ab510f5c5fb7.jpg',
    'author' => 'Mob425586533026',
    'trade' => 1,
    'branch' => '13002',
    'site_branch' => 
    array (
      'id' => '13002',
      'name' => '正式版',
      'aid' => '10717',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '3.0',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '3.0',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      13002 => 
      array (
        'id' => '13002',
        'name' => '正式版',
        'aid' => '10717',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '3.0',
      ),
    ),
  ),
  'jishulang_index1' => 
  array (
    'id' => '10614',
    'uid' => '234627',
    'name' => 'jishulang_index1',
    'title' => '开发者官网首页',
    'status' => '0',
    'description' => '此微擎首页模板由技术狼团队开发首页免费版仅做体验，支持站点配置信息修改。商业版支持所有页面参数修改，包含页面地址优化、SEO页面优化，登录注册页面。QQ：1135125706交流群：170378647...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/21/15215639715ab139441f14b_GT4XUUt7TwH1.png',
    'author' => '狼王梦科技',
    'trade' => 1,
    'branch' => '13081',
    'site_branch' => 
    array (
      'id' => '13081',
      'name' => '商业版',
      'aid' => '10614',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '2',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '2.0',
      'bought' => 
      array (
        0 => 'system_welcome',
      ),
    ),
    'version' => '2.0',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      13081 => 
      array (
        'id' => '13081',
        'name' => '商业版',
        'aid' => '10614',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '2',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.0',
      ),
    ),
  ),
  'laotouzi_tzcgw' => 
  array (
    'id' => '10174',
    'uid' => '214986',
    'name' => 'laotouzi_tzcgw',
    'title' => '挑战猜歌王',
    'status' => '1',
    'description' => '挑战猜歌王小程序功能：自定义题库，随机出题，杜绝作弊风险。牛逼算法，回答过题目不重复出题。可单独上传一类歌曲做成情歌王、粤语王等。支持付费购买积分赚钱；求助好友获得积分；成功帮好友答对题目获得积分；分...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/06/j0NDlNTLPrlkbd425a9d81900ccc3.jpg',
    'author' => '老头子',
    'trade' => 1,
    'branch' => '12392',
    'site_branch' => 
    array (
      'id' => '12392',
      'name' => '多开商业版',
      'aid' => '10174',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '2.0',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '2.0',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      12392 => 
      array (
        'id' => '12392',
        'name' => '多开商业版',
        'aid' => '10174',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.0',
      ),
      12391 => 
      array (
        'id' => '12391',
        'name' => '单开自用版',
        'aid' => '10174',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.0',
      ),
    ),
  ),
  'mijia_ivmcx' => 
  array (
    'id' => '10299',
    'uid' => '131320',
    'name' => 'mijia_ivmcx',
    'title' => 'ivmc自动售货架',
    'status' => '1',
    'description' => '&nbsp; &nbsp;米家IVMC,云享柜IVMC,自动售货机,控制器,控制板,自动售货柜程序控制，本模块对接硬件控制板+电控锁，有效解决目前无人售货架依靠信用体系来完成交易的情况。&nbsp; ...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/09/bYBN9RX7n8999m585aa2572f4ce5c.jpg',
    'author' => 'yiqianmi',
    'trade' => 1,
    'branch' => '12529',
    'site_branch' => 
    array (
      'id' => '12529',
      'name' => 'IVMC自动售货架',
      'aid' => '10299',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.1.3',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.1.3',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      12529 => 
      array (
        'id' => '12529',
        'name' => 'IVMC自动售货架',
        'aid' => '10299',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.1.3',
      ),
    ),
  ),
  'myf_shop' => 
  array (
    'id' => '6274',
    'uid' => '179250',
    'name' => 'myf_shop',
    'title' => '跳蚤市场',
    'status' => '1',
    'description' => '私人订制开发请联系QQ：1003114328以下是测试公众号，回复关键字： “小市场”&nbsp;模块说明：【注意：安装成功后，务必将/addons/ 下的mfy_shop权限设置777】这是一个二手...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/09/17/dbMpcKk6ckbp2kbK59be3f2951044.jpg',
    'author' => 'admin520',
    'trade' => 1,
    'branch' => '7906',
    'site_branch' => 
    array (
      'id' => '7906',
      'name' => '跳蚤市场',
      'aid' => '6274',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.7.1',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.7.1',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      7906 => 
      array (
        'id' => '7906',
        'name' => '跳蚤市场',
        'aid' => '6274',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.7.1',
      ),
    ),
  ),
  'qidou_info' => 
  array (
    'id' => '9061',
    'uid' => '173250',
    'name' => 'qidou_info',
    'title' => '[七豆]同城信息',
    'status' => '0',
    'description' => '【七豆门户小程序】之《同城信息》&nbsp; &nbsp; &nbsp;七豆小程序门户是一套完整的小程序门户解决方案，根据地方站长的需求，提供八大模块，同城分类信息，新闻资讯，商家店铺，论坛社区，拼车...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/01/12/15157213235a58126b5ef23_FFYG0hy9o76x.png',
    'author' => 'dennyw',
    'trade' => 1,
    'branch' => '11078',
    'site_branch' => 
    array (
      'id' => '11078',
      'name' => '普通版',
      'aid' => '9061',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.2',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.2',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      11078 => 
      array (
        'id' => '11078',
        'name' => '普通版',
        'aid' => '9061',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.2',
      ),
    ),
  ),
  'ruike_store' => 
  array (
    'id' => '10445',
    'uid' => '232648',
    'name' => 'ruike_store',
    'title' => '多门店商家列表一键拨号导航',
    'status' => '1',
    'description' => '在后台上传门店的基本信息发布成功之后，在手机端显示多门店商家必备一款简单使用小应用有使用问题可以加企鹅群群&nbsp;&nbsp;&nbsp;号：594954427',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/15/15210754995aa9c52c13a67_Ok4UH4ghi54X.jpg',
    'author' => 'Mob444635647466',
    'trade' => 1,
    'branch' => '12708',
    'site_branch' => 
    array (
      'id' => '12708',
      'name' => '普通版',
      'aid' => '10445',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.5',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.5',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      12708 => 
      array (
        'id' => '12708',
        'name' => '普通版',
        'aid' => '10445',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.5',
      ),
    ),
  ),
  'share_manage' => 
  array (
    'id' => '9045',
    'uid' => '194638',
    'name' => 'share_manage',
    'title' => '分享管理',
    'status' => '1',
    'description' => '请注意：后期会拓展很多功能！！！目前功能：1、即使你是免费版或授权版，都可以创建无微擎版权文章；2、图文样式及格式仿微信图文；3、粉丝A分享到朋友圈，他人B看到后点击进入，可记录B为A所推荐,通过查找...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/01/11/SQVLX3y03QQxVyGf5a5724604f3ae.jpg',
    'author' => 'lyping',
    'service_expiretime' => '1522144682',
    'trade' => 1,
    'branch' => '11062',
    'site_branch' => 
    array (
      'id' => '11062',
      'name' => 'master',
      'aid' => '9045',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'service_price' => '50',
      'version' => '1.0',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.0',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      11062 => 
      array (
        'id' => '11062',
        'name' => 'master',
        'aid' => '9045',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'service_price' => '50',
        'version' => '1.0',
      ),
    ),
  ),
  'star_jihua' => 
  array (
    'id' => '8494',
    'uid' => '92965',
    'name' => 'star_jihua',
    'title' => '新年计划',
    'status' => '1',
    'description' => '新年计划--一个小愿望下面我简单介绍下这款简易的模块：【使用范围】用于认证订阅号服务号，不涉及网页链接，完美规避腾讯封杀，未认证账号，个人账号不可使用【活动特性】1.支持多个微信平台同时推广，关键词单...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/12/22/FKqpLh9DgkHldhdg5a3c81b98f25a.jpg',
    'author' => '2974238227',
    'trade' => 1,
    'branch' => '10442',
    'site_branch' => 
    array (
      'id' => '10442',
      'name' => '新年计划',
      'aid' => '8494',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.1',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.1',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      10442 => 
      array (
        'id' => '10442',
        'name' => '新年计划',
        'aid' => '8494',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.1',
      ),
    ),
  ),
  'sudu8_page' => 
  array (
    'id' => '4862',
    'uid' => '72338',
    'name' => 'sudu8_page',
    'title' => '万能门店小程序',
    'status' => '1',
    'description' => '体验版：只能创建一个小程序，现支持多页面，预约功能，优惠价1元，可测试或自己公司运营使用。无限版：包含体验版的所有功能，并不限制小程序生成数量，一次购买，终身升级！每次更新功能都会增加价格，如果您需要...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/08/09/euN4Nq7E4vTaaVaV598a733c325dd.jpg',
    'author' => 'sudu8',
    'trade' => 1,
    'branch' => '6539',
    'site_branch' => 
    array (
      'id' => '6539',
      'name' => '无限版',
      'aid' => '4862',
      'displayorder' => 2,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '6.8.74',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '6.8.74',
    'package_version' => NULL,
    'displayorder' => 2,
    'branches' => 
    array (
      6539 => 
      array (
        'id' => '6539',
        'name' => '无限版',
        'aid' => '4862',
        'displayorder' => 2,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '6.8.74',
      ),
      6264 => 
      array (
        'id' => '6264',
        'name' => '体验版',
        'aid' => '4862',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.6.3',
      ),
    ),
  ),
  'sudu8_page_plugin_exchange' => 
  array (
    'id' => '10421',
    'uid' => '72338',
    'name' => 'sudu8_page_plugin_exchange',
    'title' => '积分兑换商城',
    'status' => '1',
    'description' => '万能门店积分兑换商城插件，配合万能门店小程序使用。具体可以咨询作者沟通。',
    'plugin_pid' => '4862',
    'package_support' => '1',
    'main_module' => 'sudu8_page',
    'thumb' => '//cdn.w7.cc/images/2018/03/14/15209694105aa826c2b2a52_hZ2mdjj0JAD8.jpg',
    'author' => 'sudu8',
    'trade' => 1,
    'branch' => '12678',
    'site_branch' => 
    array (
      'id' => '12678',
      'name' => '插件',
      'aid' => '10421',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.1',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.1',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      12678 => 
      array (
        'id' => '12678',
        'name' => '插件',
        'aid' => '10421',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.1',
      ),
    ),
  ),
  'sudu8_page_plugin_food' => 
  array (
    'id' => '8097',
    'uid' => '72338',
    'name' => 'sudu8_page_plugin_food',
    'title' => '餐饮小程序',
    'status' => '1',
    'description' => '功能正在完善，暂时不能商用，请暂时不要购买！！！餐饮小程序，可实现在线点餐，外卖预定',
    'plugin_pid' => '4862',
    'package_support' => '1',
    'main_module' => 'sudu8_page',
    'thumb' => '//cdn.w7.cc/images/2017/12/08/iwiwOK1060i8shAc5a2a647425500.jpg',
    'author' => 'sudu8',
    'trade' => 1,
    'branch' => '9975',
    'site_branch' => 
    array (
      'id' => '9975',
      'name' => '插件',
      'aid' => '8097',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.5.3',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.5.3',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      9975 => 
      array (
        'id' => '9975',
        'name' => '插件',
        'aid' => '8097',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.5.3',
      ),
    ),
  ),
  'sudu8_page_plugin_service' => 
  array (
    'id' => '10379',
    'uid' => '72338',
    'name' => 'sudu8_page_plugin_service',
    'title' => '小程序手机客服',
    'status' => '1',
    'description' => '可在手机端回复小程序客服信息',
    'plugin_pid' => '4862',
    'package_support' => '1',
    'main_module' => 'sudu8_page',
    'thumb' => '//cdn.w7.cc/images/2018/03/12/15208589065aa6771aba0af_H4j66ojV0rfR.jpg',
    'author' => 'sudu8',
    'trade' => 1,
    'branch' => '12628',
    'site_branch' => 
    array (
      'id' => '12628',
      'name' => '插件',
      'aid' => '10379',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.4',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.4',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      12628 => 
      array (
        'id' => '12628',
        'name' => '插件',
        'aid' => '10379',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.4',
      ),
    ),
  ),
  'sudu8_page_plugin_sign' => 
  array (
    'id' => '10163',
    'uid' => '72338',
    'name' => 'sudu8_page_plugin_sign',
    'title' => '积分签到小程序',
    'status' => '1',
    'description' => '万能门店积分签到插件签到所得的积分，同步万能门店的积分。可进行积分兑换，积分抵扣等功能。',
    'plugin_pid' => '4862',
    'package_support' => '1',
    'main_module' => 'sudu8_page',
    'thumb' => '//cdn.w7.cc/images/2018/03/05/15202422035a9d0e1b55412_uQYjKjY9S60t.jpg',
    'author' => 'sudu8',
    'trade' => 1,
    'branch' => '12378',
    'site_branch' => 
    array (
      'id' => '12378',
      'name' => '插件',
      'aid' => '10163',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.3',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.3',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      12378 => 
      array (
        'id' => '12378',
        'name' => '插件',
        'aid' => '10163',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.3',
      ),
    ),
  ),
  'sudu8_page_plugin_travel' => 
  array (
    'id' => '6903',
    'uid' => '72338',
    'name' => 'sudu8_page_plugin_travel',
    'title' => '预约预订',
    'status' => '1',
    'description' => '预约、预订小程序',
    'plugin_pid' => '4862',
    'package_support' => '1',
    'main_module' => 'sudu8_page',
    'thumb' => '//cdn.w7.cc/images/2017/10/25/150891043859f0256694af9_L0knKRa0n0rM.png',
    'author' => 'sudu8',
    'trade' => 1,
    'branch' => '8620',
    'site_branch' => 
    array (
      'id' => '8620',
      'name' => '插件',
      'aid' => '6903',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.6',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.6',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      8620 => 
      array (
        'id' => '8620',
        'name' => '插件',
        'aid' => '6903',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.6',
      ),
    ),
  ),
  'tg_forfive' => 
  array (
    'id' => '3585',
    'uid' => '125755',
    'name' => 'tg_forfive',
    'title' => '简易五子棋',
    'status' => '1',
    'description' => '该小游戏全由canvas绘制而成，初期版本画风简洁、朴素，设置触发关键字即可进入玩一玩小游戏啦',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/02/19/cCWD248qdCGkIraC58a993be74fab.jpg',
    'author' => 'tgxjz2016',
    'trade' => 1,
    'branch' => '4654',
    'site_branch' => 
    array (
      'id' => '4654',
      'name' => '娱乐',
      'aid' => '3585',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.0',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.0',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      4654 => 
      array (
        'id' => '4654',
        'name' => '娱乐',
        'aid' => '3585',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.0',
      ),
    ),
  ),
  'we7_android' => 
  array (
    'id' => '10680',
    'uid' => '76052',
    'name' => 'we7_android',
    'title' => '微擎android示例',
    'status' => '1',
    'description' => 'android官方示例源码地址https://gitee.com/we7coreteam/demo_android/tree/master/android',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/22/15217068555ab36767802d2_a58ux7A2m5t8.png',
    'author' => '微擎团队',
    'trade' => 1,
    'branch' => '12960',
    'site_branch' => 
    array (
      'id' => '12960',
      'name' => '普通版',
      'aid' => '10680',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '2',
      'ios_support' => '1',
      'version' => '1.1',
      'bought' => 
      array (
        0 => 'android',
      ),
    ),
    'version' => '1.1',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      12960 => 
      array (
        'id' => '12960',
        'name' => '普通版',
        'aid' => '10680',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '2',
        'ios_support' => '1',
        'version' => '1.1',
      ),
    ),
  ),
  'we7_coupon' => 
  array (
    'id' => '3075',
    'uid' => '76052',
    'name' => 'we7_coupon',
    'title' => '系统卡券',
    'status' => '1',
    'description' => '系统卡券模块已经停止维护，系统卡券所有功能都转移到万能小店里，系统卡券只修改严重的BUG万能小店购买链接：https://s.we7.cc/module-3717.html商户可按照其需求对会员卡进行...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2016/12/09/1481254171584a251b870ac_suOvk0vhuC35.png',
    'author' => '微擎团队',
    'trade' => 1,
    'branch' => '4020',
    'site_branch' => 
    array (
      'id' => '4020',
      'name' => '普通版',
      'aid' => '3075',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '7.0',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '7.0',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      4020 => 
      array (
        'id' => '4020',
        'name' => '普通版',
        'aid' => '3075',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '7.0',
      ),
    ),
  ),
  'we7_diyspecial' => 
  array (
    'id' => '3077',
    'uid' => '76052',
    'name' => 'we7_diyspecial',
    'title' => '专题页面',
    'status' => '1',
    'description' => '详细说明请点击这里',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2016/11/25/148006512758380068193d5_KGagBdkmkAgC.png',
    'author' => '微擎团队',
    'trade' => 1,
    'branch' => '4022',
    'site_branch' => 
    array (
      'id' => '4022',
      'name' => '普通版',
      'aid' => '3077',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.5',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '1.5',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      4022 => 
      array (
        'id' => '4022',
        'name' => '普通版',
        'aid' => '3077',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.5',
      ),
    ),
  ),
  'we7_enterprise' => 
  array (
    'id' => '9004',
    'uid' => '76052',
    'name' => 'we7_enterprise',
    'title' => '微企业',
    'status' => '1',
    'description' => '源码地址：https://gitee.com/we7coreteam/demo_pc&nbsp;官方演示应用，只有简单功能，仅作为开发者开发示例。该应用支持PC端使用一、前台页面首页功能：幻灯片，最新...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/01/12/15157262255a5825913a376_xRe2Izq9hzIE.jpg',
    'author' => '微擎团队',
    'trade' => 1,
    'branch' => '11033',
    'site_branch' => 
    array (
      'id' => '11033',
      'name' => 'pc',
      'aid' => '9004',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '1',
      'webapp_support' => '2',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '6.0',
      'bought' => 
      array (
        0 => 'webapp',
        1 => 'app',
      ),
    ),
    'version' => '6.0',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      11017 => 
      array (
        'id' => '11017',
        'name' => '普通版',
        'aid' => '9004',
        'displayorder' => 0,
        'status' => 1,
        'show' => 0,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.5',
      ),
      11033 => 
      array (
        'id' => '11033',
        'name' => 'pc',
        'aid' => '9004',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '1',
        'webapp_support' => '2',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '6.0',
      ),
    ),
  ),
  'we7_systemwelcome' => 
  array (
    'id' => '8741',
    'uid' => '76052',
    'name' => 'we7_systemwelcome',
    'title' => '系统首页',
    'status' => '1',
    'description' => '源码地址：https://gitee.com/we7coreteam/demo_systemWelcome官方演示应用，只有简单功能，仅作为开发者开发示例。该应用支持自定义微擎首页应用展示。 仅支持商...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/01/12/15157263075a5825e35c965_Fv6vz6Y6Z01l.jpg',
    'author' => '微擎团队',
    'trade' => 1,
    'branch' => '10718',
    'site_branch' => 
    array (
      'id' => '10718',
      'name' => '示例版',
      'aid' => '8741',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '2',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '0.6',
      'bought' => 
      array (
        0 => 'system_welcome',
      ),
    ),
    'version' => '0.6',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      10718 => 
      array (
        'id' => '10718',
        'name' => '示例版',
        'aid' => '8741',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '2',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '0.6',
      ),
    ),
  ),
  'we7_wmall' => 
  array (
    'id' => '1495',
    'uid' => '38439',
    'name' => 'we7_wmall',
    'title' => '啦啦外卖跑腿',
    'status' => '1',
    'description' => '因啦啦外卖版本众多，请勿在应用商城内自行下单购买，下单前请务必联系在线客服咨询购买版本等事宜；客服QQ:2622178042注意啦啦外卖购买后可以永久使用，免费更新一年。次年收取1000元服务费/年更...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2016/11/17/1479347095582d0b97e2188_zGVrER9DruHX.jpg',
    'author' => 'phpcoder',
    'trade' => 1,
    'branch' => '10301',
    'site_branch' => 
    array (
      'id' => '10301',
      'name' => '加强版',
      'aid' => '1495',
      'displayorder' => 4,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '1',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '6.0.0',
      'bought' => 
      array (
        0 => 'app',
      ),
    ),
    'version' => '6.0.0',
    'package_version' => NULL,
    'displayorder' => 4,
    'branches' => 
    array (
      10301 => 
      array (
        'id' => '10301',
        'name' => '加强版',
        'aid' => '1495',
        'displayorder' => 4,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '6.0.0',
      ),
      11157 => 
      array (
        'id' => '11157',
        'name' => '至尊版',
        'aid' => '1495',
        'displayorder' => 4,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '6.0.0',
      ),
      10299 => 
      array (
        'id' => '10299',
        'name' => '营销版',
        'aid' => '1495',
        'displayorder' => 2,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '4.0.0',
      ),
      2982 => 
      array (
        'id' => '2982',
        'name' => '标准版',
        'aid' => '1495',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'service_price' => '0',
        'version' => '3.1',
      ),
      2128 => 
      array (
        'id' => '2128',
        'name' => '更新年费',
        'aid' => '1495',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.0',
      ),
      9642 => 
      array (
        'id' => '9642',
        'name' => '小程序版（可独立运营）',
        'aid' => '1495',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.9.9',
      ),
    ),
  ),
  'we7_wxappdemo' => 
  array (
    'id' => '4357',
    'uid' => '76052',
    'name' => 'we7_wxappdemo',
    'title' => '微擎小程序模板',
    'status' => '1',
    'description' => '微擎小程序开发者模板，一些简单测试功能，普通用户使用不推荐下载使用，无实用功能！！！！',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/05/22/1495419379592249f3a59a6_R1181re6jX7J.png',
    'author' => '微擎团队',
    'trade' => 1,
    'branch' => '5665',
    'site_branch' => 
    array (
      'id' => '5665',
      'name' => '开发者测试版',
      'aid' => '4357',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.03',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.03',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      5665 => 
      array (
        'id' => '5665',
        'name' => '开发者测试版',
        'aid' => '4357',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.03',
      ),
    ),
  ),
  'we7_wxappsample' => 
  array (
    'id' => '9440',
    'uid' => '76052',
    'name' => 'we7_wxappsample',
    'title' => '微擎小程序模块示例',
    'status' => '1',
    'description' => '源码地址 ：&nbsp;https://gitee.com/we7coreteam/demo_wxapp小程序官网示例 增删改查 上传图片 支付',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/01/26/15169374855a6aa10d82242_T5SlH51111Er.png',
    'author' => '微擎团队',
    'trade' => 1,
    'branch' => '11522',
    'site_branch' => 
    array (
      'id' => '11522',
      'name' => '普通版',
      'aid' => '9440',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.3',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.3',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      11522 => 
      array (
        'id' => '11522',
        'name' => '普通版',
        'aid' => '9440',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.3',
      ),
    ),
  ),
  'we7_wxappsport' => 
  array (
    'id' => '4428',
    'uid' => '111203',
    'name' => 'we7_wxappsport',
    'title' => '微信运动助手小程序',
    'status' => '1',
    'description' => '&nbsp; 微信运动小程序appid：wx2a443f0568963809企业官网小程序推荐：http://s.we7.cc/module-5698.html微信运动助手小程序使用说明：1.必须开启...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/05/27/14958756115929401bc5972_lXZu2sWF5ZxA.jpg',
    'author' => 'kobe',
    'trade' => 1,
    'branch' => '5748',
    'site_branch' => 
    array (
      'id' => '5748',
      'name' => '普通版',
      'aid' => '4428',
      'displayorder' => 0,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.7',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.7',
    'package_version' => NULL,
    'displayorder' => 0,
    'branches' => 
    array (
      5748 => 
      array (
        'id' => '5748',
        'name' => '普通版',
        'aid' => '4428',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.7',
      ),
    ),
  ),
  'wn_storex' => 
  array (
    'id' => '3717',
    'uid' => '157135',
    'name' => 'wn_storex',
    'title' => '万能小店',
    'status' => '1',
    'description' => '注意：万能小店模块售前咨询现已外包给微擎官方客服，咨询可以直接联系官方客服，技术问题请联系qq：2938226187万能小店现已与微擎官方合作，商业版用户可以免费使用万能小店模块（仅限公众号版本）；系...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/04/19/149258134458f6fbe08f2be_eN8ju0OaPbSb.jpg',
    'author' => '万能君',
    'trade' => 1,
    'branch' => '4828',
    'site_branch' => 
    array (
      'id' => '4828',
      'name' => '普通版',
      'aid' => '3717',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '2.8.9',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '2.8.9',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      6288 => 
      array (
        'id' => '6288',
        'name' => '系统卡券免费版',
        'aid' => '3717',
        'displayorder' => 0,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '1',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.8',
      ),
      4828 => 
      array (
        'id' => '4828',
        'name' => '普通版',
        'aid' => '3717',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '2.8.9',
      ),
    ),
  ),
  'wuhao_group' => 
  array (
    'id' => '5639',
    'uid' => '122314',
    'name' => 'wuhao_group',
    'title' => '群内通知',
    'status' => '1',
    'description' => '活动通知，报名，投票体验二维码：',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2017/08/29/150398684159a5049a00efa_xC3ii9bduykp.png',
    'author' => 'wuhao_mouse',
    'trade' => 1,
    'branch' => '7174',
    'site_branch' => 
    array (
      'id' => '7174',
      'name' => '基础版',
      'aid' => '5639',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '2',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '1.4',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '1.4',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      7174 => 
      array (
        'id' => '7174',
        'name' => '基础版',
        'aid' => '5639',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '2',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '1.4',
      ),
    ),
  ),
  'yige_tzcgw' => 
  array (
    'id' => '10159',
    'uid' => '191903',
    'name' => 'yige_tzcgw',
    'title' => '挑战猜歌王',
    'status' => '1',
    'description' => '挑战猜歌王小程序。小程序游戏矩阵：猜歌：http://s.we7.cc/module-10159.html猜谜：http://s.we7.cc/module-10058.html猜图：http://s...',
    'plugin_pid' => '0',
    'package_support' => '1',
    'thumb' => '//cdn.w7.cc/images/2018/03/05/15202388195a9d00e3c0dba_A026EY668BGS.png',
    'author' => '微擎一哥',
    'trade' => 1,
    'branch' => '12374',
    'site_branch' => 
    array (
      'id' => '12374',
      'name' => '多开商业版',
      'aid' => '10159',
      'displayorder' => 1,
      'status' => 1,
      'show' => 1,
      'package_id' => '0',
      'private' => '1',
      'app_support' => '1',
      'wxapp_support' => '2',
      'webapp_support' => '1',
      'system_welcome_support' => '1',
      'android_support' => '1',
      'ios_support' => '1',
      'version' => '7.0',
      'bought' => 
      array (
        0 => 'wxapp',
      ),
    ),
    'version' => '7.0',
    'package_version' => NULL,
    'displayorder' => 1,
    'branches' => 
    array (
      12374 => 
      array (
        'id' => '12374',
        'name' => '多开商业版',
        'aid' => '10159',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '7.0',
      ),
      12373 => 
      array (
        'id' => '12373',
        'name' => '单开自用版',
        'aid' => '10159',
        'displayorder' => 1,
        'status' => 1,
        'show' => 1,
        'package_id' => '0',
        'private' => '1',
        'app_support' => '1',
        'wxapp_support' => '2',
        'webapp_support' => '1',
        'system_welcome_support' => '1',
        'android_support' => '1',
        'ios_support' => '1',
        'version' => '7.0',
      ),
    ),
  ),
  'pirate_apps' => 
  array (
    0 => 'weihaom_wb',
  ),
);
	unset($cloud_m_query_module['pirate_apps']);
	
	if (!empty($modulelist)) {
		foreach ($modulelist as $modulename => $module_local) {
			$module_upgrade_data = array(
				'name' => $modulename,
				'has_new_version' => 0,
				'has_new_branch' => 0,
				'lastupdatetime' => TIMESTAMP,
			);
			$module_cloud = $cloud_m_query_module[$modulename];
			
			if (version_compare($module_local['version'], $module_cloud['version']) == '-1') {
				$module_upgrade_data['has_new_version'] = 1;
				
				$result[$modulename]['new_version'] = 1;
				$result[$modulename]['best_version'] = $module_cloud['version'];

			}
			if (!empty($module_cloud['branches'])) {
				$module_upgrade_data['has_new_branch'] = 1;
				$result[$modulename]['new_branch'] = 1;
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