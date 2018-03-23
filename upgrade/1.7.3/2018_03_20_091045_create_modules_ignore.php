<?php

namespace We7\V173;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1521508245
 * @version 1.7.3
 */

class CreateModulesIgnore {

	/**
	 *  执行更新
	 */
	public function up() {
		if(!pdo_tableexists('modules_ignore')){
			$table_name = tablename('modules_ignore');
			$sql = <<<EOT
CREATE TABLE IF NOT EXISTS $table_name (
  `mid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `version` varchar(15) NOT NULL
) DEFAULT CHARSET=utf8;
EOT;

			pdo_query($sql);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {
		

	}
}
		