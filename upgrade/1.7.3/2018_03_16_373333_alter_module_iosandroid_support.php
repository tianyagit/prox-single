<?php

namespace We7\V173;

defined('IN_IA') or exit('Access Denied');

class AlterModuleIosandroidSupport {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_fieldexists('modules', 'ios_support')) {
			pdo_query("ALTER TABLE " . tablename('modules') . " ADD `ios_support` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否支持APP ios应用 1 不支持 2支持';");
		}
		if(!pdo_fieldexists('modules', 'android_support')) {
			pdo_query("ALTER TABLE " . tablename('modules') . " ADD `android_support` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否支持android应用 1 不支持 2支持';");
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		