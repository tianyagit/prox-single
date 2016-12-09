<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/account/post-step.ctrl.php : v 3f913000f3af : 2015/09/16 08:51:31 : yanghf $
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'post');
$do = in_array($do, $dos) ? $do : 'display';
$_W['page']['title'] = '公众号回收站 - 公众号';
uni_user_permission_check('system_account');

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
	$pager = pagination($total, $pindex, $psize);
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
	template('account/recycle');
}
if ($do == 'post') {
	load()->model('account');
	$acid = intval($_GPC['acid']);
	$uniacid = intval($_GPC['uniacid']);
	$op = trim($_GPC['op']);
	$state = uni_permission($_W['uid'], $uniacid);
	if($state != 'founder' && $state != 'manager') {
		message('没有权限！', referer(), 'error');
	}
	if ($op == 'recover') {
		if (!empty($uniacid)) {
			pdo_update('account', array('isdeleted' => 0), array('uniacid' => $uniacid));
		} else {
			pdo_update('account', array('isdeleted' => 0), array('acid' => $acid));
		}
		message('公众号恢复成功', referer(), 'success');
	}elseif ($op == 'delete') {
		if (!empty($acid)) {
			account_delete($acid);
		}
		message('删除公众号成功！', url('account/recycle/display'), 'success');
	}
}