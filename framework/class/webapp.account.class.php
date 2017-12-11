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
		if (empty($this->account) || (!empty($this->account['account']) && $this->account['type'] != ACCOUNT_TYPE_WEBAPP_NORMAL && !defined('IN_MODULE'))) {
			return array(
				'errno' => -1,
				'url' => url('webapp/home'),
				'frame' => ''
			);
		}
		return array(
			'errno' => 0,
			'url' => '',
			'frame' => 'webapp'
		);
	}

	public function fetchAccountInfo() {
		$account_table = table('account');
		$account = $account_table->getWebappAccount($this->uniaccount['acid']);
		$account['type'] = $this->uniaccount['type'];
		$account['isconnect'] = $this->uniaccount['isconnect'];
		$account['isdeleted'] = $this->uniaccount['isdeleted'];
		$account['endtime'] = $this->uniaccount['endtime'];
		return $account;
	}
}