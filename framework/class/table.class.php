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
	//表名
	protected $table_name;
	//主键
	protected $primary_key = 'id';
	//数据库属性
	protected $attributes = array();
	// 内存是否已有数据
	protected $exits = false;
	//主键是否自增
	protected $incrementing = true;

	public function __construct() {
		//实例化Query对象,并重置查询信息
		$this->query = load()->object('Query');
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
	
	/**
	 * 获取上一次查询的除去Limit的条数
	 */
	public function getLastQueryTotal() {
		return $this->query->getLastQueryTotal();
	}

	private function getAttribute($key) {
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}

	private function setAttribute($key, $value) {

	}

	private function fill($attributes) {

	}

	/** 根据主键获取数据
	 * @param $primary_key
	 */
	public function find($primary_key) {
		if(is_array($primary_key)) {
			throw new Exception('不支持数组参数');
		}
		$data = $this->query->from($this->table_name)->
			where($this->primary_key, $primary_key)->get();

		if (empty($data)) {
			return null;
		}
		$this->exits = true;
		$this->attributes = $data;
		return $this;
	}

	public function create($attributes) {
		$this->exits = false;
		$this->fill($attributes);
		$insert = pdo_insert($this->table_name, $this->attributes);
		if ($insert) {
			$this->exits = true;
			if($this->incrementing) {
				$primary_id = pdo_insertid();
				$this->attributes[$this->primary_key] = $primary_id;
			}
		}
		return $insert ? $this : false;
	}

	public function update($attributes, $primary_key = null) {
		$pkey = $this->getAttribute($this->primary_key);
		$this->fill($attributes);
		if (! $this->exits) {
			$pkey = $primary_key;
		}
		return pdo_update($this->table_name, $this->attributes, array($this->primary_key=>$pkey));
	}

	public function delete($id = null) {
		if($this->exits) {
			return pdo_delete($this->table_name, array($this->primary_key=>$this->getAttribute($this->primary_key)));
		}
	}
}