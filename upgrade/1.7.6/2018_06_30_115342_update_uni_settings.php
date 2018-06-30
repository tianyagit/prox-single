<?php

namespace We7\V176;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1530330822
 * @version 1.7.6
 */

class UpdateUniSettings {

	/**
	 *  执行更新
	 */
	public function up() {
		$wxapp_accounts = pdo_fetchall("SELECT * FROM " . tablename('account') . " WHERE type IN (4,7)");

		if (!empty($wxapp_accounts)) {
			foreach ($wxapp_accounts as $account) {
				$unisettings = array();
				$unisettings['creditnames'] = array('credit1' => array('title' => '积分', 'enabled' => 1), 'credit2' => array('title' => '余额', 'enabled' => 1));
				$unisettings['creditnames'] = iserializer($unisettings['creditnames']);
				$unisettings['creditbehaviors'] = array('activity' => 'credit1', 'currency' => 'credit2');
				$unisettings['creditbehaviors'] = iserializer($unisettings['creditbehaviors']);
				$is_exist = pdo_get('uni_settings', array('uniacid' => $account['uniacid']));
				if ($is_exist) {
					pdo_update('uni_settings', $unisettings, array('uniacid' => $account['uniacid']));
				} else {
					$unisettings['uniacid'] = $account['uniacid'];
					pdo_insert('uni_settings', $unisettings);
				}
			}
		}
	}

	/**
	 *  回滚更新
	 */
	public function down() {


	}
}
