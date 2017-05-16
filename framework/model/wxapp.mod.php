<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function wxapp_getpackage($data, $if_single = false) {
	load()->classs('cloudapi');

	$api = new CloudApi();
	$result = $api->post('wxapp', 'download', $data, 'html');

	if (is_error($result)) {
			return error(-1, $result['message']);
	} else {
		if (strpos($result, 'error:') === 0 ) {
			return error(-1, substr($result, 6));
		}
	}
	return $result;
}

function wxapp_account_create($account) {
	$uni_account_data = array(
		'name' => $account['name'],
		'description' => '',
		'groupid' => 0,
	);
	if (!pdo_insert('uni_account', $uni_account_data)) {
		return error(1, '添加公众号失败');
	}
	$uniacid = pdo_insertid();
	
	$account_data = array(
		'uniacid' => $uniacid, 
		'type' => $account['type'], 
		'hash' => random(8)
	);
	pdo_insert('account', $account_data);
	
	$acid = pdo_insertid();
	
	$wxapp_data = array(
		'acid' => $acid,
		'token' => random(32),
		'encodingaeskey' => random(43),
		'uniacid' => $uniacid,
		'name' => $account['name'],
		'account' => $account['account'],
		'original' => $account['original'],
		'level' => $account['level'],
		'key' => $account['key'],
		'secret' => $account['secret'],
	);
	pdo_insert('account_wxapp', $wxapp_data);
	
	if (empty($_W['isfounder'])) {
		pdo_insert('uni_account_users', array('uniacid' => $uniacid, 'uid' => $_W['uid'], 'role' => 'owner'));
	}
	pdo_update('uni_account', array('default_acid' => $acid), array('uniacid' => $uniacid));
	
	return $uniacid;
}
/**
 * 获取某一小程序拥有的小程序模块
 * @param int $uniacid
 * @return array
 */
function wxapp_owned_moudles() {
	load()->model('module');

	$wxapp_modules = array();

	$modules = uni_modules_by_uniacid($uniacid);
	if (!empty($modules)) {
		foreach ($modules as $module) {
			if ($module['wxapp_support'] == 2) {
				$wxapp_modules[] = $module;
			}
		}
	}
	return $wxapp_modules;
}

/**
 * 获取所有支持小程序的模块
 */
function wxapp_supoort_wxapp_modules() {
	global $_W;
	load()->model('user');
	
	$modules = user_modules($_W['uid']);
	if (!empty($modules)) {
		foreach ($modules as $module) {
			if ($module['wxapp_support'] == MODULE_SUPPORT_WXAPP) {
				$wxapp_modules[$module['name']] = $module;
			}
		}
	}
	if (empty($wxapp_modules)) {
		return array();
	}
	$bindings = pdo_getall('modules_bindings', array('module' => array_keys($wxapp_modules), 'entry' => 'page'));
	if (!empty($bindings)) {
		foreach ($bindings as $bind) {
			$wxapp_modules[$bind['module']]['bindings'][] = array('title' => $bind['title'], 'do' => $bind['do']);
		}
	}
	return $wxapp_modules;
}

/*
 * 获取小程序信息(包括最新版本信息)
 * @params int $uniacid
 * @params int $versionid 不包含版本ID，默认获取最新版
 * @return array
*/
function wxapp_fetch($uniacid, $version_id = '') {
	$wxapp_info = array();
	if (empty($uniacid)) {
		return $wxapp_info;
	}
	
	$account_wxapp = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	if (empty($account_wxapp)) {
		return $wxapp_info;
	}
	$wxapp_info['account_wxapp'] = $account_wxapp;
	
	if (empty($version_id)) {
		$sql ="SELECT * FROM " . tablename('wxapp_versions') . " WHERE `uniacid`=:uniacid ORDER BY `id` DESC";
		$wxapp_version_info = pdo_fetch($sql, array(':uniacid' => $uniacid));
	} else {
		$wxapp_version_info = pdo_get('wxapp_versions', array('id' => $version_id));
	}
	if (!empty($wxapp_version_info)) {
		$wxapp_info['version'] = $wxapp_version_info;
		$wxapp_info['version_num'] = explode('.', $wxapp_version_info['version']);
	}
	return  $wxapp_info;
}
/*  
 * 获取小程序所有版本
 * @params int $uniacid
 * @return array
*/
function wxapp_version_all($uniacid) {
	$wxapp_versions = array();
	if (empty($uniacid)) {
		return $wxapp_versions;
	}
	
	$wxapp_versions = pdo_getall('wxapp_versions', array('uniacid' => $uniacid), array(), '', array("id DESC"), array());
	return $wxapp_versions;
}

/**
 * 获取小程序单个版本
 * @param unknown $version_id
 */
function wxapp_version($version_id) {
	$version_id = intval($version_id);
	if (empty($version_id)) {
		return array();
	}
	$version_info = pdo_get('wxapp_versions', array('id' => $version_id));
	print_r($version_info);exit;
	$modules_info = json_decode($version_info['modules'], true);
}

/**
 * 判断小程序是单版还是多版
 * @param int id 小程序版本ID（wxapp_versions表ID）
 * @return int
 */
function wxapp_type($id) {
	$id = intval($id);
	if (empty($id)) {
		itoast('参数错误，请联系管理员！', '', 'error');
	}
	$version_info = pdo_get('wxapp_versions', array('id' => $id));
	if (!empty($version_info)) {
		if ($version_info['design_method'] != 3) {
			$result = WXAPP_MULTI;
		} else {
			if (!empty($version_info['multiid']) || !empty($version_info['version']) || !empty($version_info['template']) || !empty($version_info['redirect']) || !empty($version_info['quickmenu'])) {
				$result = WXAPP_MULTI;
			} else {
				$result = WXAPP_SINGLE;
			}
		}
	} else {
		itoast('此小程序不存在', '', 'error');
	}
	return $result;
}