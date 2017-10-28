<?php
namespace We7\V161;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1506147730
 * @version 1.6.1
 */


class UpgradeUsersProfile {

	/**
	 *  执行更新
	 */
	public function up() {
		$sms_info = pdo_get('core_cron', array('filename' => 'sms'));
		if (empty($sms_info)) {
			load()->model('cloud');
			$data = array(
					'module' => 'task',
					'type' => 2,
					'name' => '用户到期发送短信任务',
					'filename' => 'sms',
					'day' => -1,
					'hour' => 16,
					'minute' => 30,
					'status' => 1,
					'createtime' =>TIMESTAMP
			);
			cron_add($data);
		}

		if (!pdo_fieldexists('users_profile', 'is_send_mobile_status')) {
			pdo_query('ALTER TABLE ' . tablename('users_profile') . " ADD `is_send_mobile_status` TINYINT(3) NOT NULL DEFAULT 0 COMMENT '0没发短信，1已经发送信息';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		