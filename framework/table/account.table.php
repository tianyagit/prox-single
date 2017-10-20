<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class AccountTable extends We7Table {

	public function searchAccountList($order = '', $type = '') {
		global $_W;
		$this->query->from('uni_account', 'a')->select('a.uniacid')->leftjoin('account', 'b')
				->on(array('a.uniacid' => 'b.uniacid', 'a.default_acid' => 'b.acid'))
				->where('b.isdeleted !=', '1');

		//普通用户和副站长查询时，要附加可操作公众条件
		if (empty($_W['isfounder']) || user_is_vice_founder()) {
			$this->query->leftjoin('uni_account_users', 'c')->on(array('a.uniacid' => 'c.uniacid'))
						->where('a.default_acid !=', '0')->where('c.uid', $_W['uid']);

		} else {
			$this->query->where('a.default_acid !=', '0');
			if (!empty($type) && $type == 'expire') {
				$this->query->leftjoin('uni_account_users', 'c')->on(array('a.uniacid' => 'c.uniacid'));
			}
		}

		if (!empty($type) && $type == 'expire') {
			$this->query->leftjoin('users', 'u')->on(array('c.uid' => 'u.uid'))
					->where('c.role', 'owner')->where('u.endtime !=', 0)->where('u.endtime <', TIMESTAMP);
		}

		if (!empty($type) && $type == 'isconnect') {
			$this->query->where('b.isconnect =', '0');
		}

		$this->query->orderby('a.uniacid', $order);
		$list = $this->query->getall('a.uniacid');
		return $list;
	}

	/**
	 *  获取用户所能操作的所有公众号
	 */
	public function userOwnedAccount($uid = 0) {
		global $_W;
		$uid = intval($uid) > 0 ? intval($uid) : $_W['uid'];
		$is_founder = user_is_founder($uid);
		if (empty($is_founder) || user_is_vice_founder($uid)) {
			$users_table = table('users');
			$uniacid_list = $users_table->userOwnedAccount($uid);
			if (empty($uniacid_list)) {
				return array();
			}
			$this->query->where('u.uniacid', $uniacid_list);
		}
		return $this->query->from('uni_account', 'u')->leftjoin('account', 'a')->on(array('u.default_acid' => 'a.acid'))->where('a.isdeleted', 0)->getall('uniacid');
	}

	/**
	 * 获取某用户拥有的公众号(小程序)详细信息
	 * @param $type
	 * @param $uniacids
	 * @param $uid
	 * @return mixed
	 */
	public function accountUniInfo($type, $uniacids, $uid) {
		return $this->query->from('uni_account', 'a')
				->leftjoin(uni_account_tablename($type), 'w')
				->on(array('w.uniacid' => 'a.uniacid'))
				->leftjoin('uni_account_users', 'au')
				->on(array('a.uniacid' => 'au.uniacid'))
				->where(array('a.uniacid' => $uniacids))
				->where(array('au.uid' => $uid))
				->orderby('a.uniacid', 'asc')
				->getall('acid');
	}

	public function searchWithKeyword($title) {
		$this->query->where('a.name LIKE', "%{$title}%");
		return $this;
	}

	public function searchWithType($types = array()) {
		$this->query->where(array('b.type' => $types));
		return $this;
	}

	public function searchWithLetter($letter) {
		if (!empty($letter)) {
			$this->query->where('a.title_initial', $letter);
		} else {
			$this->query->where('a.title_initial', '');
		}
		return $this;
	}
}