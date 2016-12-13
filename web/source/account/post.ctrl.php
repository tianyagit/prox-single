<?php
/**
 * 管理公众号
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');

$dos = array('base', 'sms', 'users', 'modules_tpl');
$do = in_array($do, $dos) ? $do : 'base';

$uniacid = intval($_GPC['uniacid']);
$acid = intval($_GPC['acid']);
$_W['page']['title'] = '管理设置 - 微信公众号管理';
if (empty($uniacid) || empty($acid)) {
	message('请选择要编辑的公众号', referer(), 'error');
}
$state = uni_permission($_W['uid'], $uniacid);
if($state != 'founder' && $state != 'manager') {
	message('没有该公众号操作权限！', referer(), 'error');
}
$headimgsrc = tomedia('headimg_'.$acid.'.jpg');
$qrcodeimgsrc = tomedia('qrcode_'.$acid.'.jpg');
$account = account_fetch($acid);

if($do == 'base') {
	if($_W['ispost'] && $_W['isajax']) {
		if(!empty($_GPC['type'])) {
			$type = trim($_GPC['type']);
		}else {
			message('40035', 'ajax', 'success');
		}
		switch ($type) {
			case 'qrcodeimgsrc':
				if(!empty($_GPC['imgsrc'])) {
					if(file_exists($qrcodeimgsrc)) {
						unlink($qrcodeimgsrc);
						$result = copy($_GPC['imgsrc'], IA_ROOT . '/attachment/qrcode_'.$acid.'.jpg');
					}else {
						$result = copy($_GPC['imgsrc'], IA_ROOT . '/attachment/qrcode_'.$acid.'.jpg');
					}
				}
				break;
			case 'headimgsrc':
				if(!empty($_GPC['imgsrc'])) {
					if(file_exists($headimgsrc)) {
						unlink($headimgsrc);
						$result = copy($_GPC['imgsrc'], IA_ROOT . '/attachment/headimg_'.$acid.'.jpg');
					}else {
						$result = copy($_GPC['imgsrc'], IA_ROOT . '/attachment/headimg_'.$acid.'.jpg');
					}
				}
				break;
			case 'name':
				$uni_account = pdo_update('uni_account', array('name' => trim($_GPC['request_data'])), array('uniacid' => $uniacid));
				$account_wechats = pdo_update('account_wechats', array('name' => trim($_GPC['request_data'])), array('acid' => $acid, 'uniacid' => $uniacid));
				$result = ($uni_account && $account_wechats) ? true : false;
				break;
			case 'account' :
				$result = pdo_update('account_wechats', array('account' => trim($_GPC['request_data'])), array('acid' => $acid, 'uniacid' => $uniacid));
				break;
			case 'original':
				$result = pdo_update('account_wechats', array('original' => trim($_GPC['request_data'])), array('acid' => $acid, 'uniacid' => $uniacid));
				break;
			case 'level':
				$result = pdo_update('account_wechats', array('level' => intval($_GPC['request_data'])), array('acid' => $acid, 'uniacid' => $uniacid));
				break;
			case 'endtime' :
				if(intval($_GPC['endtype']) == 1) {
					$endtime = 0;
				}else {
					$endtime = strtotime($_GPC['endtime']);
				}
				$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $uniacid));
				if(empty($owneruid)) message('-1', 'ajax', 'error');
				$result = pdo_update('users', array('endtime' => $endtime), array('uid' => $owneruid));
				break;
			case 'key':
				$result = pdo_update('account_wechats', array('key' => trim($_GPC['request_data'])), array('acid' => $acid, 'uniacid' => $uniacid));
				break;
			case 'secret':
				$result = pdo_update('account_wechats', array('secret' => trim($_GPC['request_data'])), array('acid' => $acid, 'uniacid' => $uniacid));
				break;
			case 'token':
				$oauth = (array)uni_setting($uniacid, array('oauth'));
				if($oauth['oauth'] == $acid && $account['level'] != 4) {
					$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . " WHERE uniacid = :uniacid AND level = 4 AND secret != '' AND `key` != ''", array(':uniacid' => $uniacid));
					pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
				}
				$result = pdo_update('account_wechats', array('token' => trim($_GPC['request_data'])), array('acid' => $acid, 'uniacid' => $uniacid));
				break;
			case 'encodingaeskey':
				$oauth = (array)uni_setting($uniacid, array('oauth'));
				if($oauth['oauth'] == $acid && $account['level'] != 4) {
					$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . " WHERE uniacid = :uniacid AND level = 4 AND secret != '' AND `key` != ''", array(':uniacid' => $uniacid));
					pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
				}
				$result = pdo_update('account_wechats', array('encodingaeskey' => trim($_GPC['request_data'])), array('acid' => $acid, 'uniacid' => $uniacid));
				break;
		}
		if($result) {
			cache_delete("uniaccount:{$uniacid}");
			cache_delete("unisetting:{$uniacid}");
			cache_delete("accesstoken:{$acid}");
			cache_delete("jsticket:{$acid}");
			cache_delete("cardticket:{$acid}");
			module_build_privileges();
			message('0', 'ajax', 'success');
		}else {
			message('1', 'ajax', 'error');
		}
	}
	
	$account['end'] = $account['endtime'] == 0 ? '永久' : date('Y-m-d', $account['endtime']);
	$account['endtype'] = $account['endtime'] == 0 ? 1 : 2;
	$uniaccount = array();
	$uniaccount = pdo_fetch("SELECT * FROM ".tablename('uni_account')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
	
	template('account/manage-base');
}

if($do == 'sms') {
	template('account/manage-sms');
}

if($do == 'users') {
	template('account/manage-users');
}

if($do == 'modules_tpl') {
	template('account/manage-modules-tpl');
}