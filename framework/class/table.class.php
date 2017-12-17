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
	protected $tableName = '';
	//主键
	protected $primaryKey = 'id';
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
		$this->query->from($this->tableName);
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


	public function fill($field, $value = '') {
		if (is_array($field)) {
			foreach ($field as $column => $val) {
				$this->attributes[$column] = $val;
			}
			return $this;
		}
		$this->attributes[$field] = $value;
		$this->query->fill($field, $value);
		return $this;
	}

	/**
	 *  根据主键获取数据
	 * @param $id
	 * @return mixed
	 */
	public function findById($id) {
		$query = $this->query->from($this->tableName)->where($this->primaryKey, $id);
		if (is_array($id)) {
			return $query->getall();
		}
		return $query->get();
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
		if (count($this->rules) <= 0) {
			return error(0);
		}

		$validator = Validator::create($data, $this->rules);
		$result = $validator->valid();
		return $result;
	}
	/**
	 *  创建对象
	 * @param $attributes
	 */
	public function save() {
		$this->appendDefauls();
		$result = $this->valid($this->attributes);
		if (is_error($result)) {
			return $result;
		}

		if($this->query->hasWhere()) {
			return $this->query->update();
		}
		return $this->query->insert();
	}

	/** 删除数据
	 * @param $value
	 * @return mixed
	 */
	public function delete() {
		if ($this->query->hasWhere()) {
			return $this->query->delete();
		}
		return false;
	}

	private function doWhere($field, $params) {
		if ($params == 0) {
			return $this;
		}
		$field = lcfirst($field);
		$value = $params[0];
		if (count($params) > 1) {
			//params[1] 操作符
			$field = $field.' '.$params[1];
		}
		$this->query->where($field, $value);
		return $this;
	}

	/**
	 *  找不到方法默认调用query
	 * @param $method
	 * @param $params
	 * @return mixed
	 */
	public function __call($method, $params) {

		if(starts_with($method, 'searchWith')) {
			return $this->doWhere(str_replace('searchWith', '', $method), $params);
		}

		if(starts_with($method, 'where')) {
			return $this->doWhere(str_replace('where', '', $method), $params);
		}

		if(starts_with($method, 'update')) {
			$field = lcfirst(str_replace('update', '', $method));
			$this->fill($field, $params[0]);
			return $this;
		}
		$result = call_user_func_array(array($this->query, $method), $params);
		if (in_array($method, array('get', 'getall'))) {
			return $result;
		}
		return $this;
	}
}