<?php

/**
 * 更新模板缓存
 * @return boolean
 */
function cache_build_template() {
	load()->func('file');
	rmdirs(IA_ROOT . '/data/tpl', true);
}

/**
 * 更新设置项缓存
 * @return mixed
 */
function cache_build_setting() {
	$sql = "SELECT * FROM " . tablename('core_settings');
	$setting = pdo_fetchall($sql, array(), 'key');
	if (is_array($setting)) {
		foreach ($setting as $k => $v) {
			$setting[$v['key']] = iunserializer($v['value']);
		}
		cache_write("setting", $setting);
	}
}

/**
 * 重建公众号下可使用的模块
 * @param int $uniacid 要重建模块的公众号uniacid
 */
function cache_build_account_modules($uniacid = 0) {
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		$uniacid_arr = pdo_fetchall("SELECT uniacid FROM " . tablename('uni_account'));
		foreach($uniacid_arr as $account){
			cache_delete("unimodules:{$account['uniacid']}:1");
			cache_delete("unimodules:{$account['uniacid']}:");
			cache_delete("unimodulesappbinding:{$account['uniacid']}");
		}
	} else {
		cache_delete("unimodules:{$uniacid}:1");
		cache_delete("unimodules:{$uniacid}:");
		cache_delete("unimodulesappbinding:{$uniacid}");
	}

}
/*
 * 重建公众号缓存
 * @param int $uniacid 要重建缓存的公众号uniacid 
 */
function cache_build_account($uniacid = 0) {
	global $_W;
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		$uniacid_arr = pdo_fetchall("SELECT uniacid FROM " . tablename('uni_account'));
		foreach($uniacid_arr as $account){
			cache_delete("uniaccount:{$account['uniacid']}");
			cache_delete("unisetting:{$account['uniacid']}");
			cache_delete("defaultgroupid:{$account['uniacid']}");
		}
	} else {
		cache_delete("uniaccount:{$uniacid}");
		cache_delete("unisetting:{$uniacid}");
		cache_delete("defaultgroupid:{$uniacid}");
	}

}

function cache_build_accesstoken() {
	global $_W;
	$uniacid_arr = pdo_fetchall("SELECT acid FROM " . tablename('account_wechats'));
	foreach($uniacid_arr as $account){
		cache_delete("accesstoken:{$account['acid']}");
		cache_delete("jsticket:{$account['acid']}");
		cache_delete("cardticket:{$account['acid']}");
	}
}
/**
 * 更新会员个人信息字段
 * @return array
 */
function cache_build_users_struct() {
	$base_fields = array(
		'uniacid' => '同一公众号id',
		'groupid' => '分组id',
		'credit1' => '积分',
		'credit2' => '余额',
		'credit3' => '预留积分类型3',
		'credit4' => '预留积分类型4',
		'credit5' => '预留积分类型5',
		'credit6' => '预留积分类型6',
		'createtime' => '加入时间',
		'mobile' => '手机号码',
		'email' => '电子邮箱',
		'realname' => '真实姓名',
		'nickname' => '昵称',
		'avatar' => '头像',
		'qq' => 'QQ号',
		'gender' => '性别',
		'birth' => '生日',
		'constellation' => '星座',
		'zodiac' => '生肖',
		'telephone' => '固定电话',
		'idcard' => '证件号码',
		'studentid' => '学号',
		'grade' => '班级',
		'address' => '地址',
		'zipcode' => '邮编',
		'nationality' => '国籍',
		'reside' => '居住地',
		'graduateschool' => '毕业学校',
		'company' => '公司',
		'education' => '学历',
		'occupation' => '职业',
		'position' => '职位',
		'revenue' => '年收入',
		'affectivestatus' => '情感状态',
		'lookingfor' => ' 交友目的',
		'bloodtype' => '血型',
		'height' => '身高',
		'weight' => '体重',
		'alipay' => '支付宝帐号',
		'msn' => 'MSN',
		'taobao' => '阿里旺旺',
		'site' => '主页',
		'bio' => '自我介绍',
		'interest' => '兴趣爱好'
	);
	cache_write('userbasefields', $base_fields);
	$fields = pdo_getall('profile_fields', array(), array(), 'field');
	if (!empty($fields)) {
		foreach ($fields as &$field) {
			$field = $field['title'];
		}
		$fields['uniacid'] = '同一公众号id';
		$fields['groupid'] = '分组id';
		$fields['credit1'] ='积分';
		$fields['credit2'] = '余额';
		$fields['credit3'] = '预留积分类型3';
		$fields['credit4'] = '预留积分类型4';
		$fields['credit5'] = '预留积分类型5';
		$fields['credit6'] = '预留积分类型6';
		$fields['createtime'] = '加入时间';
		cache_write('usersfields', $fields);
	} else {
		cache_write('usersfields', $base_fields);
	}
}

