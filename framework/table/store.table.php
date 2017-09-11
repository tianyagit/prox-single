<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

class StoreTable extends We7Table {

	/**获取站内商城的商品展示列表
	 * @param $type string 1.模块 2.公众号 3.小程序
	 */
	public function searchGoodsList($type = '', $pageindex, $pagesize) {
		$this->query->from('site_store_goods');
		if (!empty($type)) {
			$this->query->where('type', $type);
		}
		$goods_list = array(
			'goods_list' => $this->searchWithPage($pageindex, $pagesize)->query->where('status !=', STORE_GOODS_STATUS_DELETE)->getall('id'),
			'total' => $this->getLastQueryTotal()
		);
		return $goods_list;
	}

	public function searchWithKeyword($title) {
		if (!empty($title)) {
			$this->query->where('title LIKE', "%{$title}%");
			return $this;
		}
	}


	public function searchWithStatus($status) {
		$status = intval($status) > 0 ? 1 : 0;
		$this->query->where('status', $status);
		return $this;
	}

	public function searchWithLetter($letter) {
		if (!empty($letter)) {
			$this->query->where('title_initial LIKE', "%{$letter}%");
			return $this;
		}
	}

	public function goodsInfo($id) {
		$id = intval($id);
		$this->query->from('site_store_goods')->where('id', $id);
		return $this->query->get();
	}

	public function searchOrderList($pageindex, $pagesize) {
		$this->query->from('site_store_order');
		$lists = $this->searchWithPage($pageindex, $pagesize)->query->orderby('id', 'desc')->getall();
		return $lists;
	}

	public function searchOrderType($type, $ortype = 0) {
		$type = intval($type);
		if (!empty($ortype)) {
			$ortype = intval($ortype);
		}
		if (!empty($ortype)) {
			$this->query->where('type in', array($type, $ortype));
		} else {
			$this->query->where('type', $type);
		}
		return $this;
	}

	public function searchOrderInfo($id) {
		$id = intval($id);
		$result = $this->query->from('site_store_order')->where('id', $id)->get();
		return $result;
	}

	public function searchOrderWithUid($uid) {
		$uid = intval($uid);
		$this->query->where('buyerid', $uid);
		return $this;
	}

	public function searchHaveModule() {
		$this->query->from('site_store_goods');
		$result = $this->query->where('type', STORE_TYPE_MODULE)->where('status !=', STORE_GOODS_STATUS_DELETE)->getall('module');
		return $result;
	}
}
