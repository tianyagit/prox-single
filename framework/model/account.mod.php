<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 添加公众号时执行数量判断
 * @param int $uid 操作用户
 * @param int $type 公众号类型 (1. 主公众号; 2. 子公众号; 4. 小程序)
 * @return array|boolean 错误原因或成功
 */
function uni_create_permission($uid, $type = ACCOUNT_TYPE_OFFCIAL_NORMAL) {
	$groupid = pdo_fetchcolumn('SELECT groupid FROM ' . tablename('users') . ' WHERE uid = :uid', array(':uid' => $uid));
	$groupdata = pdo_fetch('SELECT maxaccount, maxsubaccount, maxwxapp FROM ' . tablename('users_group') . ' WHERE id = :id', array(':id' => $groupid));
	$list = pdo_fetchall('SELECT d.type, count(*) AS count FROM (SELECT u.uniacid, a.default_acid FROM ' . tablename('uni_account_users') . ' as u RIGHT JOIN '. tablename('uni_account').' as a  ON a.uniacid = u.uniacid  WHERE u.uid = :uid AND u.role = :role ) AS c LEFT JOIN '.tablename('account').' as d ON c.default_acid = d.acid WHERE d.isdeleted = 0 GROUP BY d.type', array(':uid' => $uid, ':role' => 'owner'));
	foreach ($list as $item) {
		if ($item['type'] == ACCOUNT_TYPE_APP_NORMAL) {
			$wxapp_num = $item['count'];
		} else {
			$account_num = $item['count'];
		}
	}
	//添加主公号
	if ($type == ACCOUNT_TYPE_OFFCIAL_NORMAL || $type == ACCOUNT_TYPE_OFFCIAL_AUTH) {
		if ($account_num >= $groupdata['maxaccount']) {
			return error('-1', '您所在的用户组最多只能创建' . $groupdata['maxaccount'] . '个主公众号');
		}
	} elseif ($type == ACCOUNT_TYPE_APP_NORMAL) {
		if ($wxapp_num >= $groupdata['maxwxapp']) {
			return error('-1', '您所在的用户组最多只能创建' . $groupdata['maxwxapp'] . '个小程序');
		}
	}
	return true;
}

/**
 * 获取当前用户可操作的所有公众号
 * @param int $uid 指定操作用户
 * @return array
 */
function uni_owned($uid = 0) {
	global $_W;
	$uid = empty($uid) ? $_W['uid'] : intval($uid);
	$uniaccounts = array();
	$founders = explode(',', $_W['config']['setting']['founder']);
	if (in_array($uid, $founders)) {
		$uniaccounts = pdo_fetchall("SELECT * FROM " . tablename('uni_account') . " ORDER BY `uniacid` DESC", array(), 'uniacid');
	} else {
		$uniacids = pdo_fetchall("SELECT uniacid FROM " . tablename('uni_account_users') . " WHERE uid = :uid", array(':uid' => $uid), 'uniacid');
		if (!empty($uniacids)) {
			$uniaccounts = pdo_fetchall("SELECT * FROM " . tablename('uni_account') . " WHERE uniacid IN (" . implode(',', array_keys($uniacids)) . ") ORDER BY `uniacid` DESC", array(), 'uniacid');
		}
	}
	
	return $uniaccounts;
}

/**
 * 获取当前公号的所有子公众号
 * @param int $uniacid 公众号ID
 * @return array 当前公号下所有子公众号
 */
function uni_accounts($uniacid = 0) {
	global $_W;
	$uniacid = empty($uniacid) ? $_W['uniacid'] : intval($uniacid);
	$account_info = pdo_get('account', array('uniacid' => $uniacid));
	if (!empty($account_info)) {
		$accounts = pdo_fetchall("SELECT w.*, a.type, a.isconnect FROM " . tablename('account') . " a INNER JOIN " . tablename(uni_account_tablename($account_info['type'])) . " w USING(acid) WHERE a.uniacid = :uniacid AND a.isdeleted <> 1 ORDER BY a.acid ASC", array(':uniacid' => $uniacid), 'acid');
	}
	return !empty($accounts) ? $accounts : array();
}

/**
 * 获取指定统一公号下默认子号的的信息
 * @param int $uniacid 公众号ID
 * @return array 当前公众号信息
 */
function uni_fetch($uniacid = 0) {
	global $_W;
	$uniacid = empty($uniacid) ? $_W['uniacid'] : intval($uniacid);
	$cachekey = "uniaccount:{$uniacid}";
	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		if(!isset($cache['isconnect'])){
			$cache['isconnect'] = pdo_fetchcolumn('SELECT isconnect FROM ' . tablename('account') . ' WHERE uniacid = :uniacid', array(':uniacid' => $uniacid));
			cache_write($cachekey, $cache);
		}
		return $cache;
	}
	$account = uni_account_default($uniacid);
	$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $uniacid));
	load()->model('user');
	$owner = user_single(array('uid' => $owneruid));
	$account['uid'] = $owner['uid'];
	$account['starttime'] = $owner['starttime'];
	$account['endtime'] = $owner['endtime'];
	load()->model('mc');
	$account['groups'] = mc_groups($uniacid);
	$account['grouplevel'] = pdo_fetchcolumn('SELECT grouplevel FROM ' . tablename('uni_settings') . ' WHERE uniacid = :uniacid', array(':uniacid' => $uniacid));
	cache_write($cachekey, $account);
	return $account;
}

