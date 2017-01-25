<?php
/**
 * 公众号回收站
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('account');

$dos = array('display', 'recover', 'delete');
$do = in_array($do, $dos) ? $do : 'display';
//只有创始人、主管理员才有权限使用回收站功能
if ($_W['role'] != ACCOUNT_MANAGE_NAME_MANAGER && $_W['role'] != ACCOUNT_MANAGE_NAME_FOUNDER) {
	message('无权限操作！', referer(), 'error');
}
$_W['page']['title'] = $account_typename . '回收站 - ' . $account_typename;

if ($do == 'display') {
	$pindex = max(1, $_GPC['page']);
	$psize = 20;
	$start = ($pindex - 1) * $psize;

	$condition = '';
	$param = array();
	$keyword = trim($_GPC['keyword']);
	if (ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL) {
		$condition .= " WHERE a.acid <> 0 AND b.isdeleted = 1 AND b.type = 4";
	} else {
		$condition .= " WHERE a.acid <> 0 AND b.isdeleted = 1 AND (b.type = 1 OR b.type = 3)";
	}
	
	$order_by = " ORDER BY a.`acid` DESC";
	if(!empty($keyword)) {
		$condition .=" AND a.`name` LIKE :name";
		$param[':name'] = "%{$keyword}%";
	}
	if (ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL) {
		$tsql = "SELECT count(*) FROM ". tablename('account_wxapp'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid {$condition} {$order_by}" ;
		$sql = "SELECT * FROM ". tablename('account_wxapp'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid  {$condition} {$order_by}, a.`uniacid` DESC LIMIT {$start}, {$psize}";
	} else {
		$tsql = "SELECT count(*) FROM ". tablename('account_wechats'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid {$condition} {$order_by}" ;
		$sql = "SELECT * FROM ". tablename('account_wechats'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid  {$condition} {$order_by}, a.`uniacid` DESC LIMIT {$start}, {$psize}";
	}
	// if ($account_type != ACCOUNT_TYPE_APP_NORMAL) {
		
	// } else {
		
	// }
	$total = pdo_fetchcolumn($tsql, $param);
	$del_accounts = pdo_fetchall($sql, $param);
	if(!empty($del_accounts)) {
		foreach ($del_accounts as &$account) {
			$settings = uni_setting($account['uniacid'], array('notify'));
			if(!empty($settings['notify'])) {
				$account['sms'] = $settings['notify']['sms']['balance'];
			}else {
				$account['sms'] = 0;
			}
			$account['thumb'] = tomedia('headimg_'.$account['acid']. '.jpg').'?time='.time();
			$account['setmeal'] = uni_setmeal($account['uniacid']);
		}
	}

	$pager = pagination($total, $pindex, $psize);
	template('account/recycle' . ACCOUNT_TYPE_TEMPLATE);
}

if ($do == 'recover') {
	$acid = intval($_GPC['acid']);
	$uniacid = intval($_GPC['uniacid']);
	$state = uni_permission($_W['uid'], $uniacid);
	if($state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_MANAGER) {
		message('没有权限！', referer(), 'error');
	}

	if (!empty($uniacid)) {
		pdo_update('account', array('isdeleted' => 0), array('uniacid' => $uniacid));
	} else {
		pdo_update('account', array('isdeleted' => 0), array('acid' => $acid));
	}
	message('恢复成功', referer(), 'success');
}

if($do == 'delete') {
	$uniacid = intval($_GPC['uniacid']);
	$acid = intval($_GPC['acid']);
	
	$state = uni_permission($_W['uid'], $uniacid);
	if($state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_OWNER) {
		message('没有权限！', referer(), 'error');
	}

	if (!empty($acid)) {
		account_delete($acid);
	}
	message('删除成功！', referer(), 'success');
}