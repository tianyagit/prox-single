<?php

namespace We7\V178;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1532308448
 * @version 1.7.8
 */

class AlterMcFansTagMappingTagidVarchar {

	/**
	 *  执行更新
	 */
	public function up() {
		if (pdo_fieldexists('mc_fans_tag_mapping','tagid')) {
			pdo_query("ALTER TABLE " . tablename('mc_fans_tag_mapping') . " MODIFY COLUMN  `tagid` VARCHAR(20) NOT NULL ;");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		