/**
 * 获取当前公号下所有安装模块及模块信息
 * 公众号的权限是owner所有套餐内的全部模块权限
 * @param boolean $enabledOnly 是否只显示可用模块
 * @return array 模块列表
 */
function uni_modules($enabledOnly = true) {
	global $_W;
	load()->model('user');
	load()->model('module');

	$cachekey = cache_system_key("unimodules:{$_W['uniacid']}:{$enabledOnly}");
	$modules = cache_load($cachekey);

	if (empty($modules)) {
		$founders = explode(',', $_W['config']['setting']['founder']);
		$owner_uid = pdo_getcolumn('uni_account_users',  array('uniacid' => $_W['uniacid'], 'role' => 'owner'), 'uid');

		if (empty($owner_uid) || in_array($owner_uid, $founders)) {
			$module_list = user_modules($owner_uid);
		} else {
			$uni_modules = array();
			$packageids = pdo_getall('uni_account_group', array('uniacid' => $_W['uniacid']), array('groupid'), 'groupid');
			$packageids = array_keys($packageids);

			if (!empty($packageids) && in_array('-1', $packageids)) {
				$module_list = pdo_getall('modules', array(), array('name', 'issystem'), 'name', array('issystem DESC', 'mid DESC'));
			} else {
				$uni_groups = pdo_fetchall("SELECT `modules` FROM " . tablename('uni_group') . " WHERE " .  "id IN ('".implode("','", $packageids)."') OR " . " uniacid = '{$_W['uniacid']}'");
				if (!empty($uni_groups)) {
					foreach ($uni_groups as $group) {
						$group_module = (array)iunserializer($group['modules']);
						$uni_modules = array_merge($group_module, $uni_modules);
					}
				}
				$user_modules = user_modules($owner_uid);
				$modules = array_merge(array_keys($user_modules), $uni_modules);
				$module_list = pdo_getall('modules', array('name' => $modules), array('name', 'issystem'), 'name', array('mid DESC'));
			}
		}

		if (!empty($module_list)) {
			$modules = array();
			if (pdo_tableexists('modules_plugin')) {
				$plugin_list = pdo_getall('modules_plugin', array('name' => array_keys($module_list)), array());
			}
			$have_plugin_module = array();
			if (!empty($plugin_list)) {
				foreach ($plugin_list as $plugin) {
					$have_plugin_module[$plugin['main_module']][$plugin['name']] = $plugin['name'];
					unset($module_list[$plugin['name']]);
				}
			}

			$my_modules = pdo_getall('uni_account_modules', array('uniacid' => $_W['uniacid'], 'module' => array_keys($module_list)), array(), 'module', array('enabled DESC'));

			foreach ($module_list as $name => $module) {
				if (!empty($my_modules)) {
					if ($enabledOnly && !$module_list[$name]['issystem'] && ($my_modules[$name]['enabled'] == 0 || empty($my_modules[$name]))) {
						continue;
					}
					$module = array(
						'name' => $name,
						'enabled' => $module_list[$name]['issystem'] ? 1 : $my_modules[$name]['enabled']
					);
					if (!empty($my_modules[$name]['settings'])) {
						$module['config'] = iunserializer($my_modules[$name]['settings']);
					}
				}
				$modules[$name] = $module;
				if (!empty($have_plugin_module[$name])) {
					foreach ($have_plugin_module[$name] as $plugin) {
						$modules[$plugin] = $plugin;
					}
				}
			}
			unset($module);
		}
		cache_write($cachekey, $modules);
	}

	$module_list = array();

	if (!empty($modules)) {
		foreach ($modules as $name => $module) {
			$module_list[$name] = module_fetch($name);
			$module_list[$name]['config'] = empty($module['config']) ? array() : $module['config'];
			$module_list[$name]['enabled'] = empty($module['enabled']) ? 0 : $module['enabled'];
		}
	}
	$module_list['core'] = array('title' => '系统事件处理模块', 'name' => 'core', 'issystem' => 1, 'enabled' => 1, 'isdisplay' => 0);
	return $module_list;
}

