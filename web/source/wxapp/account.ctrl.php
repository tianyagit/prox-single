<?php
/**
 * 小程序列表
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
define('IN_GW', true);

$_W['page']['title'] = '小程序列表';

$dos = array('display', 'switch');
$do = in_array($do, $dos) ? $do : 'display';

if ($do == 'display') {
	$sql = "SELECT * FROM ".tablename('uni_account'). " AS a INNER JOIN " . tablename('account_wxapp') . " AS b ON a.uniacid = b.uniacid";
	$wxapp_list = pdo_fetchall($sql, array(), 'uniacid');
	template('wxapp/account-display');
} elseif ($do == 'switch') {
	$uniacid = intval($_GPC['uniacid']);
	$version = pdo_get('wxapp_versions', array('uniacid' => $uniacid), array('version', 'multiid'), 'version DESC');
	isetcookie('__uniacid', $uniacid, 7 * 86400);
	isetcookie('__uid', $_W['uid'], 7 * 86400);
	header('Location: ' . url('wxapp/manage/edit', array('multiid' => $version['multiid'])));
	exit;
}