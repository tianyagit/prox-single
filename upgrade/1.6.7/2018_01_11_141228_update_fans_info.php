<?php

namespace We7\V167;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1515651148
 * @version 1.6.7
 */

class UpdateFansInfo {

	/**
	 *  执行更新
	 */
	public function up() {
		load()->model('mc');
		$fans_list = pdo_getall('mc_mapping_fans');
		foreach ($fans_list as $value) {
			if (!empty($value['tag']) && is_string($value['tag'])) {
				if (is_base64($value['tag'])) {
					$value['tag'] = base64_decode($value['tag']);
				}
				if (is_serialized($value['tag'])) {
					$value['tag'] = @iunserializer($value['tag']);
				}
				if (!empty($value['tag']['headimgurl'])) {
					$value['tag']['avatar'] = tomedia($value['tag']['headimgurl']);
				}
				if (!empty($value['tag']['headimgurl']) && preg_match('/\/132132$/', $value['tag']['headimgurl'])) {
					mc_init_fans_info($value['openid']);
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
