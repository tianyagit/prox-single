<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class SystemstatvisitTable extends We7Table {
	public function deleteVisitRecord($uid) {
		load()->model('user');
		$user_modules = user_modules($uid);
		$modules = !empty($user_modules) ? array_keys($user_modules) : array();

		$old_modules = pdo_getall('system_stat_visit', array('uid' => $uid, 'uniacid' => 0), 'modulename');
		if (empty($old_modules)) {
			return true;
		}

		$old_modules = array_column($old_modules, 'modulename');
		$delete_modules = array_diff($old_modules, $modules);
		$this->query->from('system_stat_visit');
		if (!empty($modules)) {
			$this->query->where('modulename', $delete_modules);
		}
		$this->query->where('uid', $uid)
			->where('uniacid', 0)
			->delete();
		return true;
	}
}