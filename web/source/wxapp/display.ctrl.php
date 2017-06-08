<?php
/**
 * 小程序列表
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('wxapp');
load()->model('account');

$_W['page']['title'] = '小程序列表';

$dos = array('display', 'switch', 'rank' , 'home', 'version_page');
$do = in_array($do, $dos) ? $do : 'display';

if ($do == 'rank' || $do == 'switch') {
	$uniacid = intval($_GPC['uniacid']);
	if (!empty($uniacid)) {
		$wxapp_info = wxapp_fetch($uniacid);
		if (empty($wxapp_info)) {
			itoast('小程序不存在', referer(), 'error');
		}
	}
}

if ($do == 'home') {
	$last_uniacid = uni_account_last_switch();
	if (empty($last_uniacid)) {
		itoast('', url('wxapp/display'), 'info');
	} else {
		$last_version = wxapp_fetch($last_uniacid);
		if (!empty($last_version)) {
			uni_account_switch($last_uniacid);
			header('Location: ' . url('wxapp/version/manage', array('version_id' => $last_version['version']['id'])));
			exit;
		} else {
			itoast('', url('wxapp/display'), 'info');
		}
	}
} elseif ($do == 'display') {
	//模版调用，显示该用户所在用户组可添加的主公号数量，已添加的数量，还可以添加的数量
	$account_info = uni_user_account_permission();
	
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$start = ($pindex - 1) * $psize;
	
	$condition = '';
	$param = array();
	$keyword = trim($_GPC['keyword']);
	if (!empty($_W['isfounder'])) {
		$condition .= " WHERE a.default_acid <> 0 AND b.isdeleted <> 1 AND b.type = " . ACCOUNT_TYPE_APP_NORMAL;
		$order_by = " ORDER BY a.`rank` DESC";
	} else {
		$condition .= "LEFT JOIN ". tablename('uni_account_users')." as c ON a.uniacid = c.uniacid WHERE a.default_acid <> 0 AND c.uid = :uid AND b.isdeleted <> 1 AND b.type = " . ACCOUNT_TYPE_APP_NORMAL;
		$param[':uid'] = $_W['uid'];
		$order_by = " ORDER BY c.`rank` DESC";
	}

	if (!empty($keyword)) {
		$condition .=" AND a.`name` LIKE :name";
		$param[':name'] = "%{$keyword}%";
	}
	if (isset($_GPC['letter']) && strlen($_GPC['letter']) == 1) {
		$letter = trim($_GPC['letter']);
		if (!empty($letter)) {
			$condition .= " AND a.`title_initial` = :title_initial";
			$param[':title_initial'] = $letter;
		} else {
			$condition .= " AND a.`title_initial` = ''";
		}
	}
	$tsql = "SELECT COUNT(*) FROM " . tablename('uni_account'). " as a LEFT JOIN ". tablename('account'). " as b ON a.default_acid = b.acid {$condition} {$order_by}, a.`uniacid` DESC";
	$sql = "SELECT * FROM ". tablename('uni_account'). " as a LEFT JOIN ". tablename('account'). " as b ON a.default_acid = b.acid  {$condition} {$order_by}, a.`uniacid` DESC LIMIT {$start}, {$psize}";
	$total = pdo_fetchcolumn($tsql, $param);
	$wxapp_lists = pdo_fetchall($sql, $param);
	if (!empty($wxapp_lists)) {
		foreach ($wxapp_lists as &$account) {
			$account['thumb'] = tomedia('headimg_'.$account['acid']. '.jpg').'?time='.time();
			$account['versions'] = wxapp_version_page($account['uniacid'], 1);
			$account['current_version'] = array();
			if (!empty($account['versions'])) {
				foreach ($account['versions'] as $version) {
					if ($version['last_use'] == 1) {
						$account['current_version'] = $version;
						break;
					}
				}
				if (empty($account['current_version'])) {
					$account['current_version'] = $account['versions'][0];
				}
			}
		}
		unset($account_val);
		unset($account);
	}
	$pager = pagination($total, $pindex, $psize);
	template('wxapp/account-display');
} elseif ($do == 'switch') {
	$module_name = trim($_GPC['module']);
	$version_id = !empty($_GPC['version_id']) ?intval($_GPC['version_id']) : $wxapp_info['version']['id'];
	
	if (!empty($module_name) && !empty($version_id)) {
		$version_info = wxapp_version($version_id);
		if (empty($version_id) || empty($version_info['modules'][$module_name])) {
			itoast('版本信息错误');
		}
		$uniacid = !empty($version_info['modules'][$module_name]['account']['uniacid']) ? $version_info['modules'][$module_name]['account']['uniacid'] : $version_info['uniacid'];
		uni_account_switch($uniacid, url('home/welcome/ext/', array('m' => $module_name)));
	}
	uni_account_switch($uniacid);
	wxapp_save_switch($uniacid);
	wxapp_update_last_use_version($version_id);
	header('Location: ' . url('wxapp/version/manage', array('version_id' => $version_id)));
	exit;
} elseif ($do == 'rank') {
	uni_account_rank_top($uniacid);
	itoast('更新成功', '', '');
} elseif ($do == 'version_page') {
	$uniacid = intval($_GPC['uniacid']);
	if (empty($uniacid)) {
		iajax(-1, '参数错误！');
	}
	$page =max(1, intval($_GPC['page']));
	$version_page = wxapp_version_page($uniacid, $page);
	iajax(0, $version_page);
}