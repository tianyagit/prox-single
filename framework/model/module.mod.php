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
	$fields = implode("','", $types);
	$sql = 'SELECT * FROM ' . tablename('modules_bindings')." WHERE `module`=:module AND `entry` IN ('{$fields}') ORDER BY displayorder DESC, eid ASC";
	$pars = array();
	$pars[':module'] = $name;
	$bindings = pdo_fetchall($sql, $pars);
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
				$url = wurl("platform/cover", array('eid' => $bind['eid']));
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
	$fields = implode("','", $types);
	$sql = 'SELECT * FROM ' . tablename('modules_bindings')." WHERE `module`=:module AND `entry` IN ('{$fields}') ORDER BY eid ASC";
	$pars = array();
	$pars[':module'] = $name;
	$bindings = pdo_fetchall($sql, $pars);
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
 * 获取当前公号下安装好的指定模块及模块信息
 *
 * @param string $name 模块名称
 * @return array 模块信息
 */
function module_fetch($name) {
	load()->model('account');
	$modules = uni_modules();
	return $modules[$name];
}

/**
 * 检验并完善公众号的模块设置信息
 * 安装模块或添加公众号时调用.
 */
function module_build_privileges() {
	$uniacid_arr = pdo_fetchall('SELECT uniacid FROM ' . tablename('uni_account'));
	foreach($uniacid_arr as $row){
		$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $row['uniacid']));
		load()->model('user');
		$owner = user_single(array('uid' => $owneruid));
		//如果没有所有者，则取创始人权限
		if (empty($owner)) {
			$groupid = '-1';
		} else {
			$groupid = $owner['groupid'];
		}
		$modules = array();
		if (empty($groupid)) {
			return true;
		} elseif ($groupid == '-1') {
			$modules = pdo_fetchall("SELECT name FROM " . tablename('modules') . ' WHERE issystem = 0', array(), 'name');
		} else {
			$group = pdo_fetch("SELECT id, name, package FROM ".tablename('users_group')." WHERE id = :id", array(':id' => $groupid));
			$packageids = iunserializer($group['package']);
			if(empty($packageids)) {
				return true;
			}
			if (in_array('-1', $packageids)) {
				$modules = pdo_fetchall("SELECT name FROM " . tablename('modules') . ' WHERE issystem = 0', array(), 'name');
			} else {
				$wechatgroup = pdo_fetchall("SELECT `modules` FROM " . tablename('uni_group') . " WHERE id IN ('".implode("','", $packageids)."') OR uniacid = '{$row['uniacid']}'");
				if (!empty($wechatgroup)) {
					foreach ($wechatgroup as $li) {
						$li['modules'] = iunserializer($li['modules']);
						if (!empty($li['modules'])) {
							foreach ($li['modules'] as $modulename) {
								$modules[$modulename] = $modulename;
							}
						}
					}
				}
			}
		}
		$modules = array_keys($modules);
		//得到模块标识
		$mymodules = pdo_fetchall("SELECT `module` FROM ".tablename('uni_account_modules')." WHERE uniacid = '{$row['uniacid']}' ORDER BY enabled DESC ", array(), 'module');
		$mymodules = array_keys($mymodules);
		foreach($modules as $module){
			if(!in_array($module, $mymodules)) {
				$data = array();
				$data['uniacid'] = $row['uniacid'];
				$data['module'] = $module;
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
function get_all_unistalled_module($status)  {
	load()->model('cloud');
	$status = $status == 'uninstalled' ?  'uninstalled' : 'recycle';
	$all_module = pdo_getall('modules');
	$installed_module = array();
	if (!empty($all_module)) {
		foreach ($all_module as $module) {
			$installed_module[] = $module['name'];
		}
	}
	$uninstallModules = array();
	$recycle_modules = pdo_getall('modules_recycle', array(), array(), 'modulename');
	$recycle_modules = array_keys($recycle_modules);

	//获取云上未安装模块
	$cloud_module = cloud_m_query();
	if (!empty($cloud_module) && !is_error($cloud_module)) {
		foreach ($cloud_module as $module) {
			if (($status == 'uninstalled' && in_array($module['name'], $recycle_modules)) || ($status == 'recycle' && !in_array($module['name'], $recycle_modules))) {
				continue;
			}
			if (!in_array($module['name'], $installed_module)) {
				$uninstallModules[$module['name']] = array(
					'from' => 'cloud',
					'name' => $module['name'],
					'version' => $module['version'],
					'title' => $module['title'],
					'thumb' => $module['thumb']
				);
			}
		}
	}

	//获取本地未安装模块
	$path = IA_ROOT . '/addons/';
	if (is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($modulepath = readdir($handle))) {
				if ($modulepath == '.' || $modulepath == '..') {
					continue;
				}
				$manifest = ext_module_manifest($modulepath);
				if (!is_array($manifest) || empty($manifest) || empty($manifest['application']['identifie'])) {
					continue;
				}
				if (!in_array($manifest['application']['identifie'], $installed_module)) {
					$manifest = ext_module_convert($manifest);
					$module[$manifest['name']] = $manifest;
					if (($status == 'uninstalled' && in_array($manifest['name'], $recycle_modules)) || ($status == 'recycle' && !in_array($manifest['name'], $recycle_modules))) {
						continue;
					}
					$uninstallModules[$manifest['name']] = array(
						'from' => 'local',
						'name' => $manifest['name'],
						'version' => $manifest['version'],
						'title' => $manifest['title'],
					);
				}
			}
		}
		return $uninstallModules;
	} else {
		return array();
	}
}

/*
 * 获取各模块权限菜单列表
 * @return array 
*/
function frame_lists(){
	$data = pdo_fetchall('SELECT * FROM ' . tablename('core_menu') . ' WHERE pid = 0 ORDER BY is_system ASC, displayorder ASC, id ASC');
	if(!empty($data)) {
		foreach($data as &$da) {
			$childs = pdo_fetchall('SELECT * FROM ' . tablename('core_menu') . ' WHERE pid = :pid ORDER BY is_system ASC, displayorder ASC, id ASC', array(':pid' => $da['id']));
			if(!empty($childs)) {
				foreach($childs as &$child) {
					$grandchilds = pdo_fetchall('SELECT * FROM ' . tablename('core_menu') . ' WHERE pid = :pid ORDER BY is_system ASC, displayorder ASC, id ASC', array(':pid' => $child['id']));
					if(!empty($grandchilds)) {
						foreach($grandchilds as &$grandchild) {
							$greatsons = pdo_fetchall('SELECT * FROM ' . tablename('core_menu') . ' WHERE pid = :pid ORDER BY is_system ASC, displayorder ASC, id ASC', array(':pid' => $grandchild['id']));
							$grandchild['greatsons'] = $greatsons;
						}
					}
					$child['grandchild'] = $grandchilds;
				}
				$da['child'] = $childs;
			}
		}
	}
	return $data;
}