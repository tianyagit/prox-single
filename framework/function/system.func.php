<?php

/**
 * 微擎系统内部公共函数
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
/**
 *  获取配置参数
 * @param null $key
 * @param null $default
 * @return Config|mixed|null
 */
function config($key = null, $default = null) {
	load()->classs('config');
	$config = Config::instance();
	if(is_null($key)) {
		return $config;
	}
	return $config->get($key, $default);
}
