<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class MessageTable extends We7Table {

	public function messageList() {
		return $this->query->from('message_notice_log')->orderby('id', 'DESC')->getall();
	}

	public function searchWithType($type) {
		$this->query->where('type', $type);
		return $this;
	}

	public function searchWithIsRead($is_read) {
		$this->query->where('is_read', $is_read);
		return $this;
	}

	public function messageNoReadCount() {
		return $this->query->from('message_notice_log')->where('is_read', MESSAGE_NOREAD)->count();
	}
}