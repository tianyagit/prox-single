<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class SettingTable extends We7Table {
	public function getTemplatesById($id, $keyword) {
		return $this->query->from('site_templates')->where('id', $id)->getall($keyword);
	}
}