function uni_modules_app_binding() {
	global $_W;
	$cachekey = "unimodulesappbinding:{$_W['uniacid']}";
	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		return $cache;
	}
	load()->model('module');
	$result = array();
	$modules = uni_modules();
	if(!empty($modules)) {
		foreach($modules as $module) {
			if($module['type'] == 'system') {
				continue;
			}
			$entries = module_app_entries($module['name'], array('home', 'profile', 'shortcut', 'function', 'cover'));
			if(empty($entries)) {
				continue;
			}
			if($module['type'] == '') {
				$module['type'] = 'other';
			}
			$result[$module['name']] = array(
				'name' => $module['name'],
				'type' => $module['type'],
				'title' => $module['title'],
				'entries' => array(
					'cover' => $entries['cover'],
					'home' => $entries['home'],
					'profile' => $entries['profile'],
					'shortcut' => $entries['shortcut'],
					'function' => $entries['function']
				)
			);
			unset($module);
		}
	}
	cache_write($cachekey, $result);
	return $result;
}

/**
 * 获取一个或多个公众号套餐信息
 * @param array $groupids 公众号套餐ID
 * @return array uni_group 套餐信息列表
 */
function uni_groups($groupids = array()) {
	load()->model('module');
	$condition = ' WHERE uniacid = 0';
	if (!is_array($groupids)) {
		$groupids = array($groupids);
	}
	if (!empty($groupids)) {
		foreach ($groupids as $i => $row) {
			$groupids[$i] = intval($row);
		}
		unset($row);
		$condition .= " AND id IN (" . implode(',', $groupids) . ")";
	}
	$list = pdo_fetchall("SELECT * FROM " . tablename('uni_group') . $condition . " ORDER BY id DESC", array(), 'id');
	if (in_array('-1', $groupids)) {
		$list[-1] = array('id' => -1, 'name' => '所有服务');
	}
	if (in_array('0', $groupids)) {
		$list[0] = array('id' => 0, 'name' => '基础服务');
	}
	if (!empty($list)) {
		foreach ($list as $k=>&$row) {
			if (!empty($row['modules'])) {
				$modules = iunserializer($row['modules']);
				if (is_array($modules)) {
					$module_list = pdo_getall('modules', array('name' => $modules), array(), 'name');
					$row['modules'] = array();
					if (!empty($module_list)) {
						foreach ($module_list as $key => &$module) {
							$module = module_fetch($key);
							if ($module['wxapp_support'] == 2) {
								$row['wxapp'][$module['name']] = $module;
							}
							if ($module['app_support'] == 2) {
								if (!empty($module['main_module'])) {
									continue;
								}
								$row['modules'][$module['name']] = $module;
								if (!empty($module['plugin'])) {
									$group_have_plugin = array_intersect($module['plugin'], array_keys($module_list));
									if (!empty($group_have_plugin)) {
										foreach ($group_have_plugin as $plugin) {
											$row['modules'][$plugin] = module_fetch($plugin);
										}
									}
								}
							}
						}
					}
				}
			}

			if (!empty($row['templates'])) {
				$templates = iunserializer($row['templates']);
				if (is_array($templates)) {
					$row['templates'] = pdo_fetchall("SELECT name, title FROM " . tablename('site_templates') . " WHERE id IN ('" . implode("','", $templates) . "')");
				}
			}
		}
	}
	return $list;
}

/**
 * 获取当前套餐可用微站模板
 * @return array 模板列表
 */
function uni_templates() {
	global $_W;
	$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $_W['uniacid']));
	load()->model('user');
	$owner = user_single(array('uid' => $owneruid));
	//如果没有所有者，则取创始人权限
	if (empty($owner)) {
		$groupid = '-1';
	} else {
		$groupid = $owner['groupid'];
	}
	$extend = pdo_getall('uni_account_group', array('uniacid' => $_W['uniacid']), array(), 'groupid');
	if (!empty($extend)) {
		$groupid = '-2';
	}
	if (empty($groupid)) {
		$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') . " WHERE name = 'default'", array(), 'id');
	} elseif ($groupid == '-1') {
		$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') . " ORDER BY id ASC", array(), 'id');
	} else {
		$group = pdo_fetch("SELECT id, name, package FROM ".tablename('users_group')." WHERE id = :id", array(':id' => $groupid));
		$packageids = iunserializer($group['package']);
		if (!empty($extend)) {
			foreach ($extend as $extend_packageid => $row) {
				$packageids[] = $extend_packageid;
			}
		}
		if(is_array($packageids)) {
			if (in_array('-1', $packageids)) {
				$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') . " ORDER BY id ASC", array(), 'id');
			} else {
				$wechatgroup = pdo_fetchall("SELECT `templates` FROM " . tablename('uni_group') . " WHERE id IN ('".implode("','", $packageids)."') OR uniacid = '{$_W['uniacid']}'");
				$ms = array();
				$mssql = '';
				if (!empty($wechatgroup)) {
					foreach ($wechatgroup as $row) {
						$row['templates'] = iunserializer($row['templates']);
						if (!empty($row['templates'])) {
							foreach ($row['templates'] as $templateid) {
								$ms[$templateid] = $templateid;
							}
						}
					}
					$ms[] = 1;
					$mssql = " `id` IN ('".implode("','", $ms)."')";
				}
				$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') .(!empty($mssql) ? " WHERE $mssql" : '')." ORDER BY id DESC", array(), 'id');
			}
		}
	}
	if (empty($templates)) {
		$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') . " WHERE id = 1 ORDER BY id DESC", array(), 'id');
	}
	return $templates;
}

