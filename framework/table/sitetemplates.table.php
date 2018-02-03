<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');
class SitetemplatesTable extends We7Table {
	protected $tableName = 'site_templates';

	public function getAllTemplates() {
		return $this->query->getall('name');
	}
}