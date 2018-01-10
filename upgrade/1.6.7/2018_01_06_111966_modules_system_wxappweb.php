<?php

namespace We7\V167;

defined('IN_IA') or exit('Access Denied');
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * Time: 1515122343
 * @version 1.6.7
 */

class ModulesSystemWxappweb {

	/**
	 *  执行更新
	 */
	public function up() {
		$info = pdo_get('modules', array('name' => 'wxappweb', 'issystem' => 1));
		if (empty($info)) {
			$data = array(
				'name' => 'wxappweb',
				'issystem' => 1
			);
			pdo_insert('modules', $data);
		}
	}
	
	/**
	 *  回滚更新
	 */
	public function down() {


	}
}
		