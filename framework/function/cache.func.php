<?php
/**
 * 缓存统一接口
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('cache.' . cache_type());

/**
 * 获取缓存类型
 * @param $cache_type
 */
function cache_type() {
	global $_W;
	$cacher = $connect = '';
	$cache_type = strtolower($_W['config']['setting']['cache']);
	
	if (extension_loaded($cache_type)) {
		$config = $_W['config']['setting'][$cache_type];
		if (!empty($config['server']) && !empty($config['port'])) {
			if ($cache_type == 'memcache') {
				$cacher = new Memcache();
			} elseif ($cache_type == 'redis') {
				$cacher = new Redis();
			}
			$connect = $cacher->connect($config['server'], $config['port']);
		}
	}
	if (empty($cacher) || empty($connect)) {
		$cache_type = 'mysql';
	}
	return $cache_type;
}

/**
 * 读取缓存，并将缓存加载至 $_W 全局变量中
 * @param string $key 缓存键名
 * @param boolean $unserialize 是否反序列化
 * @return array
 */
function cache_load($key, $unserialize = false) {
	global $_W;
	static $we7_cache;
	if (!empty($we7_cache[$key])) {
		return $we7_cache[$key];
	}
	$data = $we7_cache[$key] = cache_read($key);
	if ($key == 'setting') {
		$_W['setting'] = $data;
		return $_W['setting'];
	} elseif ($key == 'modules') {
		$_W['modules'] = $data;
		return $_W['modules'];
	} elseif ($key == 'module_receive_enable' && empty($data)) {
		//如果不存在订阅模块数据，就再获取一下缓存
		cache_build_module_subscribe_type();
		return cache_read($key);
	} else {
		return $unserialize ? iunserializer($data) : $data;
	}
}

/**
 * 统一系统缓存名称前缀，支持缓存名称中包含占位符，最多不得超过5个
 * @param string $cache_key
 * @return string
 */
function cache_system_key($cache_key) {
	$args = func_get_args();
	switch (func_num_args()) {
		case 1:
			break;
		case 2:
			$cache_key = sprintf($cache_key, $args[1]);
			break;
		case 3:
			$cache_key = sprintf($cache_key, $args[1], $args[2]);
			break;
		case 4:
			$cache_key = sprintf($cache_key, $args[1], $args[2], $args[3]);
			break;
		case 5:
			$cache_key = sprintf($cache_key, $args[1], $args[2], $args[3], $args[4]);
			break;
		case 6:
			$cache_key = sprintf($cache_key, $args[1], $args[2], $args[3], $args[4], $args[5]);
			break;
	}
	$cache_key = 'we7:' . $cache_key;
	if (strlen($cache_key) > CACHE_KEY_LENGTH) {
		trigger_error('Cache name is over the maximum length');
	}
	return $cache_key;
}

function &cache_global($key) {
	
}

/**
 * 根据参数构造缓存键
 * @param $cachekey
 * @param $params
 * @return mixed
 */
function create_cache_key($cachename, $params = array()) {
	$cache_all = cache_all();
	$cache_info = $cache_all['caches'][$cachename];

	if (empty($cache_info)) {
		message('缓存 ' . $cachename . ' 不存在!');
	} else {
		$cachekey = $cache_info['key'];
	}

	$arr = explode('%', $cachekey);
	if (count($arr) > 1) {
		unset($arr[0]);
		$arr = explode(':', implode($arr));
		$diff = array_diff($arr, array_keys($params));

		if (!empty($diff)) {
			message('缺少参数 : ' . implode($diff, ' 、'), '', '');
			exit();
		}

		if (is_array($params)) {
			foreach ($params as $key => $param) {
				$cachekey = str_replace('%' . $key, $param, $cachekey);
			}
		} else {
			message('参数格式不正确！');
			exit();
		}
	}
	return $cachekey;
}

/**
 * 根据缓存名称删除关联的缓存
 * @param $cachename
 * @param $params
 */
function cache_delete_cache_name($cachename, $params = array()) {
	$cache_all = cache_all();

	if (empty($cache_all['caches'][$cachename])) {
		message('缓存信息 ' . $cachename . '不存在');
	}

	$cache_info = $cache_all['caches'][$cachename];
	$cache_relations = $cache_all['relations'];

	if (!empty($cache_info['relation'])) {
		$relation_keys = $cache_relations[$cache_info['relation']]['relations'];
		$relation_params = $cache_relations[$cache_info['relation']]['relation_params'];

		$diff = @array_diff($relation_params, array_keys($params));
		if (!empty($diff)) {
			message('缺少参数 : ' . implode($diff, ' 、'), '', '');
			exit();
		}

		foreach ($relation_keys as $key => $val) {
			$cache_key = $cache_all['caches'][$val]['key'];
			foreach ($params as $key => $param) {
				$cache_key = str_replace('%' . $key, $param, $cache_key);
			}
			cache_delete($cache_key);
		}
	} else {
		$cache_key = $cache_info['key'];

		$arr = explode('%', $cache_key);
		if (count($arr) > 1) {
			unset($arr[0]);
			$arr = explode(':', implode($arr));
			$diff = array_diff($arr, array_keys($params));

			if (!empty($diff)) {
				message('缺少参数 : ' . implode($diff, ' 、'), '', '');
				exit();
			}

			foreach ($params as $key => $param) {
				$cache_key = str_replace('%' . $key, $param, $cache_key);
			}
		}

		cache_delete($cache_key);
	}
}