/**
 * 保存公众号的配置数据
 * @param string $name
 * @param mixed $value
 * @return boolean
 */
function uni_setting_save($name, $value) {
	global $_W;
	if (empty($name)) {
		return false;
	}
	if (is_array($value)) {
		$value = serialize($value);
	}
	$unisetting = pdo_get('uni_settings', array('uniacid' => $_W['uniacid']), array('uniacid'));
	if (!empty($unisetting)) {
		pdo_update('uni_settings', array($name => $value), array('uniacid' => $_W['uniacid']));
	} else {
		pdo_insert('uni_settings', array($name => $value, 'uniacid' => $_W['uniacid']));
	}
	$cachekey = "unisetting:{$_W['uniacid']}";
	cache_delete($cachekey);
	return true;
}

/**
 * 获取公众号的配置项
 * @param string | array $name
 * @param int $uniacid 统一公号id, uniacid
 * @return array 设置项
 */
function uni_setting_load($name = '', $uniacid = 0) {
	global $_W;
	$uniacid = empty($uniacid) ? $_W['uniacid'] : $uniacid;
	$cachekey = "unisetting:{$uniacid}";
	$unisetting = cache_load($cachekey);
	if (empty($unisetting)) {
		$unisetting = pdo_get('uni_settings', array('uniacid' => $uniacid));
		if (!empty($unisetting)) {
			$serialize = array('site_info', 'stat', 'oauth', 'passport', 'uc', 'notify', 
								'creditnames', 'default_message', 'creditbehaviors', 'payment', 
								'recharge', 'tplnotice', 'mcplugin');
			foreach ($unisetting as $key => &$row) {
				if (in_array($key, $serialize) && !empty($row)) {
					$row = (array)iunserializer($row);
				}
			}
		}
		cache_write($cachekey, $unisetting);
	}
	if (empty($unisetting)) {
		return array();
	}
	if (empty($name)) {
		return $unisetting;
	}
	if (!is_array($name)) {
		$name = array($name);
	}
	return array_elements($name, $unisetting);
}

if (!function_exists('uni_setting')) {
	function uni_setting($uniacid = 0, $fields = '*', $force_update = false) {
		global $_W;
		load()->model('account');
		if ($fields == '*') {
			$fields = '';
		}
		return uni_setting_load($fields, $uniacid);
	}
}

/**
 * 获取当前公号的默认子号，如果未指定则获取第一个公众号为默认子号
 * @param int $uniacid 公众号ID
 * @return array 当前公号下的默认子号信息
 */
function uni_account_default($uniacid = 0) {
	global $_W;
	$uniacid = empty($uniacid) ? $_W['uniacid'] : intval($uniacid);
	$uni_account = pdo_fetch("SELECT * FROM ".tablename('uni_account')." a LEFT JOIN ".tablename('account')." w ON a.default_acid = w.acid WHERE a.uniacid = :uniacid", array(':uniacid' => $uniacid), 'acid');
	if (!empty($uni_account)) {
		$account = pdo_get(uni_account_tablename($uni_account['type']), array('acid' => $uni_account['acid']));
		$account['type'] = $uni_account['type'];
		$account['isconnect'] = $uni_account['isconnect'];
		return $account;
	}
}
/**
 * 根据公众号类型选择数据表
 * @param unknown $type
 */
function uni_account_tablename($type) {
	switch ($type) {
		case ACCOUNT_TYPE_OFFCIAL_NORMAL:
		case ACCOUNT_TYPE_OFFCIAL_AUTH:
			return 'account_wechats';
		case ACCOUNT_TYPE_APP_NORMAL:
			return 'account_wxapp';
	}
}

/**
 * 获取指定操作用户在指定的公众号所具有的操作权限
 * @param int $uid 操作用户
 * @param int $uniacid 指定统一公众号
 * @return string 操作用户的 role (manager|operator)
 */
