<?php

namespace We7\V172;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1521167566
 * @version 1.7.2
 */

class AlterJob {

	/**
	 *  执行更新
	 */
	public function up() {
		if (! pdo_fieldexists('core_job', 'uid')) {
			$tableName = tablename('core_job');
			$sql = <<<EOT
					ALTER TABLE $tableName ADD COLUMN uid int(11) DEFAULT 0;
					ALTER TABLE $tableName ADD COLUMN isdeleted TINYINT(1) DEFAULT 0;
EOT;
			pdo_run($sql);
		}

		if (! pdo_fieldexists('core_job', 'isdeleted')) {
			$tableName = tablename('core_job');
			$sql = <<<EOT
					ALTER TABLE $tableName ADD COLUMN isdeleted TINYINT(1) DEFAULT 0;
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
		