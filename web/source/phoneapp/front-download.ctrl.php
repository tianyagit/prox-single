<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('phoneapp');

$do = safe_gpc_belong($do, array('display'), 'display');

$_W['page']['title'] = 'APP - 上传下载';

$version_id = safe_gpc_int($_GPC['version_id']);
$phoneapp_info = phoneapp_fetch($_W['uniacid']);

if ($do == 'display') {

	template('phoneapp/front-download');
}