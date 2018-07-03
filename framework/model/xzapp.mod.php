<?php
defined('IN_IA') or exit('Access Denied');

/**
 * 创建熊掌号
 * @param $uniacid
 * @param $account
 * @return int
 */
function xzapp_create($uniacid, $account) {
	global $_W;
	$accountdata = array('uniacid' => $uniacid, 'type' => $account['type'], 'hash' => random(8));
	$user_create_account_info = permission_user_account_num();
	if (empty($_W['isfounder']) && empty($user_create_account_info['usergroup_account_limit'])) {
		$accountdata['endtime'] = strtotime('+1 month', time());
		pdo_insert('site_store_create_account', array('endtime' => strtotime('+1 month', time()), 'uid' => $_W['uid'], 'uniacid' => $uniacid, 'type' => ACCOUNT_TYPE_XZAPP_NORMAL));
	}
	pdo_insert('account', $accountdata);
	$acid = pdo_insertid();
	$account['acid'] = $acid;
	$account['token'] = random(32);
	$account['encodingaeskey'] = random(43);
	$account['uniacid'] = $uniacid;
	unset($account['type']);
	pdo_insert('account_xzapp', $account);
	return $acid;
}