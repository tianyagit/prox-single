<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: malina
 * Date: 2017/9/8
 * Time: 12:26.
 */
namespace We7\V155;

defined('IN_IA') or exit('Access Denied');
class UpdateUsers {
	public function up() {
		if (pdo_fieldexists('users', 'type')) {
			pdo_query("UPDATE ".tablename('users')." SET `type` = 1 WHERE `type` = 0;");
		}
	}
}