function uni_permission($uid = 0, $uniacid = 0) {
	global $_W;
	$uid = empty($uid) ? $_W['uid'] : intval($uid);
	
	$founders = explode(',', $_W['config']['setting']['founder']);
	if (in_array($uid, $founders)) {
		return ACCOUNT_MANAGE_NAME_FOUNDER;
	}
	if (!empty($uniacid)) {
		$role = pdo_getcolumn('uni_account_users', array('uid' => $uid, 'uniacid' => $uniacid), 'role');
		if ($role == ACCOUNT_MANAGE_NAME_OWNER) {
			$role = ACCOUNT_MANAGE_NAME_OWNER;
		} elseif ($role == ACCOUNT_MANAGE_NAME_MANAGER) {
			$role = ACCOUNT_MANAGE_NAME_MANAGER;
		} else {
			$role = ACCOUNT_MANAGE_NAME_OPERATOR;
		}
	} else {
		$roles = pdo_getall('uni_account_users', array('uid' => $uid), array('role'), 'role');
		$roles = array_keys($roles);
		if (in_array(ACCOUNT_MANAGE_NAME_OWNER, $roles)) {
			$role = ACCOUNT_MANAGE_NAME_OWNER;
		} elseif (in_array(ACCOUNT_MANAGE_NAME_MANAGER, $roles)) {
			$role = ACCOUNT_MANAGE_NAME_MANAGER;
		} elseif (in_array(ACCOUNT_MANAGE_NAME_OPERATOR, $roles)) {
			$role = ACCOUNT_MANAGE_NAME_OPERATOR;
		} else {
			$role = ACCOUNT_MANAGE_NAME_OPERATOR;
		}
	}
	return $role;
}

/**
 * 判断某个用户在某个公众号是否配置过权限清单
 * @param number $uid
 * @param number $uniacid
 * @return boolean
 */
function uni_user_permission_exist($uid = 0, $uniacid = 0) {
	global $_W;
	$uid = intval($uid) > 0 ? $uid : $_W['uid'];
	$uniacid = intval($uniacid) > 0 ? $uniacid : $_W['uniacid'];
	if ($_W['role'] == 'founder') {
		return false;
	}
	if (FRAME == 'system') {
		return true;
	}
	$is_exist = pdo_get('users_permission', array('uid' => $uid, 'uniacid' => $uniacid), array('id'));
	if(empty($is_exist)) {
		return false;
	} else {
		return true;
	}
}

/*
 * 默认获取当前操作员对于某个公众号的权限
* $type => 'system' 获取系统菜单权限
* */
function uni_user_permission($type = 'system') {
	global $_W;
	$user_permission = pdo_getcolumn('users_permission', array('uid' => $_W['uid'], 'uniacid' => $_W['uniacid'], 'type' => $type), 'permission');
	if(!empty($user_permission)) {
		$user_permission = explode('|', $user_permission);
	} else {
		$user_permission = array('account*');
	}
	$permission_append = frames_menu_append();
	//目前只有系统管理才有预设权限，公众号权限走数据库
	if (!empty($permission_append[$_W['role']])) {
		$user_permission = array_merge($user_permission, $permission_append[$_W['role']]);
	}
	return (array)$user_permission;
}

function uni_user_permission_check($permission_name, $show_message = true, $action = '') {
	global $_W, $_GPC;
	$user_has_permission = uni_user_permission_exist();
	if (empty($user_has_permission)) {
		return true;
	}
	$modulename = trim($_GPC['m']);
	$do = trim($_GPC['do']);
	$entry_id = intval($_GPC['eid']);
	
	if($action == 'reply') {
		$system_modules = system_modules();
		if(!empty($modulename) && !in_array($modulename, $system_modules)) {
			$permission_name = $modulename . '_rule';
			$users_permission = uni_user_permission($modulename);
		}
	} elseif($action == 'cover' && $entry_id > 0) {
		load()->model('module');
		$entry = module_entry($entry_id);
		if(!empty($entry)) {
			$permission_name = $entry['module'] . '_cover_' . trim($entry['do']);
			$users_permission = uni_user_permission($entry['module']);
		}
	} elseif($action == 'nav') {
		//只对模块的导航进行权限判断，不对微站的导航判断
		if(!empty($modulename)) {
			$permission_name = "{$modulename}_{$do}";
			$users_permission = uni_user_permission($modulename);
		} else {
			return true;
		}
	} else {
		$users_permission = uni_user_permission('system');
	}
	if(!isset($users_permission)) {
		$users_permission = uni_user_permission('system');
	}
	if($users_permission[0] != 'all' && !in_array($permission_name, $users_permission)) {
		if($show_message) {
			itoast('您没有进行该操作的权限', referer(), 'error');
		} else {
			return false;
		}
	}
	return true;
}

/*
 * 判断操作员是有具有模块某个业务功能菜单的权限
 * */
function uni_user_module_permission_check($action = '', $module_name = '') {
	global $_GPC;
	$status = uni_user_permission_exist();
	if(empty($status)) {
		return true;
	}
	$a = trim($_GPC['a']);
	$do = trim($_GPC['do']);
	$m = trim($_GPC['m']);
	//参数设置权限
	if ($a == 'module' && $do == 'setting' && !empty($m)) {
		$permission_name = $m . '_setting';
		$users_permission = uni_user_permission($m);
		if ($users_permission[0] != 'all' && !in_array($permission_name, $users_permission)) {
			return false;
		}
	//模块其他业务菜单
	} elseif (!empty($do) && !empty($m)) {
		$is_exist = pdo_get('modules_bindings', array('module' => $m, 'do' => $do, 'entry' => 'menu'), array('eid'));
		if(empty($is_exist)) {
			return true;
		}
	}
	if(empty($module_name)) {
		$module_name = IN_MODULE;
	}
	$permission = uni_user_permission($module_name);
	if(empty($permission) || ($permission[0] != 'all' && !empty($action) && !in_array($action, $permission))) {
		return false;
	}
	return true;
}

