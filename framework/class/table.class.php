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
	const ONE_TO_MANY = 'ONE_TO_MANY';
	const BELONGS_TO = 'BELONGS_TO';

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
	 *  关联关系定义
	 * @var array
	 */
	protected $relationDefine = array();


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
	 *  获取关联数据
	 * @param $relation_param
	 * @return mixed
	 */
	private function getRelationData($relation_param) {
		list($type, $table, $foreign_key, $owner_key) = $relation_param;
		$datas = $this->getall($owner_key);
		$foreign_val = array_keys($datas);
		$table_instance = table($table)->where($foreign_key, $foreign_val);
		return $table_instance->getall();
	}


	public function __get($key) {
		//获取关联关系数据
		if (in_array($key, $this->relationDefine)) {
			if (method_exists($this, $key)) {
				$relation_define = call_user_func(array($this, $key));
				return $this->getRelationData($relation_define);
			}
		}
	}

	private function manyToMany($param) {
		trigger_error('未实现');
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
		$this->loadRelation($data);
		return $data;
	}

	public function getall($keyfield = '') {
		$data = $this->query->getall($keyfield);
		$this->loadRelation($data, true);
		return $data;
	}

	/**
	 *  加载关联关系
	 * @param array $data 查询出来的文字
	 * @param bool $muti 是否主查询是 多条记录
	 */
	private function loadRelation(array &$data, $muti = false) {
		foreach ($this->relationDefine as $relation) {
			if (! $muti) {
				$this->loadOne($data, $relation);
			} else {
				$this->loadMuti($data, $relation);
			}

		}
	}



	/**
	 * 加载一条数据
	 * @param $data
	 * @param $relation
	 */
	private function loadOne(&$data, $relation, $muti = false) {
		if (method_exists($this, $relation)) {
			$relation_param = call_user_func(array($this, $relation));
			list($type, $table, $foreign_key, $owner_key) = $relation_param;
			$getall = false;
			switch ($type) {
				case self::ONE_TO_ONE : break;
				case self::ONE_TO_MANY : $getall = true; break;
				case self::BELONGS_TO : $getall = false; break;
			}
			$foreign_val = $data[$owner_key];
			$table_instance = table($table)->where($foreign_key, $foreign_val);
			if ($getall) {
				$data[$relation] = $table_instance->getall();
				return;
			}
			$data[$relation] = $table_instance->get();

		}
	}

	/**
	 * 加载多条数据 的对应关系
	 * @param $data
	 * @param $relation
	 */
	private function loadMuti($data, $relation) {
		if (method_exists($this, $relation)) {
			$relation_param = call_user_func(array($this, $relation));
			list($type, $table, $foreign_key, $owner_key) = $relation_param;
			$foreign_vals = array_map(function($item) use ($owner_key){
				return $item[$owner_key];
			}, $data);

			$table_instance = table($table)->where($foreign_key, $foreign_vals);
			$foreign_datas = $table_instance->getall();


			foreach ($foreign_datas as $foreign_data) {
				$foreign_data[$foreign_key];
			}

			foreach ($data as $item) {


			}

		}
	}

	private function doRelation($relation_param, $data) {
		// 第0个表示 type 类型
		switch(current($relation_param)) {
			case self::ONE_TO_ONE :  return $this->oneToOne($relation_param, $data); break;
			case self::ONE_TO_MANY : return $this->oneToMany($relation_param); break;
			case self::BELONGS_TO : return $this->belongTo($relation_param); break;
		}
	}


	/**
	 *  一对一
	 * @param $param
	 * @return array|mixed
	 */
	private function oneToOne($relation_param) {
		return $this->getRelationData($relation_param);
	}

	/**
	 *  执行 一对多
	 */
	private function oneToMany($relation_param) {
		return $this->getRelationData($relation_param);
	}

	/**
	 * 反向关联
	 * @param $relation_param
	 * @return mixed
	 */
	private function belongTo($relation_param) {
		return $this->getRelationData($relation_param);
	}
	/**
	 *  一对一
	 * @param $table
	 * @param $foreign_key
	 * @param bool $owner_key
	 */
	protected function hasOne($table, $foreign_key, $owner_key = false) {
		return $this->relationArray(self::ONE_TO_ONE, $table, $foreign_key, $owner_key);
	}

	/**
	 *  一对多
	 * @param $table
	 * @param $foreign_key
	 * @param bool $owner_key
	 * @return array
	 */
	protected function hasMany($table, $foreign_key, $owner_key = false) {
		return $this->relationArray(self::ONE_TO_MANY, $table, $foreign_key, $owner_key);
	}

	/**
	 *  反向关联
	 * @param $table
	 * @param $foreign_key
	 * @param bool $owner_key
	 * @return array
	 */
	protected function belongsTo($table, $foreign_key, $owner_key = false) {
		return $this->relationArray(self::BELONGS_TO, $table, $foreign_key, $owner_key);
	}

	/**
	 *  定义关联数据
	 * @param $type
	 * @param $table
	 * @param $foreign_key
	 * @param $owner_key
	 * @return array
	 */
	private function relationArray($type, $table, $foreign_key, $owner_key) {
		if (! $owner_key) {
			$owner_key = $this->primaryKey;
		}
		if (!in_array($type, array(self::ONE_TO_ONE, self::ONE_TO_MANY, self::BELONGS_TO), true)) {
			trigger_error('不支持的关联类型');
		}
		return array($type, $table, $foreign_key, $owner_key);
	}

	/**
	 *  根据主键获取数据
	 * @param $id
	 * @return mixed
	 */
	public function getById($id) {
		$this->query->from($this->tableName)->where($this->primaryKey, $id);
		if (is_array($id)) {
			return $this->getall();
		}
		return $this->get();
	}

	public function getcolumn($field = '') {
		$data = $this->query->getcolumn($field);
		return $data;
	}

	/**
	 *  拦截where 条件
	 * @param $condition
	 * @param array $parameters
	 * @param string $operator
	 * @return $this
	 */
	public function where($condition, $parameters = array(), $operator = 'AND') {
		$this->query->where($condition, $parameters, $operator);
		return $this;
	}

	/**
	 * where or
	 * @param $condition
	 * @param array $parameters
	 * @return We7Table
	 */
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