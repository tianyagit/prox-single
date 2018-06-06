<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class XiongzhangappAccount extends WeAccount {
	public function __construct($account = array()) {
		$this->menuFrame = 'xiongzhangapp';
		$this->type = ACCOUNT_TYPE_XIONGZHANGAPP_NORMAL;
		$this->typeName = 'XIONGZHANGAPP';
		$this->typeTempalte = '-xiongzhangapp';
	}

	public function checkIntoManage() {
		if (empty($this->account) || (!empty($this->uniaccount['account']) && $this->uniaccount['type'] != ACCOUNT_TYPE_XIONGZHANGAPP_NORMAL && !defined('IN_MODULE'))) {
			return false;
		}
		return true;
	}

	public function fetchAccountInfo() {
		$account_table = table('account');
		$account = $account_table->getXiongzhangappAccount($this->uniaccount['acid']);
		return $account;
	}

	public function accountDisplayUrl() {
		return url('account/display', array('type' => XIONGZHANGAPP_TYPE_SIGN));
	}
}