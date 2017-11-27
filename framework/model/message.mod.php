<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 更改消息提醒状态
 * @param $id
 * @return bool
 */
function message_notice_read($id) {
	$id = intval($id);
	if (empty($id)) {
		return true;
	}
	pdo_update('message_notice_log', array('is_read' => MESSAGE_READ), array('id' => $id));
	return true;
}


/**
 * 消息提醒记录
 * @param array $message_notice_log
 * @return bool
 */
function message_record($content, $uid, $sign, $type, $extend_message = array()) {
	$message['message'] = $content;
	$message['uid'] = $uid;
	$message['sign'] = $sign;
	$message['type'] = $type;
	$message['create_time'] = TIMESTAMP;
	$message_notice_log = array_merge($message, $extend_message);
	pdo_insert('message_notice_log', $message_notice_log);
	return true;
}