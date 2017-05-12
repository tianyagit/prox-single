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
		$result = $api->post('wxapp', 'developer-download', array('wxapp' => $data), 'html');
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
 	* 获取小程序升级后的版本号
 	@return array  
 */
function wxapp_version_update($uniacid) {
	$wxapp_version_info = pdo_get('wxapp_versions',array('uniacid'=>$uniacid),array('version','uniacid','id','multiid'));
	$version_nums = array();
	if (!empty($wxapp_version_info)) {
		$version_nums = explode('.', $wxapp_version_info['version']);
		if ($version_nums[2] < 9) {
			$version_nums[2] += 1;
		} else {
			if ($version_nums[1] < 9) {
				if ($version_nums[0] < 9) {
					$version_nums[1] += 1;
					$version_nums[2] = 0;
				} else {
					$version_nums[0] += 1;
					$version_nums[1] = 0;
					$version_nums[2] = 0;
				}
			} else {
				$version_nums[0] += 1;
				$version_nums[1] = 0;
				$version_nums[2] = 0;
			}
		}
	}
	return $version_nums;
}
/*
    * 获取小程序信息
 	@return array
*/
function wxapp_info($uniacid) {
	$wxapp_info = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	return $wxapp_info;
}