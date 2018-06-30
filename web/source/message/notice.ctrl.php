<?php
/**
 * 消息提醒功能
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'change_read_status', 'event_notice', 'all_read', 'set');
$do = in_array($do, $dos) ? $do : 'display';
load()->model('message');

$_W['page']['title'] = '系统管理 - 消息提醒 - 消息提醒';

if (in_array($do, array('display', 'all_read'))) {
	$type = $types = intval($_GPC['type']);
	if ($type == MESSAGE_ACCOUNT_EXPIRE_TYPE) {
		$types = array(MESSAGE_ACCOUNT_EXPIRE_TYPE, MESSAGE_WECHAT_EXPIRE_TYPE, MESSAGE_WEBAPP_EXPIRE_TYPE);
	}

	if (empty($type) && (!user_is_founder($_W['uid']) || user_is_vice_founder())){
		$types = array(MESSAGE_ACCOUNT_EXPIRE_TYPE, MESSAGE_WECHAT_EXPIRE_TYPE, MESSAGE_WEBAPP_EXPIRE_TYPE, MESSAGE_USER_EXPIRE_TYPE, MESSAGE_WXAPP_MODULE_UPGRADE);
	}
}

if ($do == 'display') {
	$message_id = intval($_GPC['message_id']);
	message_notice_read($message_id);

	$pindex = max(intval($_GPC['page']), 1);
	$psize = 10;

	$message_table = table('message');
	$is_read = !empty($_GPC['is_read']) ? intval($_GPC['is_read']) : '';

	if (!empty($is_read)) {
		$message_table->searchWithIsRead($is_read);
	}

	if (!empty($types)) {
		$message_table->searchWithType($types);
	}
	$message_table->searchWithPage($pindex, $psize);
	$lists = $message_table->messageList($type);

	$lists = message_list_detail($lists);

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
	if (!empty($message) && !empty($message['lists'])) {
		$set_property = message_property_type();
		$set = message_set_list();
		$propertys = $set['propertys'];
		$types = $set['types'];
		foreach ($message['lists'] as $k => $m) {
			if (empty($set_property[$m['type']])) {
				continue;
			}
			$property = $set_property[$m['type']];
			if (empty($propertys[$property]) || (!empty($propertys[$property]) && $propertys[$property]['status'] == 1)) {
				if (empty($types[$m['type']]) || (!empty($types[$m['type']]) && $types[$m['type']]['status'] == 1)) {
					continue;
				}
			}
			unset($message['lists'][$k]);
		}
	}
	sort($message['lists']);
	$message['total'] = count($message['lists']);
	$cookie_name = $_W['config']['cookie']['pre'] . '__notice';
	if (empty($_COOKIE[$cookie_name]) || $_COOKIE[$cookie_name] < TIMESTAMP) {
		message_account_expire();
		message_notice_worker();
		message_sms_expire_notice();
		message_user_expire_notice();
		message_wxapp_modules_version_upgrade();
	}
	iajax(0, $message);

}

if ($do == 'all_read') {
	message_notice_all_read($types);
	if ($_W['isajax']) {
		iajax(0, '全部已读', url('message/notice', array('type' => $type)));
	}
	itoast('', referer());
}

if ($do == 'set') {
	$set = message_property_type();
	$property = array('order', 'expire', 'work', 'register');
	$type = array(MESSAGE_ORDER_TYPE, MESSAGE_ORDER_PAY_TYPE, MESSAGE_ACCOUNT_EXPIRE_TYPE, MESSAGE_WECHAT_EXPIRE_TYPE, MESSAGE_WEBAPP_EXPIRE_TYPE, MESSAGE_USER_EXPIRE_TYPE, MESSAGE_WORKORDER_TYPE, MESSAGE_REGISTER_TYPE);
	if (!empty($_GPC['type'])) {
		if (!empty($_GPC['id'])) {
			$messageSet = pdo_get('message_notice_set', array('id' => intval($_GPC['id'])));
			if (empty($messageSet['status']) || $messageSet['status'] == 1) {
				$messageSet['status'] = 2;
			} else {
				$messageSet['status'] = 1;
			}
			pdo_update('message_notice_set', $messageSet, array('id' => $messageSet['id']));
		} else {
			if (in_array($_GPC['type'], $property)) {
				if (!pdo_get('message_notice_set', array('property' => trim($_GPC['type']), 'type' => 0))) {
					pdo_insert('message_notice_set', array('property' => trim($_GPC['type']), 'status' => 2, 'time' => TIMESTAMP));
				}
			} elseif (in_array($_GPC['type'], $type)) {
				if (!empty($set[$_GPC['type']]) && !pdo_get('message_notice_set', array('property' => $set[$_GPC['type']], 'type' => intval($_GPC['type'])))) {
					pdo_insert('message_notice_set', array('property' => $set[$_GPC['type']], 'type' => intval($_GPC['type']), 'status' => 2, 'time' => TIMESTAMP));
				}
			}
		}
		iajax(0, '更新成功', url('message/notice/set'));
	}
	$sets = message_set_list();
	$propertys = $sets['propertys'];
	$types = $sets['types'];
}
template('message/notice');