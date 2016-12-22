<?php
/**
 * 公众号回收站
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('account');

$dos = array('display', 'recover', 'delete');
$do = in_array($do, $dos) ? $do : 'display';
uni_user_permission_check('system_account');
$_W['page']['title'] = '公众号回收站 - 公众号';

if ($do == 'display') {
	$pindex = max(1, $_GPC['page']);
	$psize = 20;
	$start = ($pindex - 1) * $psize;

	$condition = '';
	$param = array();
	$keyword = trim($_GPC['keyword']);
	$condition .= " WHERE a.acid <> 0 AND b.isdeleted = 1 AND b.type = 1";
	$order_by = " ORDER BY a.`acid` DESC";
	if(!empty($keyword)) {
		$condition .=" AND a.`name` LIKE :name";
		$param[':name'] = "%{$keyword}%";
	}

	$tsql = "SELECT count(*) FROM ". tablename('account_wechats'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid {$condition} {$order_by}" ;
	$total = pdo_fetchcolumn($tsql, $param);
	$sql = "SELECT * FROM ". tablename('account_wechats'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid  {$condition} {$order_by}, a.`uniacid` DESC LIMIT {$start}, {$psize}";
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
	template('account/recycle');
}

if ($do == 'recover') {
	$state = uni_permission($_W['uid'], $uniacid);
	if($state != 'founder' && $state != 'manager') {
		message('没有权限！', referer(), 'error');
	}

	$acid = intval($_GPC['acid']);
	$uniacid = intval($_GPC['uniacid']);

	if (!empty($uniacid)) {
		pdo_update('account', array('isdeleted' => 0), array('uniacid' => $uniacid));
	} else {
		pdo_update('account', array('isdeleted' => 0), array('acid' => $acid));
	}
	message('公众号恢复成功', referer(), 'success');
}

if($do == 'delete') {
	$acid = intval($_GPC['acid']);

	if (!empty($acid)) {
		account_delete($acid);
	}
	message('删除公众号成功！', referer(), 'success');
}