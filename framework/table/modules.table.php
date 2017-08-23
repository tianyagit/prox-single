<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class ModulesTable extends We7Table {
	function modulesCount() {
		$count = $this->query->from('modules')->select('COUNT(*) as total')->where('type', 'system')->where('issystem', 1)->getall();
		return $count[0]['total'];
	}
}