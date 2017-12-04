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
//	$message_notice_log = array_merge($message, $extend_message);
	pdo_insert('message_notice_log', $message);
	message_notify($type, $content, $uid, $sign, $extend_message);
	return true;
}

/**
 * frame  顶部消息提醒获取
 * @return array
 */
function message_notice() {
	global $_W;
	$message_table = table('message');
	$message_table->searchWithIsRead(MESSAGE_NOREAD);
	if (user_is_founder($_W['uid']) && !user_is_vice_founder($_W['uid'])) {
		$type = array(MESSAGE_ORDER_TYPE, MESSAGE_ACCOUNT_EXPIRE_TYPE, MESSAGE_REGISTER_TYPE, MESSAGE_WECHAT_EXPIRE_TYPE);
	} else {
		$type = MESSAGE_ACCOUNT_EXPIRE_TYPE;
	}
	$message_table->searchWithType($type);
	$message_table->searchWithPage(1, 10);
	$lists = $message_table->messageList();

	$message_table->searchWithIsRead(MESSAGE_NOREAD);
	$message_table->searchWithType($type);
	$total = $message_table->messageNoReadCount();
	return array(
		'lists' => $lists,
		'total' => $total
	);
}

function message_notify_data($type, $message, $uid, $sign, $ext = array()) {
	$data = array();
	$data['time'] = TIMESTAMP;
	$data['remark'] = '进入系统查看';
	switch ($type) {
		case MESSAGE_REGISTER_TYPE :
			$user = user_single($uid);
			$data['first'] = $message;
			$data['keyword1'] = $user['username'];
			$data['notify_type'] = MESSAGE_REGISTER_TYPE;
			break;
		case MESSAGE_ORDER_TYPE : //创建订单
			$data['first'] = $message;
			$data['keyword1'] = $sign;
			$data['keyword2'] = 1;
			$data['keyword3'] = $ext['amount'];
			$data['notify_type'] = MESSAGE_ORDER_TYPE;
			$data['remark'] = $ext['product'];
			break;
	}
	return $data;
}

/**
 *  消息通知
 * @param $type
 * @param $message
 * @param $uid
 * @param $sign
 * @param $ext
 * @return array|mixed|string
 */
function message_notify($type, $message, $uid, $sign, $ext = array()) {
	load()->classs('cloudapi');
	$data = message_notify_data($type, $message, $uid, $sign, $ext);
	$api = new CloudApi();
	$result = $api->post('system', 'notify', array('json'=>$data), 'html', false);
	return $result;
}

function message_load_workorder_notice_url($time) {
	load()->classs('cloudapi');
	$api = new CloudApi();
	$result = $api->get('system', 'workorder', array('do'=>'notload', 'time'=>$time), 'json', false);
	return $result;
}

function message_load_in_notice($uid) {
	global $_W;
	load()->classs('query');
	$query = new Query();
	$message_log = $query->from('message_notice_log')->where('type', MESSAGE_WORKORDER_TYPE)
		->orderby('create_time', 'desc')->get();
	$time = 0;//strtotime('1970-01-01');
	if($message_log && isset($message_log['create_time'])) {
		$time = $message_log['create_time'];
	}
	$data_url =  message_load_workorder_notice_url($time);
	if (is_error($data_url)) {
		return;
	}

	$url = $data_url['data']['url'];//'http://127.0.0.1:8000/api/workorder/notesfromwe7?id=1&site_id=1';
	$response = ihttp_get($url);

	if($response['code'] == 200) {
		$content = $response['content'];
		$data = json_decode($content, JSON_OBJECT_AS_ARRAY);
		//暂不做批量插入处理
		foreach ($data['list'] as $item) {
			pdo_insert('message_notice_log', array(
				'message'=>$item['note'],
				'create_time'=>strtotime($item['updated_at']),
				'uid'=>$uid, 'sign'=>$item['uuid'],
				'is_read'=>1,
				'type'=>MESSAGE_WORKORDER_TYPE));
		}
	}
}

