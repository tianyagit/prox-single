<?php
/**
 * 微站导航管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('home', 'uc');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'home';

uni_user_permission_check('platform_nav_' . $do, true, 'nav');
$modulename = $_GPC['m'];

if ($do == 'home' || $do == 'uc') {
	
}