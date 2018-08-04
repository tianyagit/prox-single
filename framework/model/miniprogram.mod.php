<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

function miniprogram_getpackage($data, $if_single = false) {
	load()->classs('cloudapi');

	$api = new CloudApi();
	$result = $api->post('miniprogram', 'download', $data, 'html');
	if (is_error($result)) {
		return error(-1, $result['message']);
	} else {
		if (strpos($result, 'error:') === 0) {
			return error(-1, substr($result, 6));
		}
	}

	return $result;
}

function miniprogram_create($account) {
	global $_W;
	load()->model('account');
	load()->model('user');
	load()->model('permission');
	$uni_account_data = array(
		'name' => $account['name'],
		'description' => $account['description'],
		'title_initial' => get_first_pinyin($account['name']),
		'groupid' => 0,
	);
	if (!pdo_insert('uni_account', $uni_account_data)) {
		return error(1, '添加失败');
	}
	$uniacid = pdo_insertid();
	$account_data = array(
		'uniacid' => $uniacid,
		'type' => $account['type'],
		'hash' => random(8),
	);
	pdo_insert('account', $account_data);
	$acid = pdo_insertid();

	load()->model('utility');
	if (!empty($account['headimg'])) {
		utility_image_rename($account['headimg'], ATTACHMENT_ROOT . 'headimg_' . $acid . '.jpg');
	}
	if (!empty($account['qrcode'])) {
		utility_image_rename($account['qrcode'], ATTACHMENT_ROOT . 'qrcode_' . $acid . '.jpg');
	}

	$data = array(
		'acid' => $acid,
		'uniacid' => $uniacid,
		'name' => $account['name'],
		'description' => $account['description'],
		'level' => $account['level'],
		'key' => $account['appid'],
	);
	pdo_insert('account_aliapp', $data);

	if (empty($_W['isfounder'])) {
		uni_user_account_role($uniacid, $_W['uid'], ACCOUNT_MANAGE_NAME_OWNER);
	}
	if (user_is_vice_founder()) {
		uni_user_account_role($uniacid, $_W['uid'], ACCOUNT_MANAGE_NAME_VICE_FOUNDER);
	}
	if (!empty($_W['user']['owner_uid'])) {
		uni_user_account_role($uniacid, $_W['user']['owner_uid'], ACCOUNT_MANAGE_NAME_VICE_FOUNDER);
	}
	pdo_update('uni_account', array('default_acid' => $acid), array('uniacid' => $uniacid));

	return $uniacid;
}

/**
 * 获取当前公众号支持小程序的模块.
 *
 * @return array
 */
function miniprogram_support_uniacid_modules($uniacid) {
	$uni_modules = uni_modules_by_uniacid($uniacid);
	$miniprogram_modules = array();
	if (!empty($uni_modules)) {
		foreach ($uni_modules as $module_name => $module_info) {
			if ($module_info[MODULE_SUPPORT_ALIAPP_NAME] == MODULE_SUPPORT_ALIAPP) {
				$miniprogram_modules[$module_name] = $module_info;
			}
		}
	}

	return $miniprogram_modules;
}

/*
 * 获取小程序信息(包括上一次使用版本的版本信息，若从未使用过任何版本则取最新版本信息)
 * @params int $uniacid
 * @params int $versionid 不包含版本ID，默认获取上一次使用的版本，若从未使用过则取最新版本信息
 * @return array
*/
function miniprogram_fetch($uniacid, $version_id = '') {
	global $_GPC;
	load()->model('extension');
	$miniprogram_info = array();
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		return $miniprogram_info;
	}
	if (!empty($version_id)) {
		$version_id = intval($version_id);
	}

	$miniprogram_info = pdo_get('account_aliapp', array('uniacid' => $uniacid));
	if (empty($miniprogram_info)) {
		return $miniprogram_info;
	}

	if (empty($version_id)) {
		$miniprogram_cookie_uniacids = array();
		if (!empty($_GPC['__miniprogramversionids' . $uniacid])) {
			$miniprogramversionids = json_decode(htmlspecialchars_decode($_GPC['__miniprogramversionids' . $uniacid]), true);
			foreach ($miniprogramversionids as $version_val) {
				$miniprogram_cookie_uniacids[] = $version_val['uniacid'];
			}
		}
		if (in_array($uniacid, $miniprogram_cookie_uniacids)) {
			$miniprogram_version_info = miniprogram_version($miniprogramversionids[$uniacid]['version_id']);
		}

		if (empty($miniprogram_version_info)) {
			$sql = 'SELECT * FROM ' . tablename('wxapp_versions') . ' WHERE `uniacid`=:uniacid ORDER BY `id` DESC';
			$miniprogram_version_info = pdo_fetch($sql, array(':uniacid' => $uniacid));
		}
	} else {
		$miniprogram_version_info = pdo_get('wxapp_versions', array('id' => $version_id));
	}
	if (!empty($miniprogram_version_info) && !empty($miniprogram_version_info['modules'])) {

		$miniprogram_version_info['modules'] = iunserializer($miniprogram_version_info['modules']);
		//如果是单模块版并且本地模块，应该是开发者开发小程序，则模块版本号本地最新的。
		if ($miniprogram_version_info['design_method'] == miniprogram_MODULE) {
			$module = current($miniprogram_version_info['modules']);
			$manifest = ext_module_manifest($module['name']);
			if (!empty($manifest)) {
				$miniprogram_version_info['modules'][$module['name']]['version'] = $manifest['application']['version'];
			} else {
				$last_install_module = module_fetch($module['name']);
				$miniprogram_version_info['modules'][$module['name']]['version'] = $last_install_module['version'];
			}
		}
	}
	$miniprogram_info['version'] = $miniprogram_version_info;
	$miniprogram_info['version_num'] = explode('.', $miniprogram_version_info['version']);

	return  $miniprogram_info;
}
/*
 * 获取小程序所有版本
 * @params int $uniacid
 * @return array
*/
function miniprogram_version_all($uniacid) {
	load()->model('module');
	$miniprogram_versions = array();
	$uniacid = intval($uniacid);

	if (empty($uniacid)) {
		return $miniprogram_versions;
	}

	$miniprogram_versions = pdo_getall('wxapp_versions', array('uniacid' => $uniacid), array('id'), '', array('id DESC'));
	if (!empty($miniprogram_versions)) {
		foreach ($miniprogram_versions as &$version) {
			$version = miniprogram_version($version['id']);
		}
	}

	return $miniprogram_versions;
}

