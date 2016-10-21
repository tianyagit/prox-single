<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/home/welcome.ctrl.php : v 1c30921a7da3 : 2015/07/29 07:39:37 : yanghf $
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('platform');
$do = in_array($do, $dos) ? $do : 'platform';

define('FRAME', 'account');
$_W['page']['title'] = '公众号';

if (empty($_W['account']['endtime']) && !empty($_W['account']['endtime']) && $_W['account']['endtime'] < time()) {
	message('公众号已到服务期限，请续费', referer(), 'info');
}
$modules = uni_modules();
uni_update_week_stat();
$yesterday = date('Ymd', strtotime('-1 days'));
$yesterday_stat = pdo_get('stat_fans', array('date' => $yesterday, 'uniacid' => $_W['uniacid']));
$today_stat = pdo_get('stat_fans', array('date' => date('Ymd'), 'uniacid' => $_W['uniacid']));
//今日粉丝详情
$today_add_num = intval($today_stat['new']);
$today_cancel_num = intval($today_stat['cancel']);
$today_jing_num = $today_add_num - $today_cancel_num;
$today_total_num = intval($today_jing_num) + intval($yesterday_stat['cumulate']);
if($today_total_num < 0) {
	$today_total_num = 0;
}	
//启用的用户接口
load()->model('reply');
$cfg = $modules['userapi']['config'];
$ds = reply_search("`uniacid` = 0 AND module = 'userapi' AND `status`=1");
$apis = array();
foreach($ds as $row) {
	$apis[$row['id']] = $row; 
}

$ds = array();
foreach($apis as $row) {
	$reply = pdo_fetch('SELECT * FROM ' . tablename('userapi_reply') . ' WHERE `rid`=:rid', array(':rid' => $row['id']));
	$r = array();
	$r['title'] = $row['name'];
	$r['rid'] = $row['id'];
	$r['description'] = $reply['description'];
	$r['switch'] = $cfg[$r['rid']] ? true : false;
	$ds[] = $r;
}
$apis = $ds;

// 菜单权限
$accounts = uni_accounts();
$accounttypes = account_types();
// 特殊回复
$mtypes = array();
$mtypes['image'] = '图片消息';
$mtypes['voice'] = '语音消息';
$mtypes['video'] = '视频消息';
$mtypes['location'] = '位置消息';
$mtypes['link'] = '链接消息';
$mtypes['subscribe'] = '粉丝开始关注';

$setting = uni_setting($_W['uniacid'], array('default_message'));
$ds = array();
foreach($mtypes as $k => $v) {
	$row = array();
	$row['type'] = $k;
	$row['title'] = $v;
	$row['handles'] = array();
	foreach($modules as $m) {
		if(is_array($m['handles']) && in_array($k, $m['handles'])) {
			$row['handles'][] = array('name' => $m['name'], 'title' => $m['title']);
		}
	}
	$row['empty'] = empty($row['handles']);
	$row['current'] = is_array($setting['default_message']) ? $setting['default_message'][$k] : '';
	$ds[] = $row;
}
// 二维码
$qrs = pdo_fetchall("SELECT acid, COUNT(*) as num, model FROM ".tablename('qrcode')." WHERE uniacid=:uniacid GROUP BY acid, model", array(':uniacid'=>$_W['uniacid']));

$tyqr = array('qr1num'=>0,'qr2num'=>0);
foreach ($qrs as $qr) {
	$acid = intval($qr['acid']);
	if(intval($accounts[$acid]['level']) < 4){
		continue;
	}
	if(intval($qr['model']) == 1){
		$accounts[$acid]['qr1num'] = $qr['num'];
		$tyqr['qr1num'] += $qr['num'];
	} else {
		$accounts[$acid]['qr2num'] = $qr['num'];
		$tyqr['qr2num'] += $qr['num'];
	}
}
template('home/welcome');