<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');

if (empty($action)) {
	$action = 'site';
}

$eid = intval($_GPC['eid']);
if(!empty($eid)) {
	$entry = module_entry($eid);
} else {
	$entry = array(
		'module' => $_GPC['m'],
		'do' => $_GPC['do'],
		'state' => $_GPC['state'],
		'direct' => 0,
	);
}
$modules = uni_modules();
if (!empty($_W['account']) && in_array($_W['account']['type'], array(ACCOUNT_TYPE_APP_NORMAL, ACCOUNT_TYPE_APP_AUTH))) {
	$account_owner = account_owner($_W['uniacid']);
	$user_modules = user_modules($account_owner['uid']);
	$modules = array_merge($modules, $user_modules);
}
if (empty($modules[$entry['module']])) {
	message('您访问的功能模块不存在，请重新进入');
}
if(empty($entry) || empty($entry['do'])) {
	message('非法访问.');
}

$_GPC['__entry'] = $entry['title'];
$_GPC['__state'] = $entry['state'];
$_GPC['state'] = $entry['state'];
$_GPC['m'] = $entry['module'];
$_GPC['do'] = $entry['do'];

$_W['current_module'] = $modules[$entry['module']];
define('IN_MODULE', $entry['module']);