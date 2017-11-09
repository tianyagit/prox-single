<?php

defined('IN_IA') or exit('Access Denied');



function pc_save_last($uniacid) {
	isetcookie('__pcuniacid', $uniacid, 7 * 86400);
	isetcookie('__uniacid', $uniacid, 7 * 86400);
}

function pc_last_uniacid() {
	global $_GPC;
	return isset($_GPC['__pcuniacid']) ? intval($_GPC['__pcuniacid']) : 0;
}

/**
 *  是否可操作指定pc
 * @param $uniacid

 */
function pc_can_apply($uid, $uniacid) {
	if($uid == 0 || $uniacid == 0) {
		return false;
	}
	$account = uni_account_default($uniacid);
	if($account['type'] != ACCOUNT_TYPE_PC_NORMAL) {
		return false;
	}
	if(user_is_founder($uid)) {
		return true;
	}
	$accounts = uni_user_accounts($uniacid);
	return is_array($accounts) && isset($account[$uniacid]);
}
/*
 * 获取可操作的uniacid
 */
function pc_get_pc_uniacid($uid = 0, $uniacid = 0) {
	if(!$uniacid) {
		$uniacid = pc_last_uniacid();
	}
	if(!$uniacid) {
		return 0;
	}
	if(pc_can_apply($uid, $uniacid)) {
		return $uniacid;
	}
	return 0;

}

/**
 *  是否可创建PC
 * @param $uid
 *
 *
 * @since version
 */
function pc_can_create($uid) {
	if(user_is_founder($uid)) { //创始人可以创建
		return true;
	}
	$data = permission_user_account_num($uid);
	return isset($data['pc_limit']) && $data['pc_limit'] > 0;
}