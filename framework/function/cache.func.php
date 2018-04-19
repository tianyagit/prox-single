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
 * 生成缓存键
 * @param $cachekey
 * @param $params
 * @return mixed
 */
function create_cache_key($cachekey, $params) {
	foreach ($params as $key => $val) {
		$cachekey = str_replace('%' . $key, $val, $cachekey);
	}
	return $cachekey;
}

/**
 * 获取所有缓存键及缓存键的关联信息
 * @key string 缓存键值
 * @relation string 关联关系组名称（对应到 $caches_all['relations']）
 * @relations array 关联关系组
 * @relation_params array 构建关联缓存键的参数
 * @return array
 */
function cache_all() {
	$system = 'we7:';
	$caches_all = array(
		// 缓存键数组 (定义所有需要保存到缓存的键名，在添加缓存时，先把键名及相关信息添加到此数组中)
		'caches' => array(
			# -- module start --
			// 模块详细信息
			'module_info' => array(
				'key' => $system . 'module_info:%module_name', # cache_system_key(CACHE_KEY_MODULE_INFO, $name)
				'relation' => 'module',
			),
			// 模块配置信息
			'module_setting' => array(
				'key' => $system . 'module_setting:%module_name:%uniacid', # cache_system_key(CACHE_KEY_MODULE_SETTING, $module_name, $_W['uniacid'])
				'relation' => 'module',
			),
			// 对某一模块，保留最后一次进入的小程序OR公众号，以便点进入列表页时可以默认进入
			'last_account' => array(
				'key' => $system . 'lastaccount:%switch', # cache_system_key(CACHE_KEY_ACCOUNT_SWITCH, $_GPC['__switch'])
				'relation' => 'module',
			),
			# -- module end --

			'user' => array(
				'key' => 'user:%user_name',
				'relation' => 'user',
			),

			'company' => array(
				'key' => 'company:%company_name',
				'relation' => 'user',
			),

			'salary' => array(
				'key' => 'salary:%month:%uid',
				'relation' => 'user',
			),

			# -- user start --
			//当前用户拥有的所有模块及小程序标识
			'user_modules' => array(
				'key' => $system . 'user_modules:%uid', # cache_system_key('user_modules:' . $uid)
				'relation' => '',
			),
			# -- user end --

			# -- account start --
			// 获取用户可操作的所有公众号或小程序或PC
			'user_accounts' => array(
				'key' => "user_%s_accounts:%uid", # cache_system_key("user_{$type}_accounts:{$uid}")
				'relation' => '',
			),
			// 指定统一公众号下默认子号的信息
			'uniaccount' => array(
				'key' => "uniaccount:%uniacid", # "uniaccount:{$uniacid}"
				'relation' => '',
			),
			// 当前公众号及所有者可用的模块(获取指定公号下所有安装模块及模块信息)
			'unimodules' => array(
				'key' => 'unimodules:%uniacid:%enabled', # cache_system_key(CACHE_KEY_ACCOUNT_MODULES, $uniacid, $enabled),
				'relation' => '',
			),
			// 模块所有注册菜单
			'unimodules_binding' => array(
				'key' => 'unimodules:binding:%uniacid', # cache_system_key(CACHE_KEY_ACCOUNT_MODULES_BINDING, $_W['uniacid']),
				'relation' => '',
			),
			// 一个或多个公众号套餐信息
			'uni_groups' => array(
				'key' => 'uni_group', # cache_system_key(CACHE_KEY_UNI_GROUP),
				'relation' => '',
			),
			// 公众号的配置项
			'uni_setting' => array(
				'key' => "unisetting:%uniacid", # "unicount:{$uniacid}"
				'relation' => '',
			),
			// 公众号下的子号的数量
			'uni_count' => array(
				'key' => 'unicount:%uniacid', # "unicount:{$uniacid}"
				'relation' => '',
			),
			# -- account end --

			# -- permission start --
			// 管理员或操作员权限数据
			'permission' => array(
				'key' => 'permission:%uniacid:$uid', # cache_system_key("permission:{$_W['uniacid']}:{$_W['uid']}"),
				'relation' => '',
			),
			# -- permission end --

			'memberinfo' => array(
				'key' => $system . 'memberinfo:%uid', # cache_system_key(CACHE_KEY_MEMBER_INFO, $uid);
				'relation' => '',
			),
			'checkupgrade' => array(
				'key' => 'checkupgrade:system',
				'relation' => '',
			),
			'account_ticket' => array(
				'key' => 'account:ticket',
				'relation' => '',
			),
			'statistics' => array(
				'key' => $system . 'statistics:%uniacid', # cache_system_key("statistics:{$uniacid}")
				'relation' => '',
			),
			'uniacid_visit' => array(
				'key' => $system . 'uniacid_visit:%uniacid:%today', # cache_system_key("uniacid_visit:{$uniacid}:{$today}")
				'relation' => '',
			),
			'upgrade' => array(
				'key' => 'upgrade',
				'relation' => '',
			),
			'cloud_transtoken' => array(
				'key' => 'cloud:transtoken',
				'relation' => '',
			),
			'cloud_ad_uniaccount' => array(
				'key' => 'cloud:ad:uniaccount:%uniacid', # $cachekey = "cloud:ad:uniaccount:{$uniacid}";
				'relation' => '',
			),
			'cloud_ad_uniaccount_list' => array(
				'key' => 'cloud:ad:uniaccount:list', # $cachekey = "cloud:ad:uniaccount:list";
				'relation' => '',
			),
			'cloud_flow_master' => array(
				'key' => 'cloud:flow:master', # $cachekey = "cloud:flow:master";
				'relation' => '',
			),
			'cloud_ad_tags' => array(
				'key' => 'cloud:ad:tags', # $cachekey = "cloud:ad:tags";
				'relation' => '',
			),
			'cloud_ad_type_list' => array(
				'key' => 'cloud:ad:type:list', # $cachekey = "cloud:ad:type:list";
				'relation' => '',
			),
			'cloud_ad_app_list' => array(
				'key' => 'cloud:ad:app:list:%uniacid', # $cachekey = "cloud:ad:app:list:{$uniacid}";
				'relation' => '',
			),
			'cloud_ad_app_support_list' => array(
				'key' => 'cloud:ad:app:support:list', # $cachekey = "cloud:ad:app:support:list";
				'relation' => '',
			),
			'cloud_ad_site_finance' => array(
				'key' => 'cloud:ad:site:finance', # $cachekey = "cloud:ad:site:finance";
				'relation' => '',
			),
			'couponsync' => array(
				'key' => 'couponsync:%uniacid}', # $cachekey = "couponsync:{$_W['uniacid']}";
				'relation' => '',
			),
			'storesync' => array(
				'key' => 'storesync:%uniacid}', # $cachekey = "storesync:{$_W['uniacid']}";
				'relation' => '',
			),
			// 构造素材回复消息结构(回复消息结构)
			'material_reply' => array(
				'key' => 'material_reply:%attach_id', # $cachekey = cache_system_key('material_reply:' . $attach_id);
				'relation' => '',
			),
			'defaultgroupid' => array(
				'key' => 'defaultgroupid:%uniacid', # "defaultgroupid:{$_W['uniacid']}";
				'relation' => '',
			),
			'keyword' => array(
				# conent 需要MD5 加密
				'key' => $system . 'keyword:%content:$uniacid', # $cachekey = 'we7:' . 'keyword:' . md5($message['content'] . ':' . $_W['uniacid']);
				'relation' => '',
			),
			'cloud_auth_transfer' => array(
				'key' => 'cloud:auth:transfer', # 'cloud:auth:transfer';
				'relation' => '',
			),
			'modulesetting' => array(
				'key' => 'modulesetting:%module:%acid', # "modulesetting:{$data['module']}:{$data['acid']}";
				'relation' => '',
			),
			'account:ticket' => array(
				'key' => 'account:ticket', # 'account:ticket';
				'relation' => '',
			),
			'oauthaccesstoken' => array(
				'key' => 'oauthaccesstoken:%acid', # $cachekey = "oauthaccesstoken:{$this->account['acid']}";;
				'relation' => '',
			),
			'cardticket' => array(
				'key' => 'cardticket:%acid', # $cachekey = "cardticket:{$this->account['acid']}";
				'relation' => '',
			),
			'account_component_assesstoken' => array(
				'key' => 'account:component:assesstoken', # 'account:component:assesstoken';
				'relation' => '',
			),
			'scan_config' => array(
				'key' => 'scan:config', # scan:config;
				'relation' => '',
			),
			'scan_file' => array(
				'key' => 'scan:file', # scan:config;
				'relation' => '',
			),
			'scan_badfile' => array(
				'key' => 'scan:badfile', # scan:config;
				'relation' => '',
			),
			'bomtree' => array(
				'key' => 'bomtree', # bomtree
				'relation' => '',
			),
			'back_days' => array(
				'key' => 'back_days:', # cache_system_key("back_days:");
				'relation' => '',
			),
			'setting' => array(
				'key' => 'setting', # $cachekey = "setting";
				'relation' => '',
			),
			'accesstoken' => array(
				'key' => 'accesstoken:%acid', # $cachekey = "accesstoken:{$this->account['acid']}";
				'relation' => '',
			),
			'jsticket' => array(
				'key' => 'jsticket:%s', # jsticket:{$this->account['acid']}
				'relation' => '',
				'params' => array('acid'),
			),
			'wxapp_version' => array(
				'key' => 'wxapp_version:%version_id', # $cachekey = cache_system_key("wxapp_version:{$version_id}");
				'relation' => '',
			),
			'stat_todaylock' => array(
				'key' => 'stat:todaylock:%uniacid', # $cachekey = "stat:todaylock:{$_W['uniacid']}";
				'relation' => '',
			),
			'site_store_buy' => array(
				'key' => 'site_store_buy_%type:%uniacid', # $cachekey = cache_system_key('site_store_buy_' . $type . ':' . $uniacid);
				'relation' => '',
			),
		),

		// 缓存键关联关系数组 : 定义所有缓存键之间的关联关系，如果缓存有关联关系，需添加到此数组中，以便实现关联删除
		'relations' => array(
			// '模块'
			'module' => array(
				'relations' => array('module_info', 'module_setting'),
				'relation_params' => array('module_name', 'uniacid')
			),
			'user' => array(
				'relations' => array('user', 'company', 'salary'),
				'relation_params' => array('user_name', 'company_name', 'month', 'uid'),
			)
		),
	);
	return $caches_all;
}


/**
 * 缓存关联删除
 * @param $cachename $caches_all['caches'] 数组中定义的缓存名称
 * @param $params 构建缓存键需要的参数（此参数需要按照缓存关联数组 $caches_all['relations'] 中的 relation_params 的值来传入）
 */
function cache_delete_relation($cachename, $params) {
	$cache_all = cache_all();
	$cache_relations = $cache_all['relations'];
	$cache_info = $cache_all['caches'][$cachename];
	$relation_info = $cache_relations[$cache_info['relation']];
	$relation_params = $relation_info['relation_params'];

	$diff = array_diff($relation_params, array_keys($params));
	if (!empty($diff)) {
		message('缺少参数 : ' . implode($diff, ' 、'), '', '');
		exit();
	}

	$cache_key = $cache_info['key'];
	foreach ($params as $key => $val) {
		$cache_key = str_replace('%' . $key, $val, $cache_key);
	}

	cache_delete($cache_key);

	if (!empty($relation_info['relations']) && in_array($cachename, $relation_info['relations'])) {
		static $dig = -1;
		$count = count($relation_info['relations']) - 1;
		if ($dig++<$count) {
			cache_delete_relation($relation_info['relations'][$dig], $params);
		}
	}

}
