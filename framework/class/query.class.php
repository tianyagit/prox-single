<?php
/**
 * SQL构造助手
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 
 * @method Query groupby($field)
 * @method Query having($condition, $parameters = array())
 * @method Query where($condition, $parameters = array())
 * @method Query leftjoin($tablename, $alias = '')
 * @method Query innerjoin($tablename, $alias = '')
 * @method Query orderby($field, $direction = 'ASC')
 *
 */
class Query {
	//所有支持的SQL关键字
	private $clauses;
	//SQL关键字值
	private $statements = array();
	//参数绑定
	private $parameters = array();
	//主表名
	private $mainTable = '';
	//主表别名
	private $currentTableAlias = '';
	private $limitQueryTotal = 0;
	private $error = array();
	private $lastsql = '';
	
	public function __construct($db) {
		$this->db = $db;
		$this->initClauses();
	}
	
	private function initClauses() {
		$this->clauses = array(
			'SELECT' => array(),
			'DELETE' => '',
			'UPDATE' => '',
			'INSERT INTO' => '',
			
			'FROM' => '',
			'LEFTJOIN' => array(),
			'INNERJOIN' => array(),
			'ON' => array(),
			'SET' => '',
			'WHERE' => array(),
			'GROUPBY' => array(),
			'HAVING' => array(),
			'ORDERBY' => array(),
			'LIMIT' => '',
		);
		foreach ($this->clauses as $clause => $value) {
			$this->statements[$clause] = $value;
		}
	}
	
	private function resetClause($clause = '') {
		if (empty($clause)) {
			$this->initClauses();
			return $this;
		}
		$this->statements[$clause] = null;
		$this->parameters = array();
		if (isset($this->clauses[$clause]) && $this->clauses[$clause]) {
			$this->statements[$clause] = array();
		}
		return $this;
	}
	

	private function addStatement($clause, $statement, $parameters = array()) {
		if ($statement === null) {
			return $this->resetClause($clause);
		}
		//为数组时代表可以同时设置多个项
		if (isset($this->statements[$clause]) && is_array($this->statements[$clause])) {
			if (is_array($statement)) {
				$this->statements[$clause] = array_merge($this->statements[$clause], $statement);
			} else {
				if (empty($parameters) && is_array($parameters)) {
					$this->statements[$clause][] = $statement;
				} else {
					$this->statements[$clause][$statement] = empty($parameters) && is_array($parameters) ? '' : $parameters;
				}
			}
		} else {
			$this->statements[$clause] = $statement;
		}
		
		return $this;
	}
	
	public function __call($clause, $statement = array()) {
		$origin_clause = $clause;
		$clause = strtoupper($clause);
		
		if ($clause == 'WHERE' || $clause == 'HAVING') {
			array_unshift($statement, $clause);
			return call_user_func_array(array($this, 'condition'), $statement);
		}
		
		if ($clause == 'LEFTJOIN' || $clause == 'INNERJOIN') {
			array_unshift($statement, $clause);
			return call_user_func_array(array($this, 'join'), $statement);
		}
		
		//$statement = array_shift($statement);
		//if (strpos($clause, 'JOIN') !== false) {
			//return $this->addJoinStatements($clause, $statement, $parameters);
		//}
		return $this->addStatement($clause, $statement);
	}
	
	public function from($tablename, $alias = '') {
		$this->resetClause();
		
		if (empty($tablename)) {
			return $this;
		}
		$this->mainTable = $tablename;
		$this->currentTableAlias = $alias;
		
		$this->statements['FROM'] = $this->mainTable;
		$this->statements['SELECT'][] = '*';
		
		return $this;
	}
	
	public function join($clause, $tablename, $alias = '') {
		if (empty($tablename)) {
			return $this;
		}
		$this->joinTable = $tablename;
		return $this->addStatement($clause, $tablename . ' ' .$alias);
	}
	
	public function on($condition, $parameters = array()) {
		if ($condition === null) {
			return $this->resetClause('ON');
		}
		if (empty($condition)) {
			return $this;
		}
		if (is_array($condition)) {
			foreach ($condition as $key => $val) {
				$this->on($key, $val);
			}
			return $this;
		}
		if (empty($this->statements['ON'][$this->joinTable])) {
			$this->statements['ON'][$this->joinTable] = array();
		}
		$this->statements['ON'][$this->joinTable][$condition] = $parameters;
		return $this;
	}
	
	public function select($field) {
		if (is_string($field)) {
			$field = func_get_args();
		}
		
		if (empty($field)) {
			return $this;
		}
		//去掉默认的select * 
		if (count($this->statements['SELECT']) == 1) {
			$this->resetClause('SELECT');
		}
		return $this->addStatement('SELECT', $field);
	}
	