/**
 * 获取某个用户所在用户组可添加的主公号数量，已添加的数量，还可以添加的数量
 * */
function uni_user_account_permission() {
	global $_W;
	$group = pdo_fetch('SELECT * FROM ' . tablename('users_group') . ' WHERE id = :id', array(':id' => $_W['user']['groupid']));
	$uniacocunts = pdo_getall('uni_account_users', array('uid' => $_W['uid'], 'role' => 'owner'), array(), 'uniacid');
	if (empty($uniacocunts)) {
		$uniacid_num = 0;
	} else {
		//再次判断公众号是否真实存在
		$uniacid_num = pdo_fetchcolumn('SELECT COUNT(*) FROM (SELECT u.uniacid, a.default_acid FROM ' . tablename('uni_account_users') . ' as u RIGHT JOIN '. tablename('uni_account').' as a  ON a.uniacid = u.uniacid  WHERE u.uid = :uid AND u.role = :role ) AS c LEFT JOIN '.tablename('account').' as d ON c.default_acid = d.acid WHERE d.isdeleted = 0', array(':uid' => $_W['uid'], ':role' => 'owner'));
	}
	$data = array(
		'group_name' => $group['name'],
		'maxaccount' => $group['maxaccount'],
		'uniacid_num' => $uniacid_num,
		'uniacid_limit' => max((intval($group['maxaccount']) - $uniacid_num), 0),
	);
	return $data;
}

function uni_update_week_stat() {
	global $_W;
	$cachekey = "stat:todaylock:{$_W['uniacid']}";
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return true;
	}
	$seven_days = array(
		date('Ymd', strtotime('-1 days')),
		date('Ymd', strtotime('-2 days')),
		date('Ymd', strtotime('-3 days')),
		date('Ymd', strtotime('-4 days')),
		date('Ymd', strtotime('-5 days')),
		date('Ymd', strtotime('-6 days')),
		date('Ymd', strtotime('-7 days')),
	);
	$week_stat_fans = pdo_getall('stat_fans', array('date' => $seven_days, 'uniacid' => $_W['uniacid']), '', 'date');
	$stat_update_yes = false;
	foreach ($seven_days as $sevens) {
		if (empty($week_stat_fans[$sevens]) || $week_stat_fans[$sevens]['cumulate'] <=0) {
			$stat_update_yes = true;
			break;
		}
	}
	if (empty($stat_update_yes)) {
		return true;
	}
	foreach($seven_days as $sevens) {
		if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY || $_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
			$account_obj = WeAccount::create();
			$weixin_stat = $account_obj->getFansStat();
			if(is_error($weixin_stat) || empty($weixin_stat)) {
				return error(-1, '调用微信接口错误');
			} else {
				$update_stat = array();
				$update_stat = array(
					'uniacid' => $_W['uniacid'],
					'new' => $weixin_stat[$sevens]['new'],
					'cancel' => $weixin_stat[$sevens]['cancel'],
					'cumulate' => $weixin_stat[$sevens]['cumulate'],
					'date' => $sevens,
				);
			}
		} else {
			$update_stat = array();
			$update_stat['cumulate'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('mc_mapping_fans') . " WHERE acid = :acid AND uniacid = :uniacid AND follow = :follow AND followtime < :endtime", array(':acid' => $_W['acid'], ':uniacid' => $_W['uniacid'], ':endtime' => strtotime($sevens)+86400, ':follow' => 1));
			$update_stat['date'] = $sevens;
			$update_stat['new'] = $week_stat_fans[$sevens]['new'];
			$update_stat['cancel'] = $week_stat_fans[$sevens]['cancel'];
			$update_stat['uniacid'] = $_W['uniacid'];
		}
		if(empty($week_stat_fans[$sevens])) {
			pdo_insert('stat_fans', $update_stat);
		} elseif (empty($week_stat_fans[$sevens]['cumulate']) || $week_stat_fans[$sevens]['cumulate'] < 0) {
			pdo_update('stat_fans', $update_stat, array('id' => $week_stat_fans[$sevens]['id']));
		}
	}
	cache_write($cachekey, array('expire' => TIMESTAMP + 7200));
	return true;
}

/**
 * 创建子公众号
 * @param int $uniacid 指定统一公号
 * @param array $account 子公号信息
 * @return int 新创建的子公号 acid
 */
