<?php
defined('IN_IA') or exit('Access Denied');

/**
 * 是否可创建熊掌号
 * $param $uid
 * $return bool
 */
function xiongzhangapp_can_create($uid) {
	if (user_is_founder($uid)) {
		return true;
	}
	$data = permission_user_account_num($uid);
	return isset($data['xiongzhangapp_limit']) && $data['xiongzhangapp_limit'] > 0;
}