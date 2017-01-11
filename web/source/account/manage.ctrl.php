<?php
/**
 * 公众号列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('file');

$dos = array('display', 'delete');
$do = in_array($_GPC['do'], $dos)? $do : 'display';
uni_user_permission_check('system_account');
$_W['page']['title'] = '公众号列表 - 公众号';

if ($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$start = ($pindex - 1) * $psize;

	$condition = '';
	$param = array();
	$keyword = trim($_GPC['keyword']);
	if (!empty($_W['isfounder'])) {
		$condition .= " WHERE a.acid <> 0 AND b.isdeleted <> 1 AND b.type = 1";
		$order_by = " ORDER BY a.`acid` DESC";
	} else {
		$condition .= "LEFT JOIN ". tablename('uni_account_users')." as c ON a.uniacid = c.uniacid WHERE a.acid <> 0 AND c.uid = :uid AND b.isdeleted <> 1 AND b.type = 1";
		$param[':uid'] = $_W['uid'];
		$order_by = " ORDER BY c.`rank` DESC, a.`acid` DESC";
	}
	if(!empty($keyword)) {
		$condition .=" AND a.`name` LIKE :name";
		$param[':name'] = "%{$keyword}%";
	}

	$tsql = "SELECT COUNT(*) FROM " . tablename('account_wechats'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid {$condition} {$order_by}, a.`uniacid` DESC";
	$total = pdo_fetchcolumn($tsql, $param);
	$sql = "SELECT * FROM ". tablename('account_wechats'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid  {$condition} {$order_by}, a.`uniacid` DESC LIMIT {$start}, {$psize}";
	$list = pdo_fetchall($sql, $param);
	if(!empty($list)) {
		foreach($list as &$account) {
			$settings = uni_setting($account['uniacid'], array('notify'));
			if(!empty($settings['notify'])) {
				$account['sms'] = $settings['notify']['sms']['balance'];
			}else {
				$account['sms'] = 0;
			}
			$account['thumb'] = tomedia('headimg_'.$account['acid']. '.jpg').'?time='.time();
			$account['role'] = uni_permission($_W['uid'], $account['uniacid']);
			$account['setmeal'] = uni_setmeal($account['uniacid']);
		}
		unset($account);
	}

	$pager = pagination($total, $pindex, $psize);
	template('account/manage-display');
}
if ($do == 'delete') {
	$uniacid = intval($_GPC['uniacid']);
	$acid = intval($_GPC['acid']);
	$uid = $_W['uid'];
	if (!empty($acid) && empty($uniacid)) {
		$account = account_fetch($acid);
		if (empty($account)) {
			message('子公众号不存在或是已经被删除');
		}
		$state = uni_permission($uid, $uniacid);
		if($state != 'founder' && $state != 'manager') {
			message('没有该公众号操作权限！', url('account/manage'), 'error');
		}
		$uniaccount = uni_fetch($account['uniacid']);
		if ($uniaccount['default_acid'] == $acid) {
			message('默认子公众号不能删除');
		}
		pdo_update('account', array('isdeleted' => 1), array('acid' => $acid));
		message('删除子公众号成功！您可以在回收站中回复公众号', referer(), 'success');
	}
	
	if (!empty($uniacid)) {
		$account = pdo_fetch("SELECT * FROM ".tablename('uni_account')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
		if (empty($account)) {
			message('抱歉，帐号不存在或是已经被删除', url('account/manage'), 'error');
		}
		$state = uni_permission($uid, $uniacid);
		if($state != 'founder' && $state != 'manager') {
			message('没有该公众号操作权限！', url('account/manage'), 'error');
		}
		pdo_update('account', array('isdeleted' => 1), array('uniacid' => $uniacid));
		if($_GPC['uniacid'] == $_W['uniacid']) {
			isetcookie('__uniacid', '');
		}
		cache_delete("unicount:{$uniacid}");
		cache_delete("unisetting:{$uniacid}");
	}
	message('公众帐号停用成功！，您可以在回收站中回复公众号', url('account/manage'), 'success');
}