function cache_build_frame_menu() {
	$system_menu_db = pdo_getall('core_menu', array('permission_name !=' => ''), array(), 'permission_name');
	
	$system_menu = require_once IA_ROOT . '/web/common/frames.inc.php';
	if (!empty($system_menu) && is_array($system_menu)) {
		foreach ($system_menu as $menu_name => $menu) {
			$system_menu[$menu_name]['is_system'] = true;
			$system_menu[$menu_name]['is_display'] = true;
			foreach ($menu['section'] as $section_name => $section) {
				$displayorder = max(count($section['menu']), 1);
				
				//查询此节点下新增的菜单
				if (empty($section['menu'])) {
					$section['menu'] = array();
				}
				$add_menu = pdo_getall('core_menu', array('group_name' => $section_name), array(
					'id', 'title', 'url', 'is_display', 'is_system', 'permission_name', 'displayorder', 'icon',
				), 'permission_name', 'displayorder DESC');
				if (!empty($add_menu)) {
					foreach ($add_menu as $permission_name => $menu) {
						$menu['icon'] = !empty($menu['icon']) ? $menu['icon'] : 'wi wi-appsetting';
						$section['menu'][$permission_name] = $menu;
					}
				}
				foreach ($section['menu']  as $permission_name => $sub_menu) {
					$sub_menu_db = $system_menu_db[$sub_menu['permission_name']];
					$system_menu[$menu_name]['section'][$section_name]['menu'][$permission_name] = array(
						'is_system' => isset($sub_menu['is_system']) ? $sub_menu['is_system'] : 1,
						'is_display' => isset($sub_menu_db['is_display']) ? $sub_menu_db['is_display'] : 1,
						'title' => !empty($sub_menu_db['title']) ? $sub_menu_db['title'] : $sub_menu['title'],
						'url' => $sub_menu['url'],
						'permission_name' => $sub_menu['permission_name'],
						'icon' => $sub_menu['icon'],
						'displayorder' => !empty($sub_menu_db['displayorder']) ? $sub_menu_db['displayorder'] : $displayorder,
						'id' => $sub_menu['id'],
						'sub_permission' => $sub_menu['sub_permission'],
					);
					$displayorder--;
					$displayorder = max($displayorder, 0);
				}
				$system_menu[$menu_name]['section'][$section_name]['menu'] = array_sort($system_menu[$menu_name]['section'][$section_name]['menu'], 'displayorder', 'desc');
			}
		}
		$add_top_nav = pdo_getall('core_menu', array('group_name' => 'frame'), array('title', 'url', 'permission_name'));
		if (!empty($add_top_nav)) {
			foreach ($add_top_nav as $menu) {
				$menu['blank'] = true;
				$menu['is_display'] = true;
				$system_menu[$menu['permission_name']] = $menu;
			}
		}
		cache_delete('system_frame');
		cache_write('system_frame', $system_menu);
	}
}

function cache_build_module_subscribe_type() {
	global $_W;
	$modules = pdo_fetchall("SELECT name, subscribes FROM " . tablename('modules') . " WHERE subscribes <> ''");
	$subscribe = array();
	if (!empty($modules)) {
		foreach ($modules as $module) {
			$module['subscribes'] = unserialize($module['subscribes']);
			if (!empty($module['subscribes'])) {
				foreach ($module['subscribes'] as $event) {
					if ($event == 'text') {
						continue;
					}
					$subscribe[$event][] = $module['name'];
				}
			}
		}
	}
	$module_ban = $_W['setting']['module_receive_ban'];
	foreach ($subscribe as $event => $module_group) {
		if (!empty($module_group)) {
			foreach ($module_group as $index => $module) {
				if (!empty($module_ban[$module])) {
					unset($subscribe[$event][$index]);
				}
			}
		}
	}
	cache_write('module_receive_enable', $subscribe);
	return $subscribe;
}

