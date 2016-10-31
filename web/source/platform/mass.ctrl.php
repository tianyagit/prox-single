<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_exist('material_mass');
$_W['page']['title'] = '定时群发-微信素材';
$dos = array('list', 'post', 'cron', 'send', 'del');
$do = in_array($do, $dos) ? $do : 'list';

if($do == 'list') {
	set_time_limit(0);
	load()->model('cloud');
	$cloud = cloud_prepare();
	$cloud_error = 0;
	if(is_error($cloud)) {
		$cloud_error = 1;
	}
	$groups = pdo_fetch('SELECT * FROM ' . tablename('mc_fans_groups') . ' WHERE uniacid = :uniacid AND acid = :acid', array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
	$groups = iunserializer($groups['groups']);
	$time = strtotime(date('Y-m-d'));
	$record = pdo_fetchall('SELECT * FROM ' . tablename('mc_mass_record') . ' WHERE uniacid = :uniacid AND sendtime >= :time ORDER BY sendtime ASC LIMIT 7', array(':uniacid' => $_W['uniacid'], ':time' => $time), 'sendtime');
	for($i = 0; $i < 7; $i++) {
		$time_key = date('Y-m-d', strtotime("+{$i} days", $time));
		$mass_old[$time_key] = array(
			'msgtype' => 'news',
			'group' => -1,
			'time' => $time_key,
			'status' => 1,
			'clock' => '20:00',
			'media' => array(
				'items' => array(
					array(
						'title' => '请选择素材'
					)
				)
			),
		);
	}

	$mass_new = array();
	if(!empty($record)) {
		foreach($record as &$li) {
			$time_key = date('Y-m-d', $li['sendtime']);
			$li['time'] = $time_key;
			$li['clock'] = date('H:i', $li['sendtime']);
			$li['media'] = pdo_get('wechat_attachment', array('id' => $li['attach_id']));
			$li['media']['attach'] = tomedia($li['media']['attachment']);
			if($li['msgtype'] == 'video') {
				$li['media']['attach']['tag'] = iunserializer($li['media']['tag']);
			} elseif($li['msgtype'] == 'news') {
				$li['media']['items'] = pdo_getall('wechat_news', array('attach_id' => $li['attach_id']));
				foreach($li['media']['items'] as &$row) {
					$row['thumb_url'] = url('utility/wxcode/image', array('attach' => $row['thumb_url']));
				}
			} elseif($li['msgtype'] == 'wxcard') {
				$li['media'] = pdo_get('coupon', array('id' => $li['attach_id']));
				$li['media']['media_id'] = $li['media']['card_id'];
				$li['media']['logo_url'] = url('utility/wxcode/image', array('attach' => $li['media']['logo_url']));
				$li['media']['type'] = 'wxcard';
			}

			$li['media']['createtime_cn'] = date('Y-m-d H:i', $li['media']['createtime']);
			$li['media_id'] = $li['media']['media_id'];
			$mass_new[$time_key] = $li;
		}
		unset($record);
	}
	$mass = array_values((array_merge($mass_old, $mass_new)));
	template('platform/mass-display');
}

if($do == 'del') {
	load()->func('cron');
	load()->model('cloud');
	$post = $_GPC['__input'];
	$mass = pdo_get('mc_mass_record', array('uniacid' => $_W['uniacid'], 'id' => intval($post['id'])));
	if(!empty($mass) && $mass['cron_id'] > 0) {
		$status = cron_delete(array($mass['cron_id']));
		if(is_error($status)) {
			message($status, '', 'ajax');
		}
	}
	pdo_delete('mc_mass_record', array('uniacid' => $_W['uniacid'], 'id' => intval($post['id'])));
	message(error(0, ''), '', 'ajax');
}

if($do == 'post') {
	load()->model('module');

	$groups = pdo_fetch('SELECT * FROM ' . tablename('mc_fans_groups') . ' WHERE uniacid = :uniacid AND acid = :acid', array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
	$groups = iunserializer($groups['groups']);
	array_unshift($groups, array('id' => -1, 'name' => '全部粉丝', 'count' => ''));

	$time = strtotime(date('Y-m-d'));
	$starttime = strtotime("+{$_GPC['day']} days", $time);
	$endtime = $_GPC['day']+1;
	$endtime = strtotime("+{$endtime} days", $time);
	$massdata = pdo_fetch('SELECT id, `groupname`, `group`, `attach_id`, `media_id`, `sendtime` FROM '. tablename('mc_mass_record') . ' WHERE uniacid = :uniacid AND sendtime BETWEEN :starttime AND :endtime AND status = 1', array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime));
	$massdata['clock'] = date('H:m', $massdata['sendtime']);		

	if(checksubmit('submit')) {
		load()->func('cron');
		load()->model('cloud');
		$cloud = cloud_prepare();
		if(is_error($cloud)) {
			message($cloud, '', 'ajax');
		}

		//删除提交日的群发任务
		$starttime = strtotime("+{$_GPC['day']} days", $time);
		$endtime = $_GPC['day']+1;
		$endtime = strtotime("+{$endtime} days", $time);
		set_time_limit(0);
		$records = pdo_fetchall('SELECT id, cron_id FROM ' . tablename('mc_mass_record') . ' WHERE uniacid = :uniacid AND sendtime BETWEEN :starttime AND :endtime AND status = 1 ORDER BY sendtime ASC LIMIT 8', array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime), 'id');
		if(!empty($records)) {
			foreach($records as $record) {
				if(!$record['cron_id']) {
					continue;
				}
				$corn_ids[] = $record['cron_id'];
			}
			if(!empty($corn_ids)) {
				$status = cron_delete($corn_ids);
				if(is_error($status)) {
					message('删除群发错误,请重新提交', referer());
				}
			}
			$ids = implode(',', array_keys($records));
			pdo_query('DELETE FROM ' . tablename('mc_mass_record') . " WHERE uniacid = :uniacid AND id IN ({$ids})", array(':uniacid' => $_W['uniacid']));
		}

		//提交数据
		$group = json_decode(htmlspecialchars_decode($_GPC['group']), true);
		$mass = array();
		foreach ($_GPC['reply'] as $reply_k => $reply_val) {
			if($reply_val) {
				$msgtype = substr($reply_k, 6);
				switch($msgtype) {
					case 'news':
					case 'video':
						$mass = json_decode(htmlspecialchars_decode($reply_val), true);
						break;
					case 'image':
					case 'voice':
						$mass['mediaid'] = json_decode(htmlspecialchars_decode($reply_val), true);
						break;
				}
				$attachment = pdo_get('wechat_attachment', array('media_id' => $mass['mediaid']), array('id'));
				$mass['id'] = $attachment['id'];
				$mass['msgtype'] = $msgtype;
				break;
			}
		}
		$time_key = date('Y-m-d', strtotime("+{$_GPC['day']} days", $time));
		$mass['sendtime'] = strtotime($time_key . " {$_GPC['clock']}");
		$cron_status = 0;
		$mass_record_data = array(
			'uniacid' => $_W['uniacid'],
			'acid' => $_W['acid'],
			'groupname' => $group['name'],
			'group' => $group['id'],
			'attach_id' => $mass['id'],
			'media_id' => $mass['mediaid'],
			'fansnum' => $group['count'],
			'msgtype' => $mass['msgtype'],
			'sendtime' => $mass['sendtime'],
			'createtime' => TIMESTAMP,
			'type' => 1,
			'status' => 1,
			'cron_id' => 0,
		);
		pdo_insert('mc_mass_record', $mass_record_data);
		$insert_id = pdo_insertid();
		$cron_data = array(
			'uniacid' => $_W['uniacid'],
			'name' => $time_key . "微信群发任务",
			'filename' => 'mass',
			'type' => 1,
			'lastruntime' => $mass['sendtime'],
			'extra' => $insert_id,
			'module' => 'task',
			'status' => 1,
		);
		$status = cron_add($cron_data);
		if(is_error($status)) {
			$message = "{$time_key}的群发任务同步到云服务失败,请手动同步<br>";
			$cron_status = 1;
		} else {
			pdo_update('mc_mass_record', array('cron_id' => $status), array('id' => $insert_id));
		}
		if($cron_status) {
			message($message, url('platform/mass/send'));
		}
		message('群发设置成功', url('platform/mass'));
		exit;
	}

	template('platform/mass-post');
}