/**
 * 获取所有缓存键及缓存键的关联信息
 * @key string 缓存键值
 * @relation string 关联关系组名称
 * @relations array 关联关系组
 * @relation_params array 构建关联缓存键的参数
 * @return array
 */
function cache_all() {
	$system = 'we7:';
	$caches_all = array(
		'caches' => array(
			'module_info' => array(
				// 模块详细信息
				'key' => $system . 'module_info:%module_name',
				'relation' => 'module',
			),

			'module_setting' => array(
				// 模块配置信息
				'key' => $system . 'module_setting:%module_name:%uniacid',
				'relation' => 'module',
			),

			'last_account' => array(
				// 对某一模块，保留最后一次进入的小程序OR公众号，以便点进入列表页时可以默认进入
				'key' => $system . 'lastaccount:%switch',
				'relation' => 'module',
			),

			'user_modules' => array(
				//当前用户拥有的所有模块及小程序标识
				'key' => $system . 'user_modules:%uid',
				'relation' => '',
			),

			'user_accounts' => array(
				// 获取用户可操作的所有公众号或小程序或PC
				'key' => $system . 'user:account:%type:%uid',
				'relation' => '',
			),

			'unimodules' => array(
				// 当前公众号及所有者可用的模块(获取指定公号下所有安装模块及模块信息)
				'key' => $system . 'unimodules:%uniacid:%enabled',
				'relation' => '',
			),

			'unimodules_binding' => array(
				// 模块所有注册菜单
				'key' => $system . 'unimodules:binding:%uniacid',
				'relation' => '',
			),

			'uni_groups' => array(
				// 一个或多个公众号套餐信息
				'key' => $system . 'uni_group',
				'relation' => '',
			),

			'permission' => array(
				// 管理员或操作员权限数据
				'key' => $system . 'permission:%uniacid:%uid',
				'relation' => '',
			),

			'memberinfo' => array(
				'key' => $system . 'memberinfo:%uid',
				'relation' => '',
			),

			'statistics' => array(
				'key' => $system . 'statistics:%uniacid',
				'relation' => '',
			),

			'uniacid_visit' => array(
				'key' => $system . 'uniacid_visit:%uniacid:%today',
				'relation' => '',
			),

			'material_reply' => array(
				// 构造素材回复消息结构(回复消息结构)
				'key' => $system . 'material_reply:%attach_id',
				'relation' => '',
			),

			'keyword' => array(
				# conent 需要MD5 加密
				'key' => $system . 'keyword:%content:%uniacid',
				'relation' => '',
			),

			'back_days' => array(
				'key' => $system . 'back_days:',
				'relation' => '',
			),

			'wxapp_version' => array(
				'key' => $system . 'wxapp_version:%version_id',
				'relation' => '',
			),

			'site_store_buy' => array(
				'key' => $system . 'site_store_buy:%type:%uniacid',
				'relation' => '',
			),

			'proxy_wechatpay_account' => array(
				'key' => $system . 'proxy_wechatpay_account:',
				'relation' => '',
			),

			'recycle_module' => array(
				'key' => $system . 'recycle_module:',
				'relation' => '',
			),

			'all_cloud_upgrade_module' => array(
				'key' => $system . 'all_cloud_upgrade_module:',
				'relation' => '',
			),

			'module_all_uninstall' => array(
				'key' => $system . 'module:all_uninstall',
				'relation' => '',
			),

			'sync_fans_pindex' => array(
				'key' => $system . 'sync_fans_pindex:%uniacid',
				'relation' => '',
			),

			'uniaccount' => array(
				// 指定统一公众号下默认子号的信息
				'key' => "uniaccount:%uniacid",
				'relation' => 'uniaccount',
			),

			'unisetting' => array(
				// 公众号的配置项
				'key' => "unisetting:%uniacid",
				'relation' => 'uniaccount',
			),

			'defaultgroupid' => array(
				'key' => 'defaultgroupid:%uniacid',
				'relation' => 'uniaccount',
			),

			'uniaccount_type' => array(
				'key' => "uniaccount:%account_type",
				'relation' => '',
			),

			/* accesstoken */
			'accesstoken' => array(
				'key' => 'accesstoken:%acid',
				'relation' => 'accesstoken',
			),

			'jsticket' => array(
				'key' => $system . 'jsticket:%acid',
				'relation' => 'accesstoken',
			),

			'cardticket' => array(
				'key' => 'cardticket:%acid',
				'relation' => 'accesstoken',
			),
			/* accesstoken */

			'accesstoken_key' => array(
				'key' => 'accesstoken:%key',
				'relation' => '',
			),

			'account_auth_refreshtoken' => array(
				'key' => 'account:auth:refreshtoken:%acid',
				'relation' => '',
			),

			'unicount' => array(
				// 公众号下的子号的数量
				'key' => 'unicount:%uniacid',
				'relation' => '',
			),

			'checkupgrade' => array(
				'key' => 'checkupgrade:system',
				'relation' => '',
			),

			'cloud_transtoken' => array(
				'key' => 'cloud:transtoken',
				'relation' => '',
			),

			'upgrade' => array(
				'key' => 'upgrade',
				'relation' => '',
			),

			'account_ticket' => array(
				'key' => 'account:ticket',
				'relation' => '',
			),

			'oauthaccesstoken' => array(
				'key' => 'oauthaccesstoken:%acid',
				'relation' => '',
			),

			'account_component_assesstoken' => array(
				'key' => 'account:component:assesstoken',
				'relation' => '',
			),

			'cloud_ad_uniaccount' => array(
				'key' => 'cloud:ad:uniaccount:%uniacid',
				'relation' => '',
				'rels' => 'cloud_ad_uniaccount_list'
			),

			'cloud_ad_uniaccount_list' => array(
				'key' => 'cloud:ad:uniaccount:list',
				'relation' => '',
				'rels' => 'cloud_ad_uniaccount'
			),

			'cloud_flow_master' => array(
				'key' => 'cloud:flow:master',
				'relation' => '',
			),

			'cloud_ad_tags' => array(
				'key' => 'cloud:ad:tags',
				'relation' => '',
			),

			'cloud_ad_type_list' => array(
				'key' => 'cloud:ad:type:list',
				'relation' => '',
			),

			'cloud_ad_app_list' => array(
				'key' => 'cloud:ad:app:list:%uniacid',
				'relation' => '',
			),

			'cloud_ad_app_support_list' => array(
				'key' => 'cloud:ad:app:support:list',
				'relation' => '',
			),

			'cloud_ad_site_finance' => array(
				'key' => 'cloud:ad:site:finance',
				'relation' => '',
			),

			'couponsync' => array(
				'key' => 'couponsync:%uniacid',
				'relation' => '',
			),

			'storesync' => array(
				'key' => 'storesync:%uniacid',
				'relation' => '',
			),

			'cloud_auth_transfer' => array(
				'key' => 'cloud:auth:transfer',
				'relation' => '',
			),

			'modulesetting' => array(
				'key' => 'modulesetting:%module:%acid',
				'relation' => '',
			),

			'scan_config' => array(
				'key' => 'scan:config',
				'relation' => 'scan_file',
			),

			'scan_file' => array(
				'key' => 'scan:file',
				'relation' => 'scan_file',
			),

			'scan_badfile' => array(
				'key' => 'scan:badfile',
				'relation' => 'scan_file',
			),

			'bomtree' => array(
				'key' => 'bomtree',
				'relation' => '',
			),

			'setting' => array(
				'key' => 'setting',
				'relation' => '',
			),

			'stat_todaylock' => array(
				'key' => 'stat:todaylock:%uniacid',
				'relation' => '',
			),

			'account_preauthcode' => array(
				'key' => 'account:preauthcode',
				'relation' => '',
			),

			'account_auth_accesstoken' => array(
				'key' => 'account:auth:accesstoken:%key',
				'relation' => '',
			),

			'usersfields' => array(
				'key' => 'usersfields',
				'relation' => '',
			),

			'userbasefields' => array(
				'key' => 'userbasefields',
				'relation' => '',
			),

			'system_frame' => array(
				'key' => 'system_frame',
				'relation' => '',
			),

			'module_receive_enable' => array(
				'key' => 'module_receive_enable',
				'relation' => '',
			),
		),

		// 缓存键关联关系数组
		'relations' => array(

			'uniaccount' => array(
				'relations' => array('uniaccount', 'unisetting'),
				'relation_params' => array('uniacid'),
			),

			'accesstoken' => array(
				'relations' => array('accesstoken', 'jsticket', 'cardticket'),
				'relation_params' => array('acid'),
			),

			'scan_file' => array(
				'relations' => array('scan_file', 'scan_config', 'scan_badfile'),
				'relation_params' => array(),
			),

			// '模块'
			'module' => array(
				'relations' => array('module_info', 'module_setting'),
				'relation_params' => array('module_name', 'uniacid')
			),
		),
	);
	return $caches_all;
}
