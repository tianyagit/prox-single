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
	const MANY_TO_MANY = 'MANY_TO_MANY';

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
		if (! $data || empty($data)) {
			return $data;
		}
		$this->loadRelation($data);
		return $data;
	}

	public function getall($keyfield = '') {
		$data = $this->query->getall($keyfield);
		if (! $data || empty($data)) {
			return $data;
		}
		$this->loadRelation($data, true);
		return $data;
	}

	/**
	 *  多对多需要使用内部query 对象
	 * @return Query
	 */
	public function getQuery() {
		return $this->query;
	}

	public function getTableName() {
		return $this->tableName;
	}
	/**
	 *  确定加载哪个关联关系
	 * @param $relation
	 */
	public function with($relation) {
		$this->relationDefine[] = $relation;
		return $this;
	}
	/**
	 *  加载关联关系
	 * @param array $data 查询出来的文字
	 * @param bool $muti 是否主查询是 多条记录
	 */
	private function loadRelation(array &$data, $muti = false) {
		foreach ($this->relationDefine as $relation) {
			$this->doload($relation, $data, $muti); //加载关联数据
		}
	}

	/**
	 *  加载关联表数据
	 * @param $relation
	 * @param $data
	 * @param bool $muti
	 */
	private function doload($relation, &$data, $muti = false) {
		if (method_exists($this, $relation)) {
			$relation_param = call_user_func(array($this, $relation));
			list($type, $table, $foreign_key, $owner_key) = $relation_param;
			if ($type == self::MANY_TO_MANY) {
				$this->doManyToMany($relation, $relation_param, $data, $muti);
				return;
			}
			/**
			 *  获取关联类型如果是单条数据
			 */
			$single = $this->isGetSingle($type);
			/**
			 * 如果执行是 table->getall() muti是true
			 * 获取所有的外键值
			 */
			$foreign_vals = $this->getForeignVal($data, $owner_key, $muti);
			/**
			 *  获取关联表的数据  $single 表示 只获取一条即可
			 */
			$second_table_data = $this->getSecondTableData($table, $foreign_key, $foreign_vals, $single);
			if (! $muti) {
				$data[$relation] = $second_table_data;
				return;
			}
			if ($single) {
				$second_table_data = array($second_table_data);
			}
			$second_table_data = $this->groupBy($foreign_key, $second_table_data);

			foreach ($data as &$item) {
				$relation_val = isset($second_table_data[$item[$owner_key]]) ? $second_table_data[$item[$owner_key]] : array();
				if ($single) {
					$relation_val = count($relation_val) > 0 ? current($relation_val) : array();
				}
				$item[$relation] =  $relation_val;
			}
		}
	}

	/**
	 *  改为join 方式查询
	 * @param $relation
	 * @param $relation_param
	 * @param $data
	 * @param bool $muti
	 */
	private function doManyToMany2($relation, $relation_param, &$data, $muti = false) {
		list($type, $table, $foreign_key, $owner_key, $center_table, $center_foreign_key, $center_owner_key)
			= $relation_param;


		$foreign_vals = $this->getForeignVal($data, $owner_key, $muti);
		$three_table = table($table);
		$nativeQuery = $three_table->getQuery();

		$nativeQuery->from($three_table->getTableName(), 'three')
			->join($center_table, 'center')
			->on(array('center.'.$center_foreign_key => 'three.'.$foreign_key))
			->select('center.*')
			->where($center_owner_key, $foreign_vals);

		$three_table_data = $three_table->getall(); //$three_table->getall();
		if (!$muti) {
			$data[$relation] = $three_table_data;
			return;
		}

		$three_table_data = $this->groupBy($center_owner_key, $three_table_data);
		/**
		 *  按组归类
		 */
		foreach ($data as &$item) {
			$three_val = isset($three_table_data[$item[$owner_key]]) ? $three_table_data[$item[$owner_key]] : array();
			$item[$relation] = $three_val;
		}


	}

	private function doManyToMany($relation, $relation_param, &$data, $muti = false) {
		list($type, $table, $foreign_key, $owner_key, $center_table, $center_foreign_key, $center_owner_key)
			= $relation_param;


		$foreign_vals = $this->getForeignVal($data, $owner_key, $muti);


//
		/**
		 * 获取中间表的数据
		 */
		$query = new Query();
		$center_table_data = $query->from($center_table)
			->where($center_owner_key, $foreign_vals)->getall();

		//获取 第三个表的 键值
		$center_keys = array_map(function($item) use ($center_foreign_key){
			return $item[$center_foreign_key];
		}, $center_table_data);
		/**
		 *  获取关联表的数据
		 */
		$second_table_data = table($table)->where($foreign_key, $center_keys)->getall($foreign_key);
		if (!$muti) {
			$data[$relation] = $second_table_data;
			return;
		}

		/**
		 *  中间表分组
		 */
		$center_group_data = $this->groupBy($center_owner_key, $center_table_data);

		/**
		 *  按组归类
		 */
		foreach ($data as &$item) {
			$master_table_key = $item[$owner_key];
			$center_val = isset($center_group_data[$master_table_key]) ? $center_group_data[$master_table_key] : array();
			$item[$relation] = array_map(function($center_item) use ($center_foreign_key, $second_table_data){
				$second_table_key = $center_item[$center_foreign_key];
				return isset($second_table_data[$second_table_key]) ? $second_table_data[$second_table_key] : array() ;
			}, $center_val);
		}
	}

	/**
	 *  是否获取单条数据
	 * @param $type
	 * @return bool
	 */
	private function isGetSingle($type) {
		return in_array($type, array(self::ONE_TO_ONE, self::BELONGS_TO)) ? true : false;
	}

	/**
	 *  获取所有外键值
	 * @param $data
	 * @param $owner_key
	 * @param bool $muti
	 * @return array
	 */
	private function getForeignVal($data, $owner_key, $muti = false) {
		if (! $muti) {
			return $data[$owner_key];
		}
		return array_map(function($item) use ($owner_key){
			return $item[$owner_key];
		}, $data);
	}

	/**
	 *  获取关联表数据
	 * @param $table
	 * @param $foreign_key
	 * @param $foreign_vals
	 * @param bool $single
	 * @return mixed
	 */
	private function getSecondTableData($table, $foreign_key, $foreign_vals, $single = false) {
		$table_instance = table($table)->where($foreign_key, $foreign_vals);
		if ($single) {
			return $table_instance->get();
		}
		return $table_instance->getall();
	}



	/**
	 * [
	['account_id' => 'account-x10', 'product' => 'Chair'],
	['account_id' => 'account-x10', 'product' => 'Bookcase'],
	['account_id' => 'account-x11', 'product' => 'Desk'],
	]);

	$grouped = $this->groupBy('account_id');
	/*
	[
	'account-x10' => [
	['account_id' => 'account-x10', 'product' => 'Chair'],
	['account_id' => 'account-x10', 'product' => 'Bookcase'],
	],
	'account-x11' => [
	['account_id' => 'account-x11', 'product' => 'Desk'],
	],
	]
	 * @param $key
	 * @param $array
	 */
	private function groupBy($key, $array) {
		$result = array();

		foreach ($array as $item) {
			$val = $item[$key];
			if (isset($result[$val])) {
				$result[$val][] = $item;
			} else {
				$result[$val] = array($item);
			}
		}
		return $result;
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
	 * @param $foreign_key 关联表ID
	 * @param bool $owner_key 不填默认主键
	 * @return array
	 */
	protected function belongsTo($table, $foreign_key, $owner_key = false) {
		return $this->relationArray(self::BELONGS_TO, $table, $foreign_key, $owner_key);
	}


	/**
	 * @param $table
	 * @param $center_table 中间表
	 * @param $foreign_key 关联表的键
	 * @param bool $owner_key 不填默认主键
	 * @param bool $center_foreign_key 不填默认 关联表的建
	 * @param bool $center_onwer_key  不填默认主键
	 * @return array
	 */
	protected function belongsMany($table, $foreign_key, $owner_key, $center_table, $center_foreign_key = false,
	                               $center_owner_key = false) {
		if (! $owner_key) {
			$owner_key = $this->primaryKey;
		}
		if (!$center_foreign_key) {
			$center_foreign_key = $foreign_key;
		}
		if (!$center_owner_key) {
			$center_owner_key = $owner_key;
		}
		return array(self::MANY_TO_MANY, $table, $foreign_key, $owner_key, $center_table, $center_foreign_key, $center_owner_key);
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