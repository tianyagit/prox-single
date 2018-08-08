<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

function miniapp_getpackage($data, $if_single = false) {
	load()->classs('cloudapi');

	$api = new CloudApi();
	$result = $api->post('miniapp', 'download', $data, 'html');
	if (is_error($result)) {
		return error(-1, $result['message']);
	} else {
		if (strpos($result, 'error:') === 0) {
			return error(-1, substr($result, 6));
		}
	}

	return $result;
}

function miniapp_create($account) {
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
function miniapp_support_uniacid_modules($uniacid) {
	$uni_modules = uni_modules_by_uniacid($uniacid);
	$miniapp_modules = array();
	if (!empty($uni_modules)) {
		foreach ($uni_modules as $module_name => $module_info) {
			if ($module_info[MODULE_SUPPORT_ALIAPP_NAME] == MODULE_SUPPORT_ALIAPP) {
				$miniapp_modules[$module_name] = $module_info;
			}
		}
	}

	return $miniapp_modules;
}

/*
 * 获取小程序信息(包括上一次使用版本的版本信息，若从未使用过任何版本则取最新版本信息)
 * @params int $uniacid
 * @params int $versionid 不包含版本ID，默认获取上一次使用的版本，若从未使用过则取最新版本信息
 * @return array
*/
function miniapp_fetch($uniacid, $version_id = '') {
	global $_GPC;
	load()->model('extension');
	$miniapp_info = array();
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		return $miniapp_info;
	}
	if (!empty($version_id)) {
		$version_id = intval($version_id);
	}

	$miniapp_info = pdo_get('account_aliapp', array('uniacid' => $uniacid));
	if (empty($miniapp_info)) {
		return $miniapp_info;
	}

	if (empty($version_id)) {
		$miniapp_cookie_uniacids = array();
		if (!empty($_GPC['__miniappversionids' . $uniacid])) {
			$miniappversionids = json_decode(htmlspecialchars_decode($_GPC['__miniappversionids' . $uniacid]), true);
			foreach ($miniappversionids as $version_val) {
				$miniapp_cookie_uniacids[] = $version_val['uniacid'];
			}
		}
		if (in_array($uniacid, $miniapp_cookie_uniacids)) {
			$miniapp_version_info = miniapp_version($miniappversionids[$uniacid]['version_id']);
		}

		if (empty($miniapp_version_info)) {
			$sql = 'SELECT * FROM ' . tablename('wxapp_versions') . ' WHERE `uniacid`=:uniacid ORDER BY `id` DESC';
			$miniapp_version_info = pdo_fetch($sql, array(':uniacid' => $uniacid));
		}
	} else {
		$miniapp_version_info = pdo_get('wxapp_versions', array('id' => $version_id));
	}
	if (!empty($miniapp_version_info) && !empty($miniapp_version_info['modules'])) {

		$miniapp_version_info['modules'] = iunserializer($miniapp_version_info['modules']);
		//如果是单模块版并且本地模块，应该是开发者开发小程序，则模块版本号本地最新的。
		if ($miniapp_version_info['design_method'] == WXAPP_MODULE) {
			$module = current($miniapp_version_info['modules']);
			$manifest = ext_module_manifest($module['name']);
			if (!empty($manifest)) {
				$miniapp_version_info['modules'][$module['name']]['version'] = $manifest['application']['version'];
			} else {
				$last_install_module = module_fetch($module['name']);
				$miniapp_version_info['modules'][$module['name']]['version'] = $last_install_module['version'];
			}
		}
	}
	$miniapp_info['version'] = $miniapp_version_info;
	$miniapp_info['version_num'] = explode('.', $miniapp_version_info['version']);

	return  $miniapp_info;
}
/*
 * 获取小程序所有版本
 * @params int $uniacid
 * @return array
*/
function miniapp_version_all($uniacid) {
	load()->model('module');
	$miniapp_versions = array();
	$uniacid = intval($uniacid);

	if (empty($uniacid)) {
		return $miniapp_versions;
	}

	$miniapp_versions = pdo_getall('wxapp_versions', array('uniacid' => $uniacid), array('id'), '', array('id DESC'));
	if (!empty($miniapp_versions)) {
		foreach ($miniapp_versions as &$version) {
			$version = miniapp_version($version['id']);
		}
	}

	return $miniapp_versions;
}

