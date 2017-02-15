<?php
/**
 * 系统管理公共文件
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
define('FRAME', 'system');

if ($_GPC['account_type'] == ACCOUNT_TYPE_APP_NORMAL) {
	define('ACCOUNT_TYPE', ACCOUNT_TYPE_APP_NORMAL);
	define('ACCOUNT_TYPE_TEMPLATE', '-wxapp');
} else {
	define('ACCOUNT_TYPE', 0);
	define('ACCOUNT_TYPE_TEMPLATE', '');
}