/**
 * 获取某一小程序最新四个版本信息，并标记出来最后使用的版本.
 *
 * @param int $uniacid
 * @param int $page
 * @param int $pagesize
 * @return array
 */
function miniprogram_get_some_lastversions($uniacid) {
	$version_lasts = array();
	$uniacid = intval($uniacid);

	if (empty($uniacid)) {
		return $version_lasts;
	}
	$version_lasts = table('wxapp')->latestVersion($uniacid);
	$last_switch_version = miniprogram_last_switch_version($uniacid);
	if (!empty($last_switch_version[$uniacid]) && !empty($version_lasts[$last_switch_version[$uniacid]['version_id']])) {
		$version_lasts[$last_switch_version[$uniacid]['version_id']]['current'] = true;
	} else {
		reset($version_lasts);
		$firstkey = key($version_lasts);
		$version_lasts[$firstkey]['current'] = true;
	}

	return $version_lasts;
}

/**
 * 获取当前用户使用每个小程序的最后版本.
 */
function miniprogram_last_switch_version($uniacid) {
	global $_GPC;
	static $miniprogram_cookie_uniacids;
	if (empty($miniprogram_cookie_uniacids) && !empty($_GPC['__miniprogramversionids' . $uniacid])) {
		$miniprogram_cookie_uniacids = json_decode(htmlspecialchars_decode($_GPC['__miniprogramversionids' . $uniacid]), true);
	}

	return $miniprogram_cookie_uniacids;
}

/**
 * 更新最新使用版本.
 *
 * @param int $version_id
 *						return boolean
 */
function miniprogram_update_last_use_version($uniacid, $version_id) {
	global $_GPC;
	$uniacid = intval($uniacid);
	$version_id = intval($version_id);
	if (empty($uniacid) || empty($version_id)) {
		return false;
	}
	$cookie_val = array();
	if (!empty($_GPC['__miniprogramversionids' . $uniacid])) {
		$miniprogram_uniacids = array();
		$cookie_val = json_decode(htmlspecialchars_decode($_GPC['__miniprogramversionids' . $uniacid]), true);
		if (!empty($cookie_val)) {
			foreach ($cookie_val as &$version) {
				$miniprogram_uniacids[] = $version['uniacid'];
				if ($version['uniacid'] == $uniacid) {
					$version['version_id'] = $version_id;
					$miniprogram_uniacids = array();
					break;
				}
			}
			unset($version);
		}
		if (!empty($miniprogram_uniacids) && !in_array($uniacid, $miniprogram_uniacids)) {
			$cookie_val[$uniacid] = array('uniacid' => $uniacid, 'version_id' => $version_id);
		}
	} else {
		$cookie_val = array(
			$uniacid => array('uniacid' => $uniacid, 'version_id' => $version_id),
		);
	}
	isetcookie('__uniacid', $uniacid, 7 * 86400);
	isetcookie('__miniprogramversionids' . $uniacid, json_encode($cookie_val), 7 * 86400);

	return true;
}

/**
 * 获取小程序单个版本.
 *
 * @param int $version_id
 */
function miniprogram_version($version_id) {
	$version_info = array();
	$version_id = intval($version_id);

	if (empty($version_id)) {
		return $version_info;
	}

	$cachekey = cache_system_key('miniprogram_version', array('version_id' => $version_id));
// 	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		return $cache;
	}

	$version_info = pdo_get('wxapp_versions', array('id' => $version_id));
	$version_info = miniprogram_version_detail_info($version_info);
	cache_write($cachekey, $version_info);

	return $version_info;
}

function miniprogram_version_detail_info($version_info) {
	global $_W;
	$result = array();
	if (empty($version_info)) {
		return $result;
	}
	$version_info['modules'] = iunserializer($version_info['modules']);
	if (!empty($version_info['modules'])) {
		foreach ($version_info['modules'] as $i => $module) {
			$version_info['modules'][$i]['module_info'] = module_fetch($module['name']);
		}
	}
	$result = $version_info;

	return $version_info;
}

