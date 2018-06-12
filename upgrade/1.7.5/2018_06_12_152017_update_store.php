<?php

namespace We7\V175;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1528788017
 * @version 1.7.5
 */

class UpdateStore {

	/**
	 *  执行更新
	 */
	public function up() {
		$store_exist = pdo_get('modules', array('name' => 'store'));
		if (empty($store_exist)) {
			pdo_run("INSERT INTO `ims_modules` (`mid`, `name`, `type`, `title`, `title_initial`, `version`, `ability`, `description`, `author`, `url`, `settings`, `subscribes`, `handles`, `isrulefields`, `issystem`, `target`, `iscard`, `permissions`, `wxapp_support`, `account_support`, `welcome_support`, `webapp_support`, `oauth_type`, `phoneapp_support`, `xzapp_support`) VALUES (NULL, 'store', 'business', '站内商城', 'Z', '1.0', '站内商城', '站内商城', 'we7', '', '0', '', '', '0', '1', '0', '0', '', '1', '2', '1', '1', '0', '1', '2');");
		}
	}

	/**
	 *  回滚更新
	 */
	public function down() {


	}
}
