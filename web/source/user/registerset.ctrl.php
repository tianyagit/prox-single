<?php
/**
 * 用户注册设置
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('setting');

$_W['page']['title'] = '用户登录/注册设置 - 注册设置';

if (checksubmit('submit')) {
	setting_save(array('open' => intval($_GPC['open']), 'verify' => intval($_GPC['verify']), 'code' => intval($_GPC['code']), 'groupid' => intval($_GPC['groupid']), 'safe' => intval($_GPC['safe'])), 'register');
	cache_delete(cache_system_key('defaultgroupid', array('uniacid' => $_W['uniacid'])));
	itoast('更新设置成功！', url('user/registerset'), 'success');
}
$settings = $_W['setting']['register'];
$groups = user_group();

template('user/registerset');