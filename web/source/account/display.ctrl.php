<?php
/**
 * 切换公众号
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('rank', 'display', 'switch');
$do = in_array($_GPC['do'], $dos)? $do : 'display' ;
$_W['page']['title'] = '公众号列表 - 公众号';

$state = uni_permission($_W['uid'], $_W['uniacid']);
//模版调用，显示该用户所在用户组可添加的主公号数量，已添加的数量，还可以添加的数量
$account_info = uni_user_account_permission();

if($do == 'switch') {
	$uniacid = intval($_GPC['uniacid']);
	$role = uni_permission($_W['uid'], $uniacid);
	if(empty($role)) {
		itoast('操作失败, 非法访问.', '', 'error');
	}
	uni_account_save_switch($uniacid);
	$module_name = trim($_GPC['module_name']);
	$version_id = intval($_GPC['version_id']);
	if (empty($module_name)) {
		$url = url('home/welcome');
	} else {
		$url = url('home/welcome/ext', array('m' => $module_name, 'version_id' => $version_id));
	}
	uni_account_switch($uniacid, $url);
}

if ($do == 'rank' && $_W['isajax'] && $_W['ispost']) {
	$uniacid = intval($_GPC['id']);

	$exist = pdo_get('uni_account', array('uniacid' => $uniacid));
	if (empty($exist)) {
		iajax(1, '公众号不存在', '');
	}
	uni_account_rank_top($uniacid);
	iajax(0, '更新成功！', '');
}

if ($do == 'display') {
	$account = uni_site_account();
	if (empty($account)) {
		itoast('', url('account/post-step'), 'info');
	}
	$account['url'] = url('account/display/switch', array('uniacid' => $account['uniacid']));
	$account['details'] = uni_accounts($account['uniacid']);
	if(!empty($account['details'])) {
		foreach ($account['details'] as  &$account_val) {
			$account_val['thumb'] = tomedia('headimg_'.$account_val['acid']. '.jpg').'?time='.time();
		}
	}
	$account['role'] = uni_permission($_W['uid'], $account['uniacid']);
	$account['setmeal'] = uni_setmeal($account['uniacid']);
}

template('account/display');