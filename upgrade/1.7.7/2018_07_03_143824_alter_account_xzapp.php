<?php

namespace We7\V177;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1530599904
 * @version 1.7.7
 */

class AlterAccountXzapp {

	/**
	 *  执行更新
	 */
	public function up() {
		if (!pdo_fieldexists('account_xzapp', array('original', 'lastupdate', 'styleid', 'createtime'))) {
			$table_name = tablename('account_xzapp');
			$sql = <<<EOT
				ALTER TABLE {$table_name} ADD original VARCHAR(50) DEFAULT '' NOT NULL COMMENT '熊掌号id' ;
				ALTER TABLE {$table_name} ADD lastupdate INT(10) DEFAULT 0 NOT NULL ;
				ALTER TABLE {$table_name} ADD styleid INT(10) DEFAULT 0 NOT NULL COMMENT '风格ID' ;
				ALTER TABLE {$table_name} ADD createtime INT(10) DEFAULT 0 NOT NULL COMMENT '添加时间' ;
	
EOT;
			pdo_run($sql);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		