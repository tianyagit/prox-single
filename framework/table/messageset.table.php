<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class MessagesetTable extends We7Table {

	protected $tableName = 'message_notice_set';
	protected $field = array('id', 'property', 'type', 'status', 'time');

	public function messagesSetList() {
		global $_W;
		return $this->query->from($this->tableName)->orderby('id', 'DESC')->getall();
	}
}