<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class SystemstatvisitTable extends We7Table {
	protected $tableName = 'system_stat_visit';
	protected $primaryKey = 'id';
	protected $field = array('id', 'uniacid', 'modulename', 'uid', 'displayorder', 'createtime', 'updatetime');

	public function systemStatUpdate($system_stat_visit, $displayorder = false) {
		if (empty($system_stat_visit['uniacid']) && empty($system_stat_visit['modulename'])) {
			return true;
		}
		if (empty($system_stat_visit['uid'])) {
			return true;
		}

		if (!empty($system_stat_visit['uniacid'])) {
			$this->query->where('uniacid', $system_stat_visit['uniacid']);
		}

		if (!empty($system_stat_visit['modulename'])) {
			$this->query->where('modulename', $system_stat_visit['modulename']);
		}

		$system_stat_info = $this->query->from($this->tableName)->where('uniacid', $system_stat_visit['uniacid'])->where('modulename', $system_stat_visit['modulename'])->where('uid', $system_stat_visit['uid'])->get();

		if (empty($system_stat_info['createtime'])) {
			$system_stat_visit['createtime'] = TIMESTAMP;
		}

		if (empty($system_stat_visit['updatetime'])) {
			$system_stat_visit['updatetime'] = TIMESTAMP;
		}

		if (empty($system_stat_info)) {
			table('systemstatvisit')->fill($system_stat_visit)->save();
		} else {
			if (!empty($displayorder)) {
				$system_stat_max_order = pdo_fetchcolumn("SELECT MAX(displayorder) FROM " . tablename('system_stat_visit') . " WHERE uid = :uid", array(':uid' => $system_stat_info['uid']));
				$system_stat_visit['displayorder'] = ++$system_stat_max_order;
			}
			$system_stat_visit['updatetime'] = TIMESTAMP;
			table('systemstatvisit')->fill($system_stat_visit)->whereId($system_stat_info['id'])->save();
		}
		return true;
	}
}