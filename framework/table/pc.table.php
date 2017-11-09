<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 * @since 1.6.3
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
defined('IN_IA') or exit('Access Denied');

class PcTable extends We7Table {

	const ACCOUNT_TABLE = 'account';
	const UNI_ACCOUNT_TABLE = 'uni_account_table';
	private $PC_TYPE = 5;


	/**
	 *  创建PC
	 * @param $attr
	 * @return bool
	 * @since version
	 */
	public function create($attr) {
		$name = $attr['name'];
		$description = $attr['description'];
		$data = array(
			'name' => $name,
			'description' => $description,
			'title_initial' => get_first_pinyin($name),
			'groupid' => 0,
		);
		if (!pdo_insert('uni_account', $data)) {
			 return false;
		}
		$uniacid = pdo_insertid();
		if($uniacid) {
			$accountdata = array('uniacid' => $uniacid, 'type' => $this->PC_TYPE, 'hash' => random(8));
			pdo_insert('account', $accountdata);
			$acid = pdo_insertid();
			pdo_update('uni_account', array('default_acid'=>$acid), array('uniacid'=>$uniacid));
			pdo_insert('account_pc', array('uniacid'=>$uniacid, 'acid'=>$acid));
		}

		return $uniacid;
	}

	/**
	 *  删除PC
	 * @param $uniacid
	 *
	 *
	 * @since version
	 */
	public function delete($uniacid) {
		if(is_array($uniacid)) {
			return false;
		}
		pdo_delete(self::ACCOUNT_TABLE, array('uniacid'=>$uniacid));
		pdo_delete(self::UNI_ACCOUNT_TABLE, array('uniacid'=>$uniacid));
	}

	/** 修改pc
	 * @param $attr
	 * @param $uniacid
	 * @since version
	 */
	public function update($attr, $uniacid) {
		pdo_update(self::UNI_ACCOUNT_TABLE, $attr, array('uniacid'=>$uniacid));
	}

	/**
	 * 查询所有PC
	 * @since version
	 */
	public function pclist($uid, $pageindex = 1, $pagesize = 15) {
		$query = $this->createQuery($uid);
		$query->page($pageindex, $pagesize);
		$query->where(array('b.type' => array(ACCOUNT_TYPE_PC_NORMAL)));
		$total =  $query->getLastQueryTotal();
		$list = $query->getall();
		return [$list, $total];
	}

	/**
	 * @param $uid
	 *
	 * @return Query
	 *
	 * @since version
	 */
	private function createQuery($uid) {
		$query = load()->object('Query');
		$query->from('uni_account', 'a')->select('a.uniacid')->select(array('a.name','a.default_acid','a.uniacid'))->leftjoin('account', 'b')
			->on(array('a.uniacid' => 'b.uniacid', 'a.default_acid' => 'b.acid'))
			->where('b.isdeleted !=', '1');

		//普通用户和副站长查询时，要附加可操作公众条件
		if (!user_is_founder($uid) || user_is_vice_founder()) {
			$query->leftjoin('uni_account_users', 'c')->on(array('a.uniacid' => 'c.uniacid'))
				->where('a.default_acid !=', '0')->where('c.uid', $uid);
		} else {
			$query->where('a.default_acid !=', '0');
		}
		return $query;
	}


}