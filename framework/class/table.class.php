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

	//表名
	protected $table_name = '';
	//主键
	protected $primary_key = 'id';
	protected $query;
	//数据库属性
	protected $attributes = array();

	// 字段验证规则
	protected $rules = array();
	// 字段默认值
	protected $defaults = array();
	// 字段类型
	protected $casts = array();


	public function __construct() {
		//实例化Query对象,并重置查询信息
		load()->classs('validator');
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


	protected function fill($attributes) {
		$this->attributes = array_merge($this->attributes, $attributes);
	}

	/**
	 * 追加默认数据
	 */
	private function appendDefauls() {
		foreach ($this->defaults as $field => $value) {
			if (! isset($this->attributes[$field])) {
				if ($value instanceof Closure) {
					$value = call_user_func($value, $this);
				}
				$this->attributes[$field] = $value;
			}
		}
	}

	/**
	 *  获取字段所有验证规则
	 */
	protected function valid($data) {
		$validator = Validator::create($data, $this->rules);
		$result = $validator->valid();
		return $result;
	}
	/**
	 *  创建对象
	 * @param $attributes
	 */
	public function save(array $attributes = array()) {
		$this->fill($attributes);
		$result = $this->valid($attributes);
		if (is_error($result)) {
			return $result;
		}
		//追加默认值
		$this->appendDefauls();
		return pdo_insert($this->table_name, $this->attributes);
	}

	/**
	 * 更新数据
	 * @param array $attributes
	 */
	public function update(array $data = array(), $params = array()) {
		$result = $this->valid($data);
		if (is_error($result)) {
			return $result;
		}
		return pdo_update($this->table_name, $data, $params);
	}

	/** 删除数据
	 * @param $value
	 * @return mixed
	 */
	public function delete(array $params) {
		return pdo_delete($this->table_name, $params);
	}


	public function __set($key, $value) {
		$this->attributes[$key] = $value;
	}

	public function __get($key) {
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}

	/**
	 *  找不到方法默认调用query
	 * @param $method
	 * @param $params
	 * @return mixed
	 */
	public function __call($method, $params) {
		$search_method = 'search'.ucfirst($method);
		if(method_exists($this, $search_method)) {
			return call_user_func_array(array($this, $search_method), $params);
		}
		return call_user_func_array(array($this->query, $method), $params);
	}
}