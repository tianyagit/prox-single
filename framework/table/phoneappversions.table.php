<?php

defined('IN_IA') or exit('Access Denied');

class PhoneappversionsTable extends We7Table {
	public function getVersionInfo() {
		return $this->query->from('phoneapp_versions')->get();
	}
}