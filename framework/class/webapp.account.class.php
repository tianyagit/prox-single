<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

class WebappAccount extends WeAccount {
	public function __construct($account = array()) {
		if (empty($account)) {
			return true;
		}
		$this->account = $account;
	}
	
	public function checkIntoManage() {
		
	}
}