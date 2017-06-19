<?php
/**
 * 自动回复公共组建（关键字）
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
error_reporting(0);
if (!in_array($do, array('keyword'))) {
	exit('Access Denied');
}

if($do == 'keyword') {
	$type = trim($_GPC['type']);
	if ($type == 'all') {
		$condition_sql = " WHERE uniacid = :uniacid AND status = 1 ";
		$condition = array(':uniacid' => $_W['uniacid']);
	} else {
		$condition_sql = " WHERE uniacid = :uniacid AND status = 1 AND module = :module ";
		$condition = array(':uniacid' => $_W['uniacid'], ':module' => $type);
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 24;
	$sql = "SELECT %s FROM " . tablename('rule') . $condition_sql . "%s";
	$total_sql = sprintf($sql, 'COUNT(*)', '');
	$list_sql = sprintf($sql, '*', " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ", {$psize}");
	$total = pdo_fetchcolumn($total_sql, $condition);
	$lists = pdo_fetchall($list_sql, $condition, 'id');
	if(!empty($lists)) {
		foreach($lists as &$row) {
			if($type == 'all') {
				$row['child_items'] = pdo_getall('rule_keyword', array('uniacid' => $_W['uniacid'], 'rid' => $row['id'], 'status' => 1));
			} else {
				$row['child_items'] = pdo_getall('rule_keyword', array('uniacid' => $_W['uniacid'], 'rid' => $row['id'], 'status' => 1, 'module' => $type));
			}
		}
		unset($row);
	}
	$result = array(
			'items' => $lists,
			'pager' => pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null')),
	);
	iajax(0, $result);
}