<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

/**
 * @property Query $query
 */
abstract class We7Table {
	protected $query;
	
	public function __construct() {
		//实例化Query对象,并重置查询信息
		$this->query = load()->singleton('Query');
		$this->query->from('');
	}
	
	/**
	 * 构造一个查询分页
	 * @param int $pageindex
	 * @param int $pagesize
	 * @return We7Table
	 */
	public function searchWithPage($pageindex, $pagesize) {
		if (!empty($pageindex) && !empty($pagesize)) {
			$this->query->page($pageindex, $pagesize);
		}
		
		return $this;
	}
	
	public function getLastQueryTotal() {
		return $this->query->getLastQueryTotal();
	}
}