<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class AccountTable extends We7Table {

	public function searchAccountList($expire = false) {
		global $_W;
		$this->query->from('uni_account', 'a')->select('a.uniacid')->leftjoin('account', 'b')
				->on(array('a.uniacid' => 'b.uniacid', 'a.default_acid' => 'b.acid'))
				->where('b.isdeleted !=', '1');

		//普通用户和副站长查询时，要附加可操作公众条件
		if (!user_is_founder($_W['uid']) || user_is_vice_founder()) {
			$this->query->leftjoin('uni_account_users', 'c')->on(array('a.uniacid' => 'c.uniacid'))
						->where('a.default_acid !=', '0')->where('c.uid', $_W['uid']);
		} else {
			$this->query->where('a.default_acid !=', '0');
		}
		if (!empty($expire)) {
			$this->searchWithExprie();
		}
		$this->accountUniacidOrder();
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
	 * 获取某用户拥有的公众号的详细信息
	 * @param $uniacids
	 * @param $uid
	 * @return mixed
	 */
	public function accountWechatsInfo($uniacids, $uid) {
		return $this->query->from('uni_account', 'a')
				->leftjoin('account_wechats', 'w')
				->on(array('w.uniacid' => 'a.uniacid'))
				->leftjoin('uni_account_users', 'au')
				->on(array('a.uniacid' => 'au.uniacid'))
				->where(array('a.uniacid' => $uniacids))
				->where(array('au.uid' => $uid))
				->orderby('a.uniacid', 'asc')
				->getall('acid');
	}

	/**
	 * 获取某用户拥有的小程序的详细信息
	 * @param $uniacids
	 * @param $uid
	 * @return mixed
	 */
	public function accountWxappInfo($uniacids, $uid) {
		return $this->query->from('uni_account', 'a')
				->leftjoin('account_wxapp', 'w')
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

	public function searchWithTitle($title) {
		$this->query->where('a.name', $title);
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

	public function accountRankOrder() {
		$this->query->orderby('a.rank', 'desc');
		return $this;
	}

	public function accountUniacidOrder($order = 'desc') {
		$order = !empty($order) ? $order : 'desc';
		$this->query->orderby('a.uniacid', $order);
		return $this;
	}

	public function searchWithNoconnect() {
		$this->query->where('b.isconnect =', '0');
		return $this;
	}

	public function searchWithExprie() {
		global $_W;
		if (user_is_founder($_W['uid']) && !user_is_vice_founder()) {
			$this->query->leftjoin('uni_account_users', 'c')->on(array('a.uniacid' => 'c.uniacid'));
			$this->query->leftjoin('users', 'u')->on(array('c.uid' => 'u.uid'))
				->where('c.role', 'owner')->where('u.endtime !=', 0)->where('u.endtime <', TIMESTAMP);
		}

		return $this;
	}
	
	public function getWechatappAccount($acid) {
		return $this->query->from('account_wechats')->where('acid', $acid)->get();
	}
	
	public function getWxappAccount($acid) {
		return $this->query->from('account_wxapp')->where('acid', $acid)->get();
	}
	
	public function getWebappAccount($acid) {
		return $this->query->from('account_webapp')->where('acid', $acid)->get();
	}
	
	public function getUniAccountByUniacid($uniacid) {
		$uniaccount = $this->query->from('uni_account')->where('uniacid', $uniacid)->get();
		if (!empty($uniaccount['default_acid'])) {
			$subaccount = $this->query->from('account')->where('acid', $uniaccount['default_acid'])->get();
		} else {
			$subaccount = $this->query->from('account')->where('uniacid', $uniacid)->orderby('acid', 'desc')->get();
		}
		if (empty($subaccount)) {
			return array();
		} else {
			return array_merge($uniaccount, $subaccount);
		}
	}
	
	public function getAccountOwner($uniacid) {
		if (empty($uniacid)) {
			return array();
		}
		$owneruid = $this->query->from('uni_account_users')->where(array('uniacid' => $uniacid, 'role' => ACCOUNT_MANAGE_NAME_OPERATOR))->getcolumn('uid');
		if (empty($owneruid)) {
			return array();
		}
		return table('users')->usersInfo($owneruid);
	}

	public function getUserAccounts($uid, $role = '') {
		global $_W;
		$uid = !empty($uid) >0 ? intval($uid) : $_W['uid'];
		$field = 'w.acid, w.uniacid, w.key, w.secret, w.level, w.name, w.token';
		$user_is_founder = user_is_founder($uid);
		if (empty($user_is_founder) || user_is_vice_founder($uid)) {
			$field .= ', u.role';
		}
		$this->query->from('account_wechats', 'w')->select($field)->leftjoin('account', 'a')
				->on(array('w.acid' => 'a.acid', 'a.uniacid' => 'w.uniacid'))->where('a.isdeleted !=', '1');
		if (empty($user_is_founder) || user_is_vice_founder($uid)) {
			$this->query->leftjoin('uni_account_users', 'u')->on(array('u.uniacid' => 'w.uniacid'))->where('u.role', $role)->where('u.uid', $uid);
		}
		return $this->query->getall('uniacid');
	}

	public function getAccountByuniacid($unicaid) {
		return $this->query->from('account')->where('uniacid', $unicaid)->get('acid');
	}

	public function getTbaleName($type) {
		$table = array(
			ACCOUNT_TYPE_OFFCIAL_NORMAL => 'account_wechats',
			ACCOUNT_TYPE_OFFCIAL_AUTH => 'account_wechats',
			ACCOUNT_TYPE_APP_NORMAL => 'account_wxapp',
			ACCOUNT_TYPE_WEBAPP_NORMAL => 'account_webapp',
		);
		return $table[$type];
	}

	public function getUniAccounts($uniacid, $type) {
		$field = 'w.*, a.type, a.isconnect';
		$tablename = $this->getTbaleName($type);
		return $this->query->select($field)->from('account', 'a')->leftjoin($tablename, 'w')->on(array('a.uniacid' => 'w.uniacid'))
				->where('uniacid', $uniacid)->where('a.isdeleted !=', 1)->orderby('a.acid', 'asc')->getall('acid');
	}

	public function getUniAccountNums($uniacid) {
		return $this->query->from('account_wechats')->where('uniacid', $uniacid)->count();
	}

	public function getUniAccountByAcid($acid) {
		return $this->query->from('uni_account')->where('default_acid', $acid)->get();
	}

	public function getAccountsByUniacid($uniacid) {
		return $this->query->from('account')->where('uniacid', $uniacid)->getall();
	}

	public function getAccountWechatsByUniacid($uniacid) {
		return $this->query->from('account_wechats')->where('uniacid', $uniacid)->get();
	}

	public function getAccountOwnerUidByUniacid($uniacid) {
		$owner = $this->query->from('uni_account_users')->where('uniacid', $uniacid)->where('role', 'owner')->get();
		if (empty($owner)) {
			$owner = $this->query->from('uni_account_users')->where('uniacid', $uniacid)->where('role', 'vice_founder')->get();
		}
		if (empty($owner)) {
			return false;
		}
		return $owner['uid'];
	}

	public function getAccountByAcid($acid) {
		return $this->query->from('account')->where('acid', $acid)->get();
	}

	public function createAccount($uniacid, $account) {
		$accountdata = array('uniacid' => $uniacid, 'type' => $account['type'], 'hash' => random(8));
		pdo_insert('account', $accountdata);
		$acid = pdo_insertid();
		$account['acid'] = $acid;
		$account['token'] = random(32);
		$account['encodingaeskey'] = random(43);
		$account['uniacid'] = $uniacid;
		unset($account['type']);
		pdo_insert('account_wechats', $account);
		return $acid;
	}

	public function getUniAccountMaxRand() {
		$rank = $this->query->from('uni_account')->orderby('rank', 'desc')->get();
		return empty($rank) ? 0 : $rank['rank'];
	}

	public function getUniAccountUserMaxRand() {
		global $_W;
		$rank = $this->query->from('uni_account_users')->where('uid', $_W['uid'])->orderby('rank', 'desc')->get();
		return empty($rank) ? 0 : $rank['rank'];
	}


	public function uniAccountRankTop($uniacid) {
		global $_W;
		if (empty($uniacid)) {
			return true;
		}

		if (!empty($_W['isfounder'])) {
			$max_rank = $this->getUniAccountMaxRand();
			pdo_update('uni_account', array('rank' => ($max_rank + 1)), array('uniacid' => $uniacid));
		}else {
			$max_rank = $this->getUniAccountUserMaxRand();
			pdo_update('uni_account_users', array('rank' => ($max_rank['maxrank'] + 1)), array('uniacid' => $uniacid, 'uid' => $_W['uid']));
		}
		return true;
	}
}