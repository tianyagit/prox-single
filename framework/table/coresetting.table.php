<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class CoresettingTable extends We7Table  {
	protected $tableName = 'core_settings';

	public function getSettingList() {
		return $this->query->from($this->tableName)->getall('key');
	}
}