function account_create($uniacid, $account) {
	$accountdata = array('uniacid' => $uniacid, 'type' => $account['type'], 'hash' => random(8));
	pdo_insert('account', $accountdata);
	$acid = pdo_insertid();
	$account['acid'] = $acid;
	$account['token'] = random(32);
	$account['encodingaeskey'] = random(43);
	$account['uniacid'] = $uniacid;
	unset($account['type']);
	pdo_insert('account_wechats', $account);
	return $acid;
}

/**
 * 获取指定子公号信息
 * @param int $acid 子公号acid
 * @return array
 */
function account_fetch($acid) {
	$account_info = pdo_get('account', array('acid' => $acid));
	if (empty($account_info)) {
		return error(-1, '公众号不存在');
	}
	$account = pdo_fetch("SELECT w.*, a.type, a.isconnect FROM " . tablename('account') . " a INNER JOIN " . tablename(uni_account_tablename($account_info['type'])) . " w USING(acid) WHERE acid = :acid AND a.isdeleted = '0'", array(':acid' => $acid));
	if (empty($account)) {
		return error(1, '公众号不存在');
	}
	$uniacid = $account['uniacid'];
	$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $uniacid));
	load()->model('user');
	$owner = user_single(array('uid' => $owneruid));
	$account['uid'] = $owner['uid'];
	$account['starttime'] = $owner['starttime'];
	$account['endtime'] = $owner['endtime'];
	$account['thumb'] = tomedia('headimg_'.$account['acid']. '.jpg').'?time='.time();
	load()->model('mc');
	$account['groups'] = mc_groups($uniacid);
	$account['grouplevel'] = pdo_fetchcolumn('SELECT grouplevel FROM ' . tablename('uni_settings') . ' WHERE uniacid = :uniacid', array(':uniacid' => $uniacid));
	return $account;
}

/*
 * 获取某个公众号的所有人和套餐有效期限（如果没有所有人，默认属于创始人，服务创始人）
 * */
function uni_setmeal($uniacid = 0) {
	global $_W;
	if(!$uniacid) {
		$uniacid = $_W['uniacid'];
	}
	$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $uniacid));
	if(empty($owneruid)) {
		$user = array(
			'uid' => -1,
			'username' => '创始人',
			'timelimit' => '未设置',
			'groupid' => '-1',
			'groupname' => '所有服务'
		);
		return $user;
	}
	load()->model('user');
	$groups = pdo_getall('users_group', array(), array('id', 'name'), 'id');
	$owner = user_single(array('uid' => $owneruid));
	$user = array(
		'uid' => $owner['uid'],
		'username' => $owner['username'],
		'groupid' => $owner['groupid'],
		'groupname' => $groups[$owner['groupid']]['name']
	);
	if(empty($owner['endtime'])) {
		$user['timelimit'] = date('Y-m-d', $owner['starttime']) . ' ~ 无限制' ;
	} else {
		if($owner['endtime'] <= TIMESTAMP) {
			$user['timelimit'] = '已到期';
		} else {
			$year = 0;
			$month = 0;
			$day = 0;
			$endtime = $owner['endtime'];
			$time = strtotime('+1 year');
			while ($endtime > $time)
			{
				$year = $year + 1;
				$time = strtotime("+1 year", $time);
			};
			$time = strtotime("-1 year", $time);
			$time = strtotime("+1 month", $time);
			while($endtime > $time)
			{
				$month = $month + 1;
				$time = strtotime("+1 month", $time);
			} ;
			$time = strtotime("-1 month", $time);
			$time = strtotime("+1 day", $time);
			while($endtime > $time)
			{
				$day = $day + 1;
				$time = strtotime("+1 day", $time);
			} ;
			if (empty($year)) {
				$timelimit = empty($month)? $day.'天' : date('Y-m-d', $owner['starttime']) . '~'. date('Y-m-d', $owner['endtime']);
			}else {
				$timelimit = date('Y-m-d', $owner['starttime']) . '~'. date('Y-m-d', $owner['endtime']);
			}
			$user['timelimit'] = $timelimit;
		}
	}
	return $user;
}

/*
 * 检测公众号是否只有多个子号。如果有多个子号，返回true;
 * */
function uni_is_multi_acid($uniacid = 0) {
	global $_W;
	if(!$uniacid) {
		$uniacid = $_W['uniacid'];
	}
	$cachekey = "unicount:{$uniacid}";
	$nums = cache_load($cachekey);
	$nums = intval($nums);
	if(!$nums) {
		$nums = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('account_wechats') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
		cache_write($cachekey, $nums);
	}
	if($nums == 1) {
		return false;
	}
	return true;
}
/**
 * 删除公众号
 * @param string $acid 微信公众号的acid
 */
