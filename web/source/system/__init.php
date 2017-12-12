<?php
/**
 * 系统管理公共文件
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
if (in_array($action, array('site', 'menu', 'attachment', 'systeminfo', 'logs', 'filecheck', 'optimize',
	'database', 'scan', 'bom', 'ipwhitelist', 'workorder', 'sensitiveword', 'thirdlogin', 'oauth'))) {
	define('FRAME', 'site');
} else {
	define('FRAME', 'system');
}

$account_api = WeAccount::create();
$account_param = WeAccount::createByType($_GPC['account_type']);
define('ACCOUNT_TYPE', $account_param->accountManageType);
define('ACCOUNT_TYPE_TEMPLATE', $account_param->accountTypeTemplate);