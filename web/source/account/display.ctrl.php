<?php
/**
 * 切换公众号
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('rank', 'display', 'switch');
$do = in_array($_GPC['do'], $dos)? $do : 'display' ;
$_W['page']['title'] = '公众号列表 - 公众号';

$state = uni_permission($_W['uid'], $uniacid);

if($do == 'switch') {
	$uniacid = intval($_GPC['uniacid']);
	$role = uni_permission($_W['uid'], $uniacid);
	if(empty($role)) {
		message('操作失败, 非法访问.');
	}
	isetcookie('__uniacid', $uniacid, 7 * 86400);
	isetcookie('__uid', $_W['uid'], 7 * 86400);

	if($_W['role'] == 'clerk' || $role == 'clerk') {
		header('location: ' . url('paycenter/desk'));
		die;
	}
	header('Location: ' . url('platform/reply'));
	exit;
}

if ($do == 'rank' && $_W['isajax'] && $_W['ispost']) {
	$uniacid = intval($_GPC['id']);

	$exist = pdo_get('uni_account', array('uniacid' => $uniacid));
	if (empty($exist)) {
		message(error(1, '公众号不存在'), '', 'ajax');
	}
	if (!empty($_W['isfounder'])) {
		$max_rank= pdo_fetch("SELECT max(rank) as maxrank FROM ".tablename('uni_account'));
		pdo_update('uni_account', array('rank' => ($max_rank['maxrank']+1)), array('uniacid' => $uniacid));
	}else {
		$max_rank= pdo_fetch("SELECT max(rank) as maxrank FROM ".tablename('uni_account_users'));
		pdo_update('uni_account_users', array('rank' => ($max_rank['maxrank']+1)), array('uniacid' => $uniacid, 'uid' => $_W['uid']));
	}
	message(error(0), '', 'ajax');
}

if ($do == 'display') {
	include IA_ROOT . '/framework/library/pinyin/pinyin.php';
	$pinyin = new Pinyin_Pinyin();

	$pindex = max(1, intval($_GPC['page']));
	$psize = 8;
	$start = ($pindex - 1) * $psize;

	$condition = '';
	$param = array();
	$keyword = trim($_GPC['keyword']);
	if (!empty($_W['isfounder'])) {
		$condition .= " WHERE a.default_acid <> 0 AND b.isdeleted <> 1 AND b.type = 1";
		$order_by = " ORDER BY a.`rank` DESC";
	} else {
		$condition .= "LEFT JOIN ". tablename('uni_account_users')." as c ON a.uniacid = c.uniacid WHERE a.default_acid <> 0 AND c.uid = :uid AND b.isdeleted <> 1 AND b.type = 1";
		$param[':uid'] = $_W['uid'];
		$order_by = " ORDER BY c.`rank` DESC";
	}
	if(!empty($keyword)) {
		$condition .=" AND a.`name` LIKE :name";
		$param[':name'] = "%{$keyword}%";
	}
	if(isset($_GPC['letter']) && strlen($_GPC['letter']) == 1) {
		$letter = trim($_GPC['letter']);
		$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		if(in_array($letter, $letters)){
			$condition .= " AND a.`letter` = :letter";
			$param[':letter'] = $letter;
		}else {
			$condition .= " AND a.`letter` = ''";
		}
	}

	$tsql = "SELECT COUNT(*) FROM " . tablename('uni_account'). " as a LEFT JOIN". tablename('account'). " as b ON a.default_acid = b.acid {$condition} {$order_by}, a.`uniacid` DESC";
	$total = pdo_fetchcolumn($tsql, $param);
	$sql = "SELECT * FROM ". tablename('uni_account'). " as a LEFT JOIN". tablename('account'). " as b ON a.default_acid = b.acid  {$condition} {$order_by}, a.`uniacid` DESC LIMIT {$start}, {$psize}";
	$list = pdo_fetchall($sql, $param);
	if(!empty($list)) {
		foreach($list as &$account) {
			$account['url'] = url('account/display/switch', array('uniacid' => $account['uniacid']));
			$account['details'] = uni_accounts($account['uniacid']);
			if(!empty($account['details'])) {
				foreach ($account['details'] as  &$account_val) {
					$account_val['thumb'] = tomedia('headimg_'.$account_val['acid']. '.jpg').'?time='.time();
					$account_val['title_first_pinyin'] = $pinyin->get_first_char($account_val['name']);
				}
			}
			$account['role'] = uni_permission($_W['uid'], $account['uniacid']);
			$account['setmeal'] = uni_setmeal($account['uniacid']);
		}
		unset($account);
	}
	$pager = pagination($total, $pindex, $psize);
}

template('account/display');