<?php

namespace We7\V178;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1532073547
 * @version 1.7.8
 */

class UpdateModuleCloud {

	/**
	 *  执行更新
	 */
	public function up() {
        if(!pdo_fieldexists('modules_cloud', 'cloud_id')) {
            pdo_query("ALTER TABLE " . tablename('modules_cloud') . " ADD `cloud_id` INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  '云商城内模块ID' AFTER  `id`;");
        }
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		