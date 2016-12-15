<?php
/**
 * 管理公众号
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('cloud');

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
	$settings = uni_setting($uniacid, array('notify'));
	$notify = $settings['notify'] ? $settings['notify'] : array();

	$sms_info = cloud_sms_info();
	$max_num = empty($sms_info['sms_count']) ? 0 : $sms_info['sms_count'];
	$signatures = $sms_info['sms_sign'];

	if ($_W['isajax'] && $_W['ispost'] && $_GPC['type'] == 'balance') {
		if ($max_num == 0) {
			message(error(-1), '', 'ajax');
		}
		$balance = intval($_GPC['balance']);
		$notify['sms']['balance'] = $balance;
		$notify['sms']['balance'] = min(max(0, $notify['sms']['balance']), $max_num);
		$count_num = $max_num - $notify['sms']['balance'];
		$num = $notify['sms']['balance'];
		$notify = iserializer($notify);
		$updatedata['notify'] = $notify;
		$result = pdo_update('uni_settings', $updatedata , array('uniacid' => $uniacid));
		if($result){
			message(error(0, array('count' => $count_num, 'num' => $num)), '', 'ajax');
		}else {
			message(error(1), '', 'ajax');
		}
	}
	if($_W['isajax'] && $_W['ispost'] && $_GPC['type'] == 'signature') {
		if (!empty($_GPC['signature'])) {
			$signature = trim($_GPC['signature']);
			$setting = pdo_get('uni_settings', array('uniacid' => $uniacid));
			$notify = iunserializer($setting['notify']);
			$notify['sms']['signature'] = $signature;

			$notify = serialize($notify);
			$result = pdo_update('uni_settings', array('notify' => $notify), array('uniacid' => $uniacid));
			if($result) {
				message(error(0), '', 'ajax');
			}else {
				message(error(1), '', 'ajax');
			}
		}else {
			message(error(40035), '', 'ajax');
		}
	}

	template('account/manage-sms');
}

if($do == 'users') {
	template('account/manage-users');
}

if($do == 'modules_tpl') {
	$unigroups = uni_groups();
	$ownerid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $uniacid));
	$owner = user_single(array('uid' => $ownerid));
	if($_W['isajax'] && $_W['ispost']) {
		if($_GPC['type'] == 'group') {
			if(!empty(trim($_GPC['groupdata']))) {
				$groups = explode(',', trim($_GPC['groupdata']));
				//附加套餐组
				pdo_delete('uni_account_group', array('uniacid' => $uniacid));
				$group = pdo_get('users_group', array('id' => $owner['groupid']));
				$group['package'] = iunserializer($group['package']);
				$group['package'] = array_unique($group['package']);
				foreach ($groups as $packageid) {
					if (!empty($packageid) && !in_array($packageid, $group['package'])) {
						pdo_insert('uni_account_group', array(
							'uniacid' => $uniacid,
							'groupid' => $packageid,
						));
					}
				}
				message(error(0), '', 'ajax');
			}else {
				message(error(40035), '', 'ajax');
			}
		}

		if($_GPC['type'] == 'extend') {
			//如果有附加的权限，则生成专属套餐组
			
			$module = trim($_GPC['module']);
			$tpl = trim($_GPC['tpl']);
			if (!empty($module) || !empty($tpl)) {
				$module = explode(',', $module);
				$tpl = explode(',', $tpl);
				$data = array(
					'modules' => iserializer($module),
					'templates' => iserializer($tpl),
					'uniacid' => $uniacid,
					'name' => '',
				);
				$id = pdo_fetchcolumn("SELECT id FROM ".tablename('uni_group')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
				if (empty($id)) {
					pdo_insert('uni_group', $data);
				} else {
					pdo_update('uni_group', $data, array('id' => $id));
				}
			} else {
				pdo_delete('uni_group', array('uniacid' => $uniacid));
			}
			message(error(0), '', 'ajax');
		}
		message(error(40035), '', 'ajax');
	}
	$modules_tpl = $extend = array();
	if (!empty($ownerid)) {
		$owner['group'] = pdo_fetch("SELECT id, name, package FROM ".tablename('users_group')." WHERE id = :id", array(':id' => $owner['groupid']));
		$owner['group']['package'] = iunserializer($owner['group']['package']);
		if(!empty($owner['group']['package'])){
			foreach ($owner['group']['package'] as $package_value) {
				if($package_value == -1){
					$modules_tpl[] = array(
							'id' => -1,
							'name' => '所有服务',
							'modules' => array(array('name' => 'all', 'title' => '所有模块')),
							'templates' => array(array('name' => 'all', 'title' => '所有模板')),
						);
				}elseif ($package_value == 0) {
					
				}else {
					$modules_tpl[] = $unigroups[$package_value];
				}
			}
		}
		//附加套餐
		$extendpackage = pdo_getall('uni_account_group', array('uniacid' => $uniacid), array(), 'groupid');
		if(!empty($extendpackage)) {
			foreach ($extendpackage as $extendpackage_val) {
				if($extendpackage_val['groupid'] == -1){
					$modules_tpl[] = array(
							'id' => -1,
							'name' => '所有服务',
							'modules' => array(array('name' => 'all', 'title' => '所有模块')),
							'templates' => array(array('name' => 'all', 'title' => '所有模板')),
						);
				}elseif ($extendpackage_val['groupid'] == 0) {
					
				}else {
					$modules_tpl[] = $unigroups[$extendpackage_val['groupid']];
				}
			}
		}
		//附加权限
		$modules = pdo_fetchall("SELECT mid, name, title FROM " . tablename('modules') . ' WHERE issystem != 1', array(), 'name');
		$templates  = pdo_fetchall("SELECT * FROM ".tablename('site_templates'));
		$extend = pdo_fetch("SELECT * FROM ".tablename('uni_group')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
		$extend['modules'] = iunserializer($extend['modules']);
		$extend['templates'] = iunserializer($extend['templates']);
		if (!empty($extend['modules'])) {
			$extend['modules'] = pdo_getall('modules', array('name' => $extend['modules']), array('title', 'name'));
		}
		if (!empty($extend['templates'])) {
			$extend['templates'] = pdo_getall('site_templates', array('id' => $extend['templates']), array('title', 'name'));
		}
	}else {
		message('公众号错误！', referer(), 'error');
	}
	template('account/manage-modules-tpl');
}