<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('receive', 'consume_code');
$do = in_array($do, $dos) ? $do: 'consume_code';

$_W['uniacid'] = intval($_GPC['i']);

if (empty($_W['uniacid'])) {
	iajax(1, '请先指定公众号');
}

if ($do == 'receive') {
	ignore_user_abort(true);
	set_time_limit(60);

	$modulename = $_GPC['modulename'];
	$request = json_decode(html_entity_decode($_GPC['request']), true);
	$response = json_decode(html_entity_decode($_GPC['response']), true);
	$message = json_decode(html_entity_decode($_GPC['message']), true);

	$module = module_fetch($modulename);
	sleep(10);
	WeUtility::logging('test', $modulename . '--' .  var_export($message, true));
	exit;
	if (!empty($module)) {
		$module_receiver = WeUtility::createModuleReceiver($modulename);
		$module_receiver->message = $message;
		$module_receiver->params = $request;
		$module_receiver->response = $response;
		$module_receiver->keyword = $request['keyword'];
		$module_receiver->module = $module;
		$module_receiver->uniacid = $_W['uniacid'];
		if(method_exists($obj, 'receive')) {
			@$obj->receive();
		}
	}
} else {
	$sql = 'SELECT * FROM ' . tablename('core_queue') . ' WHERE `uniacid`=:uniacid AND type = 2 ORDER BY `qid` ASC LIMIT 15';
	$pars = array();
	$pars[':uniacid'] = $_W['uniacid'];
	$cards = pdo_fetchall($sql, $pars);
	if(!empty($cards)) {
		load()->classs('coupon');
		foreach($cards as $li) {
			if(!empty($li['acid']) && !empty($li['message'])) {
				$acc = new coupon($li['acid']);
				$code = $acc->DecryptCode(array('encrypt_code' => $li['message']));
				if(is_error($code)) {
					continue;
				} else {
					$sumecode = $acc->ConsumeCode(array('code' => $code['code']));
					if(is_error($sumecode)) {
						continue;
					} else{
						pdo_delete('core_queue', array('uniacid' => $_W['uniacid'], 'id' => $li['id']));
						pdo_update('coupon_record', array('status' => 3), array('acid' => $li['acid'], 'code' => $code['code'], 'card_id' => $li['params']));
					}
				}
			}
		}
	}
}
if(!empty($_W['uniacid'])) {
	

	$sql = 'SELECT * FROM ' . tablename('core_queue') . ' WHERE `uniacid`=:uniacid AND type = 1 ORDER BY `qid` ASC LIMIT 50';
	$pars = array();
	$pars[':uniacid'] = $_W['uniacid'];
	$messages = pdo_fetchall($sql, $pars);
	
	if (!empty($messages)) {
		
		$qids = '';
		foreach($messages as &$message) {
			$message['message'] = iunserializer($message['message']);
			$message['params'] = iunserializer($message['params']);
			$message['response'] = iunserializer($message['response']);
			$message['keyword'] = iunserializer($message['keyword']);
		
			$qids .= $message['qid'] . ',';
		}
		
		$qids = trim($qids, ',');
		$sql = 'DELETE FROM ' . tablename('core_queue') . " WHERE `qid` IN ({$qids})";
		pdo_query($sql);
		
		load()->model('module');
		$modules = uni_modules();
		foreach($messages as $msg) {
			if(empty($msg['module'])) {
				continue;
			}
			$m = $modules[$msg['module']];
			if(!empty($m['subscribes']) && in_array($msg['message']['type'], $m['subscribes'])) {
				$obj = WeUtility::createModuleReceiver($m['name']);
				$obj->message = $msg['message'];
				$obj->params = $msg['params'];
				$obj->response = $msg['response'];
				$obj->keyword = $msg['keyword'];
				$obj->module = $m;
				$obj->uniacd = $msg['uniacid'];
				$obj->acid = $msg['acid'];
				if(method_exists($obj, 'receive')) {
					@$obj->receive();
				}
			}
		}
	}
}