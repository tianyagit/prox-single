<?php 
/**
 * MemCached缓存
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

function cache_memcache() {
	global $_W;
	static $memcacheobj;
	if (!extension_loaded('memcache')) {
		return error(1, 'Class Memcache is not found');
	}
	if (empty($memcacheobj)) {
		$config = $_W['config']['setting']['memcache'];
		$memcacheobj = new Memcache();
		if($config['pconnect']) {
			$connect = $memcacheobj->pconnect($config['server'], $config['port']);
		} else {
			$connect = $memcacheobj->connect($config['server'], $config['port']);
		}
		if(!$connect) {
			return error(-1, 'Memcache is not in work');
		}
	}
	return $memcacheobj;
}

/**
 * 取出缓存的单条数据
 *
 * @param 缓存键名 ，多个层级或分组请使用:隔开
 * @return mixed
 */
function cache_read($key, $forcecache = false) {
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	$result = $memcache->get(cache_prefix($key));
	if (empty($result) && empty($forcecache)) {
		$dbcache = pdo_get('core_cache', array('key' => $key), array('value'));
		if (!empty($dbcache['value'])) {
			$result = iunserializer($dbcache['value']);
			$memcache->set(cache_prefix($key), $result);
		}
	}
	return $result;
}


/**
 * 检索缓存中指定层级或分组的所有缓存
 *
 * @param 缓存分组
 * @return mixed
 */
function cache_search($key) {
	return cache_read(cache_prefix($key));
}

/**
 * 将值序列化并写入数据库( ims_core_cache )
 *
 * @param string $key
 * @param mixed $data
 * @return mixed
 */
function cache_write($key, $value, $ttl = 0, $forcecache = false) {
	$key = cache_namespace($key);
	
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	if (empty($forcecache)) {
		$record = array();
		$record['key'] = $key;
		$record['value'] = iserializer($value);
		pdo_insert('core_cache', $record, true);
	}
	if ($memcache->set(cache_prefix($key), $value, MEMCACHE_COMPRESSED, $ttl)) {
		return true;
	} else {
		return false;
	}
}

/**
 * 删除某个键的缓存数据
 * @param string $key
 * @return mixed 
 */
function cache_delete($key) {
	$key = cache_namespace($key);
	
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	if ($memcache->delete(cache_prefix($key))) {
		pdo_delete('core_cache', array('key' => $key));
		return true;
	} else {
		pdo_delete('core_cache', array('key' => $key));
		return false;
	}
}


/**
 * 清空缓存指定前缀或所有数据
 * @param string $prefix
 */
function cache_clean($prefix = '') {
	if (!empty($prefix)) {
		cache_namespace($prefix);
		return true;
	}
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	if ($memcache->flush()) {
		unset($_W['cache']);
		pdo_delete('core_cache');
		return true;
	} else {
		return false;
	}
}

/**
 * Memcache不支持按前缀删除缓存，自己实现一个
 * @param string $key
 * @return mixed
 */
function cache_namespace($key, $forcenew = false) {
	$add_namespace = array(
		cache_system_key('unimodules')
	);
	
	$add_cache_namespace = false;
	foreach ($add_namespace as $cache_key) {
		if (strexists($key, $cache_key)) {
			$add_cache_namespace = true;
			break;
		}
	}
	
	if ($add_cache_namespace || $forcenew) {
		//获取命名空间
		$namespace_cache_key = cache_system_key('cachensl:' . md5($cache_key));
		$namespace = pdo_getcolumn('core_cache', array('key' => $namespace_cache_key), 'value');
		if (empty($namespace)) {
			$namespace = random(3);
			pdo_insert('core_cache', array('key' => $namespace_cache_key, 'value' => $namespace), true);
		}
		$key = str_replace($cache_key, "{$cache_key}:{$namespace}", $key);
	}
	return $key;
}

function cache_prefix($key) {
	return $GLOBALS['_W']['config']['setting']['authkey'] . $key;
}