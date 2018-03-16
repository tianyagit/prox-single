<?php

namespace We7\V172;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1520666528
 * @version 1.7.2
 */

class AlterMcCreditsRecord {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_fieldexists('mc_credits_record', 'real_uniacid')){
			pdo_query("ALTER TABLE " . tablename('mc_credits_record') . " ADD `real_uniacid` INT(11) NOT NULL DEFAULT 0 COMMENT '小程序同步公众号,为小程序的uniacid';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		