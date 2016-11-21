<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pros/web/source/account/display.ctrl.php : 2016年11月5日 10:18:14 $
 */
defined('IN_IA') or exit('Access Denied');

$_W['page']['title'] = '公众号列表 - 公众号';
$dos = array('rank', 'display', 'switch');
$do = in_array($_GPC['do'], $dos)? $do : 'display' ;

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
	header('location: ' . url('platform/reply'));
}

if ($do == 'rank' && $_W['isajax']) {
	$uniacid = intval($_GPC['__input']['id']);

	$exist = pdo_get('uni_account', array('uniacid' => $uniacid));
	if (empty($exist)) {
		message(error(1, '公众号不存在'), '', 'ajax');
	}
	if (!empty($_W['isfounder'])) {
		pdo_update('uni_account', array('rank' => 5, 'ranktime' => time()), array('uniacid' => $uniacid));
	}else {
		pdo_update('uni_account_users', array('rank' => 5, 'ranktime' => time()), array('uniacid' => $uniacid, 'uid' => $_W['uid']));
	}
	message(error(0), '', 'ajax');
}

if ($do == 'display') {
	//是否存在letter字段，否则添加并更新
	if(!pdo_fieldexists('uni_account', 'letter')) {
		$add_letter = pdo_query("ALTER TABLE ". tablename('uni_account') . " ADD `letter` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'title首字母' , ADD FULLTEXT (`letter`)");
		if($add_letter) {
			$sql = '';
			$all_account = pdo_fetchall("SELECT uniacid,name FROM ". tablename('uni_account'));
			foreach ($all_account as $all_value) {
				$letter = '';
				$letter = get_first_char($all_value['name']);
				$sql .= "UPDATE ". tablename('uni_account'). " SET `letter` = '". $letter . "' WHERE `uniacid` = {$all_value['uniacid']};";
			}
			$run = pdo_run($sql);
			if($run){
				pdo_query("ALTER TABLE ". tablename('uni_account') ." DROP `letter`");
			}
		}
	}

	$pindex = max(1, intval($_GPC['page']));
	$psize = 8;
	$start = ($pindex - 1) * $psize;
	$condition = '';
	$param = array();
	$keyword = trim($_GPC['keyword']);
	$letter = trim($_GPC['letter']);
	$s_uniacid = intval($_GPC['s_uniacid']);
	if (!empty($_W['isfounder'])) {
		$condition .= " WHERE a.default_acid <> 0 AND b.isdeleted <> 1 AND b.type = 1";
		$order_by = " ORDER BY a.`rank` DESC, a.`ranktime` DESC";
	} else {
		$condition .= "LEFT JOIN ". tablename('uni_account_users')." as c ON a.uniacid = c.uniacid WHERE a.default_acid <> 0 AND c.uid = :uid AND b.isdeleted <> 1 AND b.type = 1";
		$param[':uid'] = $_W['uid'];
		$order_by = " ORDER BY c.`rank` DESC, a.`ranktime` DESC";
	}
	if(!empty($keyword)) {
		$condition .=" AND a.`name` LIKE :name";
		$param[':name'] = "%{$keyword}%";
	}
	if(!empty($letter) && strlen($letter) == 1) {
		$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$letter = $_GPC['letter'];
		if(in_array($letter, $letters)){
			$condition .= " AND a.`letter` = :letter";
		}else {
			$condition .= " AND a.`letter` NOT IN ('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z')";
		}
		$param[':letter'] = $letter;
	}
	$tsql = "SELECT COUNT(*) FROM " . tablename('uni_account'). " as a LEFT JOIN". tablename('account'). " as b ON a.default_acid = b.acid {$condition} {$order_by}, a.`uniacid` DESC";
	$total = pdo_fetchcolumn($tsql, $param);
	$sql = "SELECT * FROM ". tablename('uni_account'). " as a LEFT JOIN". tablename('account'). " as b ON a.default_acid = b.acid  {$condition} {$order_by}, a.`uniacid` DESC LIMIT {$start}, {$psize}";
	$pager = pagination($total, $pindex, $psize);
	$list = pdo_fetchall($sql, $param);
	if(!empty($list)) {
		foreach($list as $unia => &$account) {
			$account['url'] = url('account/display/switch', array('uniacid' => $account['uniacid']));
			$account['details'] = uni_accounts($account['uniacid']);
			foreach ($account['details'] as  &$account_val) {
				$account_val['thumb'] = tomedia('headimg_'.$account_val['acid']. '.jpg').'?time='.time();
				$account_val['title_first_pinyin'] = get_first_char($account_val['name']);
			}
			$account['role'] = uni_permission($_W['uid'], $account['uniacid']);
			$account['setmeal'] = uni_setmeal($account['uniacid']);
		}
	}
}

template('account/display');