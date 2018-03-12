<?php

namespace We7\V172;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1520833261
 * @version 1.7.2
 */

class UpdateMcMappingFans {

	/**
	 *  执行更新
	 */
	public function up() {
		if (pdo_fieldexists('mc_mapping_fans', 'tag')){
			pdo_query("ALTER TABLE " . tablename('mc_mapping_fans') . " CHANGE `tag`  `tag` VARCHAR( 2000 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");			
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		