function cache_build_platform() {
	return pdo_query("DELETE FROM " . tablename('core_cache') . " WHERE `key` LIKE 'account%' AND `key` <> 'account:ticket';");
}

/*更新公众号关注人数*/
function cache_build_stat_fans() {
	global $_W;
	$uniacid_arr = pdo_fetchall("SELECT uniacid FROM " . tablename('uni_account'));
	foreach($uniacid_arr as $account){
		cache_delete("stat:todaylock:{$account['uniacid']}");
	}
}
/*更新流量主缓存*/
function cache_build_cloud_ad() {
	global $_W;
	$uniacid_arr = pdo_fetchall("SELECT uniacid FROM " . tablename('uni_account'));
	foreach($uniacid_arr as $account){
		cache_delete("stat:todaylock:{$account['uniacid']}");
		cache_delete("cloud:ad:uniaccount:{$account['uniacid']}");
		cache_delete("cloud:ad:app:list:{$account['uniacid']}");
	}
	cache_delete("cloud:flow:master");
	cache_delete("cloud:ad:uniaccount:list");
	cache_delete("cloud:ad:tags");
	cache_delete("cloud:ad:type:list");
	cache_delete("cloud:ad:app:support:list");
	cache_delete("cloud:ad:site:finance");
}

/**
 * 更新未安装模块列表
 */
function cache_build_uninstalled_module() {
	load()->model('cloud');
	load()->classs('cloudapi');
	$cloud_api = new CloudApi();
	$cloud_m_count = $cloud_api->get('site', 'stat', array('module_quantity' => 1), 'json');
	$all_module = pdo_getall('modules');
	$installed_module = array();
	if (!empty($all_module)) {
		foreach ($all_module as $module) {
			$installed_module[] = $module['name'];
		}
	}
	$uninstallModules = array('recycle' => array(), 'uninstalled' => array());
	$recycle_modules = pdo_getall('modules_recycle', array(), array(), 'modulename');
	$recycle_modules = array_keys($recycle_modules);
	$cloud_module = cloud_m_query();
	unset($cloud_module['pirate_apps']);
	if (!empty($cloud_module) && !is_error($cloud_module)) {
		foreach ($cloud_module as $module) {
			if (!in_array($module['name'], $installed_module)) {
				$status = in_array($module['name'], $recycle_modules) ? 'recycle' : 'uninstalled';
				if (!empty($module['id'])) {
					$uninstallModules[$status][$module['name']] = array(
						'from' => 'cloud',
						'name' => $module['name'],
						'version' => $module['version'],
						'title' => $module['title'],
						'thumb' => $module['thumb'],
						'wxapp_support' => !empty($module['site_branch']['wxapp_support']) ? $module['site_branch']['wxapp_support'] : 1,
						'app_support' => !empty($module['site_branch']['app_support']) ? $module['site_branch']['app_support'] : 2
					);
				}
			}
		}
	}
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
					$app_support = !empty($manifest['support']['app_support']) ? $manifest['support']['app_support'] : 2;
					$wxapp_support = !empty($manifest['support']['wxapp_support']) ? $manifest['support']['wxapp_support'] : 1;
					$module_info = array(
						'from' => 'local',
						'name' => $manifest['name'],
						'version' => $manifest['version'],
						'title' => $manifest['title'],
						'app_support' => $app_support,
						'wxapp_support' => $wxapp_support
					);
					if (in_array($manifest['name'], $recycle_modules)) {
						$uninstallModules['recycle'][$manifest['name']] = $module_info;
					}
					if (!in_array($manifest['name'], $recycle_modules)) {
						$uninstallModules['uninstalled'][$manifest['name']] = $module_info;
					}
				}
			}
		}
		$cache = array(
			'cloud_m_count' => $cloud_m_count,
			'modules' => $uninstallModules,
			'uninstall_count' => count($uninstallModules['uninstalled'])
		);
		cache_write('we7:module:all_uninstall', $cache);
		return $cache;
	} else {
		return array();
	}
}

