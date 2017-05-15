<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function wxapp_getpackage($data, $if_single = false) {
	if (empty($if_single)) {
		$request_cloud_data = json_encode($data);
	} else {
		$request_cloud_data = $data;
	}
	load()->classs('cloudapi');
	$api = new CloudApi();
	if (empty($if_single)) {
		$result = $api->post('wxapp', 'download', $request_cloud_data, 'html');
	} else {
		$result = $api->post('wxapp', 'developer-download', array('wxapp' => $request_cloud_data), 'html');
	}
	if (is_error($result)) {
			return error(-1, $result['message']);
	} else {
		if (strpos($result, 'error:') === 0 ) {
			return error(-1, substr($result, 6));
		}
	}
	return $result;
}

function wxapp_account_create($uniacid, $account) {
	$accountdata = array('uniacid' => $uniacid, 'type' => $account['type'], 'hash' => random(8));
	pdo_insert('account', $accountdata);
	$acid = pdo_insertid();
	$account['acid'] = $acid;
	$account['token'] = random(32);
	$account['encodingaeskey'] = random(43);
	$account['uniacid'] = $uniacid;
	unset($account['type']);
	pdo_insert('account_wxapp', $account);
	return $acid;
}
/**
 * 获取某一小程序拥有的小程序模块
 * @param int $uniacid
 * @return array
 */
function wxapp_owned_moudles($uniacid) {
	load()->model('module');

	$wxapp_modules = array();

	$modules = uni_modules();
	if (!empty($modules)) {
		foreach ($modules as $module) {
			if ($module['wxapp_support'] == 2) {
				$wxapp_modules[] = $module;
			}
		}
	}
	return $wxapp_modules;
}
/*
 * 小程序版本号构造函数
.* @return array
*/
function wxapp_version_parser($first_value, $second_value, $third_value) {
	$version = array(
		0 => intval($first_value),	
		1 => intval($second_value),
		2 => intval($third_value)	
	);	
	if($version[2] >= 10){
		$version[1] += 1;
		$version[2] = 0;
	}
	if($version[1] >= 10) {
		$version[0] += 1;
		$version[1] = 0;
	} 
	return $version;
}
/*
 * 获取小程序信息(包括最新版本信息)
 * @params int $uniacid
 * @params int $versionid 
 * @return array
*/
function wxapp_fetch($uniacid) {
	$wxapp_info = array();
	if (empty($uniacid)) {
		return $wxapp_info;
	}
	
	$account_wxapp = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	if (empty($account_wxapp)) {
		return $wxapp_info;
	}
	$wxapp_info['account_wxapp'] = $account_wxapp;
	
	$sql ="SELECT * FROM " . tablename('wxapp_versions') . " WHERE `uniacid`=:uniacid ORDER BY `id` DESC";
	$wxapp_version_info = pdo_fetch($sql, array(':uniacid' => $uniacid));
	if (!empty($wxapp_version_info)) {
		$wxapp_info['last_version'] = $wxapp_version_info;
		$wxapp_info['last_version_num'] = explode('.', $wxapp_version_info['version']);
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