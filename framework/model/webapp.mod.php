<?php

defined('IN_IA') or exit('Access Denied');



function webapp_save_last($uniacid) {
	isetcookie('__webappuniacid', $uniacid, 7 * 86400);
	isetcookie('__uniacid', $uniacid, 7 * 86400);
}

function webapp_last_uniacid() {
	global $_GPC;
	return isset($_GPC['__webappuniacid']) ? intval($_GPC['__webappuniacid']) : 0;
}

/**
 *  是否可操作指定pc
 * @param $uniacid

 */
function webapp_can_apply($uid, $uniacid) {
	if($uid == 0 || $uniacid == 0) {
		return false;
	}
	$account = uni_account_default($uniacid);
	if ($account['type'] != ACCOUNT_TYPE_WEBAPP_NORMAL) {
		return false;
	}
	if (user_is_founder($uid)) {
		return true;
	}
	$user = account_owner($uniacid);
	return isset($user['uid']) && $uid == $user['uid'];
}
/*
 * 获取可操作的uniacid
 */
function webapp_get_uniacid($uid = 0, $uniacid = 0) {
	if(!$uniacid) {
		$uniacid = webapp_last_uniacid();
	}
	if(!$uniacid) {
		return 0;
	}
	if(webapp_can_apply($uid, $uniacid)) {
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
function webapp_can_create($uid) {
	if(user_is_founder($uid)) { //创始人可以创建
		return true;
	}
	$data = permission_user_account_num($uid);
	return isset($data['webapp_limit']) && $data['webapp_limit'] > 0;
}