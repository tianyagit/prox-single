<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class GroupTable extends We7Table {
	public function searchGroup($is_vice_founder = false) {
		$table_name = empty($is_vice_founder) ? 'users_group' : 'users_founder_group';
		return $this->query->from($table_name)->get();
	}

	public function searchWithId($id) {
		$this->query->where('id', $id);
		return $this;
	}

	public function searchWithName($name) {
		$this->query->where('name', $name);
		return $this;
	}

	public function searchWithNoId($id) {
		$this->query->where('id !=', $id);
		return $this;
	}

	public function searchGroups() {
		return $this->query->from('users_group')->getall('id');
	}

	public function uniGroupOrderById($order) {
		return $this->query->orderby('id', $order);
	}

	public function searchGroupByUniacid($uniacid) {
		return $this->query->where('uniacid', $uniacid);
	}

	public function searchUniGroupsList($keyword = '') {
		return $this->query->from('uni_group')->getall($keyword);
	}

	public function searchUniAccountGroup($uniacid) {
		return $this->query->from('uni_account_group')->where('uniacid', $uniacid)->getall('groupid');
	}

	public function searchUniGroups($uniacid, $id) {
		return $this->query->from('uni_group')->where('uniacid', $uniacid)->whereor('id', $id)->getall();
	}
}