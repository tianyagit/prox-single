<?php
/**
 * 公众号列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('file');

$dos = array('display', 'delete');
$do = in_array($_GPC['do'], $dos)? $do : 'display';


$_W['page']['title'] = $account_typename . '列表 - ' . $account_typename;
//模版调用，显示该用户所在用户组可添加的主公号数量，已添加的数量，还可以添加的数量
$account_info = uni_user_account_permission();

if ($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$start = ($pindex - 1) * $psize;

	$condition = '';
	$param = array();
	$keyword = trim($_GPC['keyword']);
	if (!empty($_W['isfounder'])) {
		if (ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL) {
			$condition .= " WHERE a.acid <> 0 AND b.isdeleted <> 1 AND b.type = ".ACCOUNT_TYPE_APP_NORMAL;
		} else {
			$condition .= " WHERE a.acid <> 0 AND b.isdeleted <> 1 AND (b.type = ".ACCOUNT_TYPE_OFFCIAL_NORMAL." OR b.type = ".ACCOUNT_TYPE_OFFCIAL_AUTH.")";
		}
		$order_by = " ORDER BY a.`acid` DESC";
	} else {
		if (ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL) {
			$condition .= "LEFT JOIN ". tablename('uni_account_users')." as c ON a.uniacid = c.uniacid WHERE a.acid <> 0 AND c.uid = :uid AND b.isdeleted <> 1 AND b.type = ".ACCOUNT_TYPE_APP_NORMAL;
		} else {
			$condition .= "LEFT JOIN ". tablename('uni_account_users')." as c ON a.uniacid = c.uniacid WHERE a.acid <> 0 AND c.uid = :uid AND b.isdeleted <> 1 AND (b.type = ".ACCOUNT_TYPE_OFFCIAL_NORMAL." OR b.type = ".ACCOUNT_TYPE_OFFCIAL_AUTH.")";
		}
		$param[':uid'] = $_W['uid'];
		$order_by = " ORDER BY c.`rank` DESC, a.`acid` DESC";
	}
	if(!empty($keyword)) {
		$condition .=" AND a.`name` LIKE :name";
		$param[':name'] = "%{$keyword}%";
	}
	if (ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL) {
		$tsql = "SELECT COUNT(*) FROM " . tablename('account_wxapp'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid {$condition} {$order_by}, a.`uniacid` DESC";
		$sql = "SELECT * FROM ". tablename('account_wxapp'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid {$condition} {$order_by}, a.`uniacid` DESC LIMIT {$start}, {$psize}";
	} else {
		$tsql = "SELECT COUNT(*) FROM " . tablename('account_wechats'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid {$condition} {$order_by}, a.`uniacid` DESC";
		$sql = "SELECT * FROM ". tablename('account_wechats'). " as a LEFT JOIN". tablename('account'). " as b ON a.acid = b.acid {$condition} {$order_by}, a.`uniacid` DESC LIMIT {$start}, {$psize}";
	}
	$total = pdo_fetchcolumn($tsql, $param);
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
	template('account/manage-display' . ACCOUNT_TYPE_TEMPLATE);
}
if ($do == 'delete') {
	$uniacid = intval($_GPC['uniacid']);
	$acid = intval($_GPC['acid']);
	$uid = $_W['uid'];
	$type = intval($_GPC['type']);
	//只有创始人、主管理员才有权限停用公众号
	$state = uni_permission($uid, $uniacid);
	if ($state != ACCOUNT_MANAGE_NAME_OWNER && $state != ACCOUNT_MANAGE_NAME_FOUNDER) {
		message('无权限操作！', url('account/manage'), 'error');
	}
	if (!empty($acid) && empty($uniacid)) {
		$account = account_fetch($acid);
		if (empty($account)) {
			message('子公众号不存在或是已经被删除');
		}
		$uniaccount = uni_fetch($account['uniacid']);
		if ($uniaccount['default_acid'] == $acid) {
			message('默认子公众号不能删除');
		}
		pdo_update('account', array('isdeleted' => 1), array('acid' => $acid));
		message('删除子公众号成功！您可以在回收站中回复公众号', referer(), 'success');
	}
	
	if (!empty($uniacid)) {
		$account = pdo_get('uni_account', array('uniacid' => $uniacid));
		if (ACCOUNT_TYPE == ACCOUNT_TYPE_APP_NORMAL) {
			$redirect_url = url('account/manage', array('account_type' => '4'));
		} else {
			$redirect_url = url('account/manage');
		}
		if (empty($account)) {
			message('抱歉，帐号不存在或是已经被删除', $redirect_url, 'error');
		}
		$state = uni_permission($uid, $uniacid);
		if($state != 'founder' && $state != 'manager') {
			message('没有该'. ACCOUNT_TYPE_NAME . '操作权限！', $redirect_url, 'error');
		}
		pdo_update('account', array('isdeleted' => 1), array('uniacid' => $uniacid));
		if($_GPC['uniacid'] == $_W['uniacid']) {
			isetcookie('__uniacid', '');
		}
		cache_delete("unicount:{$uniacid}");
		cache_delete("unisetting:{$uniacid}");
	}
	message('停用成功！，您可以在回收站中恢复', $redirect_url, 'success');
}