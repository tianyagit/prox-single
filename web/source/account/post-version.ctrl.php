<?php
/**
 * 管理公众号--使用者管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('system');

$dos = array('delete', 'display');
$do = in_array($do, $dos) ? $do : 'display';

$uniacid = intval($_GPC['uniacid']);
$acid = intval($_GPC['acid']);
if (empty($uniacid)) {
	message('请选择要编辑的小程序', referer(), 'error');
}

$state = uni_permission($_W['uid'], $uniacid);
//只有创始人、主管理员、管理员才有权限
if ($state != ACCOUNT_MANAGE_NAME_OWNER && $state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_MANAGER) {
	message('无权限操作！', referer(), 'error');
}

if ($do == 'display') {
	$account = account_fetch($acid);
	$uniacid = intval($_GPC['uniacid']);
	if (!empty($uniacid)) {
		$wxapp_version_lists = pdo_getall('wxapp_versions', array('uniacid' => $uniacid));
	}
	template('account/manage-version-wxapp');
}

if ($do == 'delete') {
	$id = intval($_GPC['id']);
	$version_info = pdo_get('wxapp_versions', array('id' => $id));
	if (!empty($version_info)) {
		pdo_delete('wxapp_versions', array('id' => $id));
	} else {
		message('版本不存在', referer(), 'error');
	}
	message('删除成功', referer(), 'success');
}