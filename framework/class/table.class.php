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

	const ONE_TO_ONE = 'ONE_TO_ONE';
	//表名
	protected $tableName = '';
	//主键
	protected $primaryKey = 'id';
	protected $field = array();
	// 字段验证规则
	protected $rule = array();
	// 字段默认值
	protected $default = array();
	
	protected $query;
	//数据库属性
	private $attribute = array();

	/**
	 *  关联关系
	 */
	protected $relation = array();


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


	/**
	 *  字段填充
	 * @param $field
	 * @param string $value
	 * @return $this
	 */
	public function fill($field, $value = '') {
		if (is_array($field)) {
			foreach ($field as $column => $val) {
				$this->fillField($column, $val);
			}
			return $this;
		}
		$this->fillField($field, $value);
		return $this;
	}

	/**
	 *  字段填充
	 * @param $column
	 * @param $val
	 */
	private function fillField($column, $val) {
		if (in_array($column, $this->field)) {
			$this->attribute[$column] = $val;
			$this->query->fill($column, $val);

		}
	}
	/**
	 *  根据主键获取数据
	 * @param $id
	 * @return mixed
	 */
	public function getById($id) {
		$query = $this->query->from($this->tableName)->where($this->primaryKey, $id);
		if (is_array($id)) {
			return $query->getall();
		}
		return $query->get();
	}

	private function doRelation($key) {
		$relation_param = $this->relation[$key];
		switch($relation_param['type']) {
			case 'ONE_TO_MANY' : return $this->oneToMany($relation_param); break;
			case 'ONE_TO_ONE' : return $this->oneToOne($relation_param); break;
			case 'MANY_TO_ONE' : return $this->manyToOne($relation_param); break;
			case 'MANY_TO_MANY' : return $this->manyTomany($relation_param);break;
		}
	}

	/**
	 *  执行 一对多
	 */
	private function oneToMany($param) {
		$datas = $this->getall($this->primaryKey);
		if (empty($datas)) {
			return array();
		}
		$table = $param['table']; 
		$foreign_key = $table['foreign_key'];
		$table_instance = table($table);
		return $table_instance->where($foreign_key, array_keys($datas))->getall();
	}

	/**
	 *  一对一
	 * @param $param
	 * @return array|mixed
	 */
	private function oneToOne($param) {
		$data = $this->get();
		if (empty($data)) {
			return array();
		}
		$owner_key = isset($param['owner_key']) ? $param['owner_key'] : $this->primaryKey;
		$table = $param['table'];
		$foreign_key = $param['foreign_key'];
		$frieign_val = $data[$owner_key];
		$table_instance = table($table);

		return $table_instance->where($foreign_key, $frieign_val)->get();
	}

	/**
	 *  执行 多对一
	 */
	private function manyToOne($param) {
		$datas = $this->getall($this->primaryKey);
		if (empty($datas)) {
			return array();
		}
		$foreign_keys = array_unique(array_keys($datas));
		$table = $param['table'];
		// 外键
		$foreign_key_field = $table['foreign_key'];
		$table_instance = table($table);
		return $table_instance->where($foreign_key_field, $foreign_keys)->getall();
	}


	private function manyToMany($param) {
		$center_table = $param['center_table']; //中间表
		return null;
	}


	public function __get($key) {

		if (isset($this->relation[$key])) {
			return $this->doRelation($key);
		}
	}

	
	/**
	 * 追加默认数据
	 */
	private function appendDefault() {
		foreach ($this->default as $field => $value) {
			if (! isset($this->attribute[$field])) {
				if ($value === 'custom') {
					$method = 'default'.$this->studly($field);
					if (! method_exists($this, $method)) {
						trigger_error($method.'方法未找到');
					}
					$value = call_user_func(array($this, $method));
				}
				$this->fillField($field, $value);
			}
		}
	}

	/**
	 *  获取字段所有验证规则
	 */
	protected function valid($data) {
		if (count($this->rule) <= 0) {
			return error(0);
		}

		$validator = Validator::create($data, $this->rule);
		$result = $validator->valid();
		return $result;
	}

	public function get() {
		$data = $this->query->get();
		return $data;
	}

	public function getall($keyfield = '') {
		$data = $this->query->getall($keyfield);
		return $data;
	}

	public function getcolumn($field = '') {
		$data = $this->query->getcolumn($field);
		return $data;
	}

	public function where($condition, $parameters = array(), $operator = 'AND') {
		$this->query->where($condition, $parameters, $operator);
		return $this;
	}

	public function whereor($condition, $parameters = array()) {
		return $this->where($condition, $parameters, 'OR');
	}
	/**
	 *  创建对象
	 */
	public function save() {
		// 更新不处理默认值
		if($this->query->hasWhere()) {
			$result = $this->valid($this->attribute);
			if (is_error($result)) {
				return $result;
			}
			return $this->query->update();
		}

		$this->appendDefault();
		$result = $this->valid($this->attribute);
		if (is_error($result)) {
			return $result;
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

	private function doWhere($field, $params, $operator = 'AND') {
		if ($params == 0) {
			return $this;
		}
		$value = $params[0];
		if (count($params) > 1) {
			//params[1] 操作符
			$field = $field.' '.$params[1];
		}
		$this->query->where($field, $value, $operator);
		return $this;
	}

	/**
	 *  HelloWord 转 hello_word
	 * @param $value
	 * @return mixed|string
	 */
	private function snake($value) {
		$delimiter = '_';
		if (! ctype_lower($value)) {
			$value = preg_replace('/\s+/u', '', ucwords($value));
			$value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
		}
		return $value;
	}

	/**
	 * hello_word 转HelloWord
	 * @return mixed
	 */
	private function studly($value) {
		$value = ucwords(str_replace(array('-', '_'), ' ', $value));
		return str_replace(' ', '', $value);
	}

	/**
	 *  找不到方法默认调用query
	 * @param $method
	 * @param $params
	 * @return mixed
	 * 
	 * 语法糖 -> where() fill()
	 * whereUsername('test')->delete()  ->get()...;
	 * searchWithUsername('test') ->get()
	 * fillUsername('test')->update()   ->insert()
	 * 
	 * 业务方法
	 * getByid()
	 * 
	 */
	public function __call($method, $params) {
		//whereor 必须在where 前边 否则 whereorAge 或被替换成 where('or_age',1)
		$actions = array(
			'searchWith',
			'whereor',
			'where',
			'fill'
		);
		foreach ($actions as $action) {
			$fields = explode($action, $method);
			if (count($fields) > 1 && empty($fields[0]) && !empty($fields[1])) {
				$field = $this->snake($fields[1]);
				switch ($action) {
					case 'whereor':
						return $this->doWhere($field, $params, 'OR');
					case 'fill' :
						$this->fill($field, $params[0]);
						return $this;
					default :
						return $this->doWhere($field, $params);
				}
			}
		}
		return $this;
	}
}