<?php
/**
 * 消息提醒功能
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'change_read_status', 'event_notice', 'account_expire', 'notice_worker');
$do = in_array($do, $dos) ? $do : 'display';
load()->model('message');

$_W['page']['title'] = '系统管理 - 消息提醒 - 消息提醒';

if ($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;

	/* xstart */
	if (IMS_FAMILY == 'x') {
		$types = $type = !empty($_GPC['type']) ? intval($_GPC['type']) :
				(user_is_founder($_W['uid']) && !user_is_vice_founder() ? MESSAGE_ORDER_TYPE : MESSAGE_ACCOUNT_EXPIRE_TYPE);
	}
	/* xend */

	/* vstart */
	if (IMS_FAMILY == 'v') {
		$types = $type = !empty($_GPC['type']) ? intval($_GPC['type']) : MESSAGE_ACCOUNT_EXPIRE_TYPE;
	}
	/* vend */

	if ($type == MESSAGE_ACCOUNT_EXPIRE_TYPE) {
		$types = array(MESSAGE_ACCOUNT_EXPIRE_TYPE, MESSAGE_WECHAT_EXPIRE_TYPE);
	}
	$is_read = !empty($_GPC['is_read']) ? trim($_GPC['is_read']) : '';

	$message_table = table('message');

	if (!empty($is_read)) {
		$message_table->searchWithIsRead($is_read);
	}

	$message_table->searchWithType($types);
	$message_table->searchWithPage($pindex, $psize);
	$lists = $message_table->messageList();

	if (!empty($lists)) {
		foreach($lists as &$list) {
			$list['create_time'] = date('Y-m-d H:i:s', $list['create_time']);
		}
	}
	$total = $message_table->getLastQueryTotal();
	$pager = pagination($total, $pindex, $psize);
}

if ($do == 'change_read_status') {
	$id = $_GPC['id'];
	message_notice_read($id);
	iajax(0, '成功');
}

if ($do == 'event_notice') {
	if (!pdo_tableexists('message_notice_log')) {
		iajax(-1);
	}
	$message = message_event_notice_list();
	iajax(0, $message);

}

if ($do == 'account_expire') {
	message_account_expire();
}

if ($do == 'notice_worker') {
	message_notice_worker();
}
template('message/notice');