/**
 * 获取某一小程序最新四个版本信息，并标记出来最后使用的版本.
 *
 * @param int $uniacid
 * @param int $page
 * @param int $pagesize
 * @return array
 */
function miniapp_get_some_lastversions($uniacid) {
	$version_lasts = array();
	$uniacid = intval($uniacid);

	if (empty($uniacid)) {
		return $version_lasts;
	}
	$version_lasts = table('wxapp_versions')->latestVersion($uniacid);
	$last_switch_version = miniapp_last_switch_version($uniacid);
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
function miniapp_last_switch_version($uniacid) {
	global $_GPC;
	static $miniapp_cookie_uniacids;
	if (empty($miniapp_cookie_uniacids) && !empty($_GPC['__miniappversionids' . $uniacid])) {
		$miniapp_cookie_uniacids = json_decode(htmlspecialchars_decode($_GPC['__miniappversionids' . $uniacid]), true);
	}

	return $miniapp_cookie_uniacids;
}

/**
 * 更新最新使用版本.
 *
 * @param int $version_id
 *						return boolean
 */
function miniapp_update_last_use_version($uniacid, $version_id) {
	global $_GPC;
	$uniacid = intval($uniacid);
	$version_id = intval($version_id);
	if (empty($uniacid) || empty($version_id)) {
		return false;
	}
	$cookie_val = array();
	if (!empty($_GPC['__miniappversionids' . $uniacid])) {
		$miniapp_uniacids = array();
		$cookie_val = json_decode(htmlspecialchars_decode($_GPC['__miniappversionids' . $uniacid]), true);
		if (!empty($cookie_val)) {
			foreach ($cookie_val as &$version) {
				$miniapp_uniacids[] = $version['uniacid'];
				if ($version['uniacid'] == $uniacid) {
					$version['version_id'] = $version_id;
					$miniapp_uniacids = array();
					break;
				}
			}
			unset($version);
		}
		if (!empty($miniapp_uniacids) && !in_array($uniacid, $miniapp_uniacids)) {
			$cookie_val[$uniacid] = array('uniacid' => $uniacid, 'version_id' => $version_id);
		}
	} else {
		$cookie_val = array(
			$uniacid => array('uniacid' => $uniacid, 'version_id' => $version_id),
		);
	}
	isetcookie('__uniacid', $uniacid, 7 * 86400);
	isetcookie('__miniappversionids' . $uniacid, json_encode($cookie_val), 7 * 86400);

	return true;
}

/**
 * 获取小程序单个版本.
 *
 * @param int $version_id
 */
function miniapp_version($version_id) {
	$version_info = array();
	$version_id = intval($version_id);

	if (empty($version_id)) {
		return $version_info;
	}

	$cachekey = cache_system_key('miniapp_version', array('version_id' => $version_id));
	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		return $cache;
	}

	$version_info = pdo_get('wxapp_versions', array('id' => $version_id));
	$version_info = miniapp_version_detail_info($version_info);
	cache_write($cachekey, $version_info);

	return $version_info;
}

function miniapp_version_detail_info($version_info) {
	global $_W;
	$result = array();
	if (empty($version_info) || empty($version_info['uniacid'])) {
		return $result;
	}
	$uni_modules = uni_modules_by_uniacid($version_info['uniacid']);
	$uni_modules = array_keys($uni_modules);
	$version_info['modules'] = iunserializer($version_info['modules']);
	if (!empty($version_info['modules'])) {
		foreach ($version_info['modules'] as $i => $module) {
			if (!in_array($module['name'], $uni_modules)) {
				continue;
			}
			$version_info['modules'][$i]['module_info'] = module_fetch($module['name']);
			$version_info['modules'][$i]['logo'] = $version_info['modules'][$i]['module_info']['logo'];
			$version_info['modules'][$i]['title'] = $version_info['modules'][$i]['module_info']['title'];
		}
	}
	$result = $version_info;

	return $version_info;
}

