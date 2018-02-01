<?php
defined('IN_IA') or exit('Access Denied');

load()->model('phoneapp');
load()->model('welcome');

$do = safe_gpc_belong($do, array('display', 'home'), 'display');

$_W['page']['title'] = 'APP - 管理';

$version_id = safe_gpc_int($_GPC['version_id']);
$phoneapp_info = phoneapp_fetch($_W['uniacid']);

if (!empty($version_id)) {
	$version_info = phoneapp_version($version_id);
}

if ($do == 'display') {
	$wxapp_version_list = wxapp_version_all($_W['uniacid']);
	template('phoneapp/version-display');
}

if ($do == 'home') {
	$role = permission_account_user_role($_W['uid'], $wxapp_info['uniacid']);
	$notices = welcome_notices_get();
	template('phoneapp/version-home');
}