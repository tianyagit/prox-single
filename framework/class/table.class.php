<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

/**
 * @property Query $query
 */
abstract class We7Table implements ArrayAccess {
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
	// 获取数据默认值
	protected $default = array();
	// 是否自定追加创建时间
	public $timestamps = true;
	// 允许fill的字段
	protected $fillable = array();


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

	protected function getPrimaryVal() {
		return $this->getAttribute($this->primary_key);
	}

	private function getAttribute($key) {
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}


	private function setAttribute($key, $value) {
		if($this->canFill($key)) {
			$this->attributes[$key] = $value;
		}
	}


	private function fill($attributes) {
		foreach ($attributes as $key => $value) {
			$this->setAttribute($key, $value);
		}
	}

	protected function canFill($key) {
		return isset($this->fillable[$key]);
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
				$primary_value = pdo_insertid();
				$this->attributes[$this->primary_key] = $primary_value;
			}
		}
		return $insert ? $this : false;
	}

	public function update($attributes, $primary_val = null) {
		$default_primary_val = $this->getPrimaryVal();
		$this->fill($attributes);
		if (! $this->exits) {
			$default_primary_val = $primary_val;
		}
		return pdo_update($this->table_name, $this->attributes, array($this->primary_key=>$default_primary_val));
	}
	/**
	 *  根据主键删除数据
	 * @param null $id
	 * @return mixed
	 */
	public function delete($id = null) {
		$pval = $this->getPrimaryVal();
		if($id) {
			$pval = $id;
		}
		$deleted = pdo_delete($this->table_name, array($this->primary_key => $pval));
		if ($deleted) {
			$this->exits = false;
		}
		return $deleted;
	}


	public function offsetExists($offset) {
		return isset($this->attributes[$offset]);
	}

	public function offsetGet($offset) {
		return $this->getAttribute($offset);
	}

	public function offsetSet($offset, $value) {
		$this->setAttribute($offset, $value);
	}

	public function offsetUnset($offset) {
		unset($this->attributes[$offset]);
	}
}