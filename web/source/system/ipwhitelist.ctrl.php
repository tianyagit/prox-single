<?php
/**
 * ip白名单
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('site');

$dos = array('display', 'change_status', 'add', 'delete');
$do = in_array($_GPC['do'], $dos)? $do : 'display';
$_W['page']['title'] = '站点管理 - 设置  - IP白名单';

if ($do == 'display') {
	$keyword = trim($_GPC['keyword']);
	$condition = array();
	if (!empty($keyword)) {
		$condition['ip LIKE'] = "%" . $keyword . "%";
	}

	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$lists = pdo_getslice('ip_list', $condition, array($pindex, $psize), $total, array(), '', array('id DESC'));
	$pager = pagination($total, $pindex, $psize);
}

if ($do == 'change_status') {
	$id = intval($_GPC['id']);
	$status = pdo_getcolumn('ip_list', array('id' => $id), 'status');
	$status = empty($status) ? 1 : 0;
	$update = pdo_update('ip_list', array('status' => $status), array('id' => $id));
	iajax(0, '');
}

if ($do == 'add') {
	$ips = $_GPC['ips'];
	$ip_data = site_ip_add($ips);
	if (is_error($ip_data)) {
		iajax(-1, $ip_data['message']);
	}
	iajax(0, '添加成功', url('system/ipwhitelist'));
}

if ($do == 'delete') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		itoast('参数错误');
	}
	$id_exists = pdo_getcolumn('ip_list', array('id' => $id), 'id');
	if (empty($id_exists)) {
		itoast('此条记录不存在');
	}
	pdo_delete('ip_list', array('id' => $id));
	itoast('删除成功', url('system/ipwhitelist'));
}
template('system/ip-list');