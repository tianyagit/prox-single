<?php

namespace We7\V178;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1532058477
 * @version 1.7.8
 */

class AlterMcMappingFansGroupid {

	/**
	 *  执行更新
	 */
	public function up() {
		if (pdo_fieldexists('mc_mapping_fans','groupid')) {
			pdo_query("ALTER TABLE " . tablename('mc_mapping_fans') . " MODIFY COLUMN `groupid` varchar(60) NOT NULL ;");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		