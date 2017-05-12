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

function wxapp_account_create($uniacid, $account,$wxapp_type = 1) {
	$accountdata = array('uniacid' => $uniacid, 'type' => $account['type'], 'hash' => random(8));
	pdo_insert('account', $accountdata);
	$acid = pdo_insertid();
	$account['acid'] = $acid;
	$account['token'] = random(32);
	$account['encodingaeskey'] = random(43);
	$account['uniacid'] = $uniacid;
	$account['wxapp_type'] = $wxapp_type;
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
 * 获取小程序新版本号
	@return array
*/
function wxapp_version_parser($pos1_val,$pos2_val,$pos3_val) {
	$new_version = array(
		0 => $pos1_val,	
		1 => $pos2_val,
		2 => $pos3_val	
	);
	if ($pos3_val < 9) {
		$new_version[2] += 1;
	} else {
		if ($pos2_val < 9) {
			if ($pos1_val < 9) {
				$new_version[1] += 1;
				$new_version[2] = 0;
			} else {
				$new_version[0] += 1;
				$new_version[1] = 0;
				$new_version[2] = 0;
			}
		} else {
			$new_version[0] += 1;
			$new_version[1] = 0;
			$new_version[2] = 0;
		}
	}
	
	return $new_version;
}
/*
    * 获取小程序信息(包括版本信息)
    @params int $uniacid
    @params int $versionid 
 	@return array
*/
function wxapp_fetch($uniacid, $versionid = 0) {
	$wxapp_account = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	$wxapp_version = array();
	if ($versionid) {
		$version = pdo_get('wxapp_versions', array('uniacid' => $uniacid, 'id' => $versionid));
		$wxapp_version[0] = $version;
	} else {
		$wxapp_version = pdo_getall('wxapp_versions', array('uniacid' => $uniacid), array(), '', array("id DESC"), array());
	}
	$wxapp_info = array(
		"account_wxapp" => $wxapp_account,
		"versions" => $wxapp_version
	);
	return $wxapp_info;
}