	/**
	 * 构造条件
	 * @param <string|array> $condition
	 * 			条件与Pdo_get中相同
	 * @param array $parameters
	 */
	private function condition($operator, $condition, $parameters = array(), $glue = 'AND') {
		if ($condition === null) {
			return $this->resetClause('WHERE');
		}
		if (empty($condition)) {
			return $this;
		}
		if (is_array($condition)) {
			foreach ($condition as $key => $val) {
				$this->condition($key, $val);
			}
			return $this;
		}
		
		return $this->addStatement($operator, $condition, $parameters);
	}
	
	public function orderby($field, $direction = 'ASC') {
		if (is_array($field)) {
			foreach ($field as $column => $order) {
				$this->orderby($column, $order);
			}
			return $this;
		}
		$direction = strtoupper($direction);
		$direction = in_array($direction, array('ASC', 'DESC')) ? $direction : 'ASC';
	
		return $this->addStatement('ORDERBY', $field . ' ' . $direction);
	}
	
	public function get() {
		$this->lastsql = $this->buildQuery();
		$result = pdo_fetch($this->lastsql, $this->parameters);
		return $result;
	}
	
	public function getcolumn($field = '') {
		$this->lastsql = $this->buildQuery();
		$result = pdo_fetchcolumn($this->lastsql, $this->parameters, $field);
		return $result;
	}
	
	public function getall($keyfield = '') {
		$this->lastsql = $this->buildQuery();
		$result = pdo_fetchall($this->lastsql, $this->parameters, $keyfield);
		return $result;
	}
	
	/**
	 * 一般用于获取分页后总记录条数
	 */
	public function lastcount() {
		
	}
	
	public function count() {
		return pdo_count($this->statements['FROM'], $this->statements['WHERE']);
	}
	
	public function exists() {
		return pdo_exists($this->statements['FROM'], $this->statements['WHERE']);
	}
	
	private function buildQuery() {
		$query = '';
		foreach ($this->clauses as $clause => $separator) {
			if (!empty($this->statements[$clause])) {
				if (method_exists($this, 'buildQuery' . $clause)) {
					$query .= call_user_func(array($this, 'buildQuery' . $clause), $this->statements[$clause]);
				} elseif (is_string($separator)) {
					$query .= " $clause " . implode($separator, $this->statements[$clause]);
				} elseif ($separator === null) {
					$query .= " $clause " . $this->statements[$clause];
				}
			}
		}
		print_r($query);
		print_r($this->statements);exit;
		return trim($query);
	}
	
	private function buildQueryWhere() {
		$where = \SqlPaser::parseParameter($this->statements['WHERE'], 'AND', $this->currentTableAlias);
		$this->parameters = array_merge($this->parameters, $where['params']);
		return empty($where['fields']) ? '' : " WHERE {$where['fields']} ";
	}
	
	private function buildQueryHaving() {
		$where = \SqlPaser::parseParameter($this->statements['HAVING'], 'AND', $this->currentTableAlias);
		$this->parameters = array_merge($this->parameters, $where['params']);
		return empty($where['fields']) ? '' : " HAVING {$where['fields']} ";
	}
	
	private function buildQueryFrom() {
		return " FROM " . tablename($this->statements['FROM']) . ' ' . $this->currentTableAlias;
	}
	
	private function buildQueryLeftjoin() {
		return $this->buildQueryJoin('LEFTJOIN');
	}
	
	private function buildQueryInnerjoin() {
		return $this->buildQueryLeftjoin('INNERJOIN');
	}
	
	private function buildQueryJoin($clause) {
		if (empty($this->statements[$clause])) {
			return '';
		}
		$clause_operator = array(
			'LEFTJOIN' => ' LEFT JOIN ',
			'INNERJOIN' => ' INNER JOIN ',
		);
		$sql = '';
		foreach ($this->statements[$clause] as $tablename) {
			list($tablename, $alias) = explode(' ', $tablename);
			$sql .=  $clause_operator[$clause] . tablename($tablename) . ' ' . $alias;
			if (!empty($this->statements['ON'][$tablename])) {
				$sql .= " ON ";
				$split = "";
				foreach ($this->statements['ON'][$tablename] as $field => $condition) {
					list($field, $operator) = explode(' ', $field);
					$operator = $operator ? $operator : '=';
						
					$field = '`' . str_replace('.', '`.`', $field) . '`';
					if (strexists($condition, '.')) {
						$condition = '`' . str_replace('.', '`.`', $condition) . '`';
					}
					$sql .= " $split $field $operator $condition ";
					$split = " AND ";
				}
			}
		}
		return $sql;
	}
	
	private function buildQuerySelect() {
		return \SqlPaser::parseSelect($this->statements['SELECT'], $this->currentTableAlias);
	}
	
	private function buildQueryLimit() {
		return \SqlPaser::parseLimit($this->statements['LIMIT']);
	}
	
	private function buildQueryOrderby() {
		return \SqlPaser::parseOrderby($this->statements['ORDERBY'], $this->currentTableAlias);
	}
	
	private function buildQueryGroupby() {
		return \SqlPaser::parseGroupby($this->statements['GROUPBY'], $this->currentTableAlias);
	}
	
	public function getlastsql() {
		return array($this->lastsql, $this->parameters);
	}
}