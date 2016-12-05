<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/15
 * Time: 14:14
 * 粉丝聊天功能
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('chats', 'send', 'endchats');
$dos = array('chats', 'send');
$do = in_array($do , $dos) ? $do : 'chats';

load()->model('mc');

if ($do == 'chats') {
	$_W['page']['title'] = '粉丝聊天';
	$openid = addslashes($_GPC['openid']);
	$fan_info = mc_fansinfo($openid);
	if (!empty($fan_info['uid'])) {
		$fan_info['member_info'] = mc_fetch($fan_info['uid']);
	}
	$chat_record = pdo_getslice('mc_chats_record', array('uniacid' => $_W['uniacid'], 'openid' => $openid), array('1', 20), $total, array(), '', 'createtime desc');
	if (!empty($chat_record)) {
		foreach ($chat_record as &$record) {
			$record['content'] = iunserializer($record['content']);
			$record['content'] = urldecode($record['content']['content']);
			$record['createtime'] = date('Y-m-d H:i', $record['createtime']);
		}
	}
}

if ($do == 'send') {
	$type = addslashes($_GPC['type']);
	$content = trim(htmlspecialchars_decode($_GPC['content']), '\"');
	$send['touser'] = trim($_GPC['openid']);
	$send['msgtype'] = $type;
	if ($type == 'text') {
		$send['text'] = array('content' => urlencode($content));
	} elseif ($type == 'image') {
		$send['image'] = array('media_id' => $content);
	} elseif ($type == 'voice') {
		$send['voice'] = array('media_id' => $content);
	} elseif($type == 'video') {
		$content = json_decode($content, true);
		$send['video'] = array(
			'media_id' => $content['mediaid'],
			'thumb_media_id' => '',
			'title' => urlencode($content['title']),
			'description' => ''
		);
	}  elseif($type == 'music') {
		$send['music'] = array(
			'musicurl' => tomedia($_GPC['musicurl']),
			'hqmusicurl' => tomedia($_GPC['hqmusicurl']),
			'title' => urlencode($_GPC['title']),
			'description' => urlencode($_GPC['description']),
			'thumb_media_id' => $_GPC['thumb_media_id'],
		);
	} elseif($type == 'news') {
		$content = json_decode($content, true);
		$send['msgtype'] =  'mpnews';
		$send['mpnews'] = array(
			'media_id' => $content['mediaid']
		);
	}
	$wechat_api = WeAccount::create($_W['acid']);
	$result = $wechat_api->sendCustomNotice($send);
	if (is_error($result)) {
		message($result, '', 'ajax');
	} else {
		//生成上下文
		$account = account_fetch($_W['acid']);
		$message['from'] = $_W['openid'] = $send['touser'];
		$message['to'] = $account['original'];
		if(!empty($message['to'])) {
			$sessionid = md5($message['from'] . $message['to'] . $_W['uniacid']);
			load()->classs('wesession');
			load()->classs('account');
			session_id($sessionid);
			WeSession::start($_W['uniacid'], $_W['openid'], 300);
			$processor = WeUtility::createModuleProcessor('chats');
			$processor->begin(300);
		}

		if($send['msgtype'] == 'mpnews') {
			$material = pdo_getcolumn('wechat_attachment', array('uniacid' => $_W['uniacid'], 'media_id' => $content['mediaid']), 'id');
			$content = urlencode('图文素材');
		}
		//保存消息记录
		pdo_insert('mc_chats_record',array(
			'uniacid' => $_W['uniacid'],
			'acid' => $acid,
			'flag' => 1,
			'openid' => $send['touser'],
			'msgtype' => $send['msgtype'],
			'content' => iserializer($send[$send['msgtype']]),
			'createtime' => TIMESTAMP,
		));
		message(error(0, array('createtime' => date('Y-m-d', time()), 'content' => $content)), '', 'ajax');
	}
}

if ($do == 'endchats') {
	$openid = trim($_GPC['openid']);
	$fans_info = mc_fansinfo($openid);
	$account = account_fetch($fans_info['acid']);
	$message['from'] = $_W['openid'] = $openid['openid'];
	$message['to'] = $account['original'];
	if(!empty($message['to'])) {
		$sessionid = md5($message['from'] . $message['to'] . $_W['uniacid']);
		load()->classs('wesession');
		load()->classs('account');
		session_id($sessionid);
		WeSession::start($_W['uniacid'], $_W['openid'], 300);
		$processor = WeUtility::createModuleProcessor('chats');
		$processor->end();
	}
	if (is_error($result)) {
		message($result, '', 'ajax');
	} else {

	}
}
template('mc/chats');