<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class SettingTable extends We7Table {
	public function uniSettingSave($uniacid, $field_name, $filed_value) {
		$setting = $this->query->from('uni_settings')->get('uniacid');
		if (!empty($setting)) {
			pdo_update('uni_settings', array($field_name => $filed_value), array('uniacid' => $uniacid));
		} else {
			pdo_insert('uni_settings', array($field_name => $filed_value, 'uniacid' => $uniacid));
		}
		return true;
	}

	public function uniSetting($uniacid) {
		return $this->query->from('uni_settings')->where('uniacid', $uniacid)->get();
	}

	public function uniSetttingUpdateOauth($uniacid, $acid) {
		$oauth = $this->query->from('uni_settings')->where('uniacid', $uniacid)->get();
		if (empty($oauth['oauth'])) {
			return true;
		}
		$oauth = unserialize($oauth['oauth']);
		if ($oauth['account'] == $acid) {
			$account_wechat = table('account')->getAccountWechatsByUniacid($uniacid);
			if (empty($account_wechat) || $account_wechat['level'] != ACCOUNT_SERVICE_VERIFY || empty($account_wechat['secret']) || empty($account_wechat['key'])) {
				$account_wechat['acid'] = 0;
			}
			pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $account_wechat['acid'], 'host' => $oauth['host']))), array('uniacid' => $uniacid));
		}
		return true;
	}
}