if($do == 'cron') {
	$id = intval($_GPC['id']);
	$record = pdo_get('mc_mass_record', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if(empty($record)) {
		message('群发任务不存在或已删除', referer(), 'error');
	}
	load()->func('cron');
	$cron = array(
		'uniacid' => $_W['uniacid'],
		'name' => date('Y-m-d', $record['sendtime']) . "微信群发任务",
		'filename' => 'mass',
		'type' => 1,
		'lastruntime' => $record['sendtime'],
		'extra' => $record['id'],
		'module' => 'task',
		'status' => 1
	);
	$status = cron_add($cron);
	if(is_error($status)) {
		message($status['message'], referer(), 'error');
	}
	pdo_update('mc_mass_record', array('cron_id' => $status), array('uniacid' => $_W['uniacid'], 'id' => $id));
	message('同步到云服务成功', referer(), 'success');
}

if($do == 'send') {
	$_W['page']['title'] = '定时群发记录-定时群发';
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' WHERE `uniacid` = :uniacid AND `acid` = :acid';
	$params = array();
	$params[':uniacid'] = $_W['uniacid'];
	$params[':acid'] = $_W['acid'];
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('mc_mass_record').$condition, $params);
	$lists = pdo_fetchall("SELECT * FROM ".tablename('mc_mass_record') . $condition ." ORDER BY `id` DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
	$types = array('text' => '文本消息', 'image' => '图片消息', 'voice' => '语音消息', 'video' => '视频消息', 'news' => '图文消息', 'wxcard' => '微信卡券');
	$pager = pagination($total, $pindex, $psize);
	template('platform/mass-send');
}