function account_delete($acid) {
	global $_W;
	load()->func('file');
	load()->model('module');
	//判断是不是主公众号
	$account = pdo_get('uni_account', array('default_acid' => $acid));
	if ($account) {
		$uniacid = $account['uniacid'];
		$state = uni_permission($_W['uid'], $uniacid);
		if($state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_OWNER) {
			itoast('没有该公众号操作权限！', url('account/recycle'), 'error');
		}
		if($uniacid == $_W['uniacid']) {
			isetcookie('__uniacid', '');
		}
		cache_delete("unicount:{$uniacid}");
		$modules = array();
		//获取全部规则
		$rules = pdo_fetchall("SELECT id, module FROM ".tablename('rule')." WHERE uniacid = '{$uniacid}'");
		if (!empty($rules)) {
			foreach ($rules as $index => $rule) {
				$deleteid[] = $rule['id'];
			}
			pdo_delete('rule', "id IN ('".implode("','", $deleteid)."')");
		}

		$subaccount = pdo_fetchall("SELECT acid FROM ".tablename('account')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
		if (!empty($subaccount)) {
			foreach ($subaccount as $account) {
				@unlink(IA_ROOT . '/attachment/qrcode_'.$account['acid'].'.jpg');
				@unlink(IA_ROOT . '/attachment/headimg_'.$account['acid'].'.jpg');
				file_remote_delete('qrcode_'.$account['acid'].'.jpg');
				file_remote_delete('headimg_'.$account['acid'].'.jpg');
			}
			if (!empty($acid)) {
				rmdirs(IA_ROOT . '/attachment/images/' . $uniacid);
				@rmdir(IA_ROOT . '/attachment/images/' . $uniacid);
				rmdirs(IA_ROOT . '/attachment/audios/' . $uniacid);
				@rmdir(IA_ROOT . '/attachment/audios/' . $uniacid);
			}
		}

		//遍历全部表删除公众号数据
		$tables = array(
			'account','account_wechats', 'account_wxapp', 'wxapp_versions', 'core_attachment','core_paylog','core_queue','core_resource',
			'wechat_attachment', 'cover_reply', 'mc_chats_record','mc_credits_recharge','mc_credits_record',
			'mc_fans_groups','mc_groups','mc_handsel','mc_mapping_fans','mc_mapping_ucenter','mc_mass_record',
			'mc_member_address','mc_member_fields','mc_members','menu_event',
			'qrcode','qrcode_stat', 'rule','rule_keyword','site_article','site_category','site_multi','site_nav','site_slide',
			'site_styles','site_styles_vars','stat_keyword','stat_msg_history',
			'stat_rule','uni_account','uni_account_modules','uni_account_users','uni_settings', 'uni_group', 'uni_verifycode','users_permission',
			'mc_member_fields',
		);
		if (!empty($tables)) {
			foreach ($tables as $table) {
				$tablename = str_replace($GLOBALS['_W']['config']['db']['tablepre'], '', $table);
				pdo_delete($tablename, array( 'uniacid'=> $uniacid));
			}
		}
	} else {
		$account = account_fetch($acid);
		if (empty($account)) {
			itoast('子公众号不存在或是已经被删除', '', '');
		}
		$uniacid = $account['uniacid'];
		$state = uni_permission($_W['uid'], $uniacid);
		if($state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_OWNER) {
			itoast('没有该公众号操作权限！', url('account/recycle'), 'error');
		}
		$uniaccount = uni_fetch($account['uniacid']);
		if ($uniaccount['default_acid'] == $acid) {
			itoast('默认子公众号不能删除', '', '');
		}
		pdo_delete('account', array('acid' => $acid));
		pdo_delete('account_wechats', array('acid' => $acid, 'uniacid' => $uniacid));
		cache_delete("unicount:{$uniacid}");
		cache_delete("unisetting:{$uniacid}");
		cache_delete('account:auth:refreshtoken:'.$acid);
		$oauth = uni_setting($uniacid, array('oauth'));
		if($oauth['oauth']['account'] == $acid) {
			$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . " WHERE uniacid = :id AND level = 4 AND secret != '' AND `key` != ''", array(':id' => $uniacid));
			pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
		}
		@unlink(IA_ROOT . '/attachment/qrcode_'.$acid.'.jpg');
		@unlink(IA_ROOT . '/attachment/headimg_'.$acid.'.jpg');
		file_remote_delete('qrcode_'.$acid.'.jpg');
		file_remote_delete('headimg_'.$acid.'.jpg');
	}
	return true;
}

/**
 * 获取所有可借用支付的公众号
 * @return array() 微信支付可借用的的公众号和服务商公众号
 */
function account_wechatpay_proxy () {
	global $_W;
	$proxy_account = cache_load(cache_system_key('proxy_wechatpay_account:'));
	if (empty($proxy_account)) {
		$proxy_account = cache_build_proxy_wechatpay_account();
	}
	unset($proxy_account['borrow'][$_W['uniacid']]);
	unset($proxy_account['service'][$_W['uniacid']]);
	return $proxy_account;
}
