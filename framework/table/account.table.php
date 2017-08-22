<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

class AccountTable extends We7Table {
	
	public function searchAccountList() {
		return $this->query->from('uni_account', 'a')->leftjoin('account', 'b')->
				on(array('a.uniacid' => 'b.uniacid', 'a.default_acid' => 'b.acid'))->getall();
	}
	
	public function searchKeyword($title) {
		$this->query->where('a.name LIKE', "%{$title}%");
		return $this;
	}
}