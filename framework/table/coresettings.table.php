<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class CoresettingsTable extends We7Table {
	public function searchSetting($key) {
		return $this->query->from('core_settings')
			->select('value')
			->where('key', $key)
			->get();
	}
}