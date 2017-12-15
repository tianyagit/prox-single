<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class ProfileTable extends We7Table {
	public function searchProfileField() {
		return $this->query->from('profile_fields')->getall('id');
	}
}