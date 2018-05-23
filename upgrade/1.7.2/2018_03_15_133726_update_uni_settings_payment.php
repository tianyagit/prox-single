<?php

namespace We7\V172;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1521092246
 * @version 1.7.2
 */

class UpdateUniSettingsPayment {

	/**
	 *  执行更新
	 */
	public function up() {
		$setting = pdo_getall('uni_settings',array(), array('uniacid', 'payment'), 'uniacid');
		if (!empty($setting) && is_array($setting)) {
			foreach ($setting as $key => $value) {
				$payment = iunserializer($value['payment']);
				if (!empty($payment) && is_array($payment)) {
					foreach ($payment as $k => &$val) {
						if (!in_array($k, array('wechat_refund', 'ali_refund'))) {
							if (!empty($val['switch'])) {
								$val['pay_switch'] = $val['recharge_switch'] = true;
							} else {
								$val['pay_switch'] = $val['recharge_switch'] = false;
							}
							if (in_array($k, array('credit', 'mix', 'delivery', 'line'))) {
								$val['recharge_switch'] = false;
							}
						}
					}
					unset($val);
					pdo_update('uni_settings', array('payment' => iserializer($payment)), array('uniacid' => $key));
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
		