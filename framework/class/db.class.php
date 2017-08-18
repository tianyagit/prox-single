<?php
/**
 * 数据库操作类
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
define('PDO_DEBUG', true);

class DB {
	protected $pdo;
	protected $cfg;
	protected $tablepre;
	protected $result;
	protected $statement;
	protected $errors = array();
	protected $link = array();

	public function getPDO() {
		return $this->pdo;
	}

	public function __construct($name = 'master') {
		global $_W;
		$this->cfg = $_W['config']['db'];
		$this->connect($name);
	}

	public function connect($name = 'master') {
		if(is_array($name)) {
			$cfg = $name;
		} else {
			$cfg = $this->cfg[$name];
		}
		$this->tablepre = $cfg['tablepre'];
		if(empty($cfg)) {
			exit("The master database is not found, Please checking 'data/config.php'");
		}
		$dsn = "mysql:dbname={$cfg['database']};host={$cfg['host']};port={$cfg['port']};charset={$cfg['charset']}";
		$dbclass = '';
		$options = array();
		if (class_exists('PDO')) {
			if (extension_loaded("pdo_mysql") && in_array('mysql', PDO::getAvailableDrivers())) {
				$dbclass = 'PDO';
				$options = array(PDO::ATTR_PERSISTENT => $cfg['pconnect']);
			} else {
				if(!class_exists('_PDO')) {
					include IA_ROOT . '/framework/library/pdo/PDO.class.php';
				}
				$dbclass = '_PDO';
			}
		} else {
			include IA_ROOT . '/framework/library/pdo/PDO.class.php';
			$dbclass = 'PDO';
		}
		$this->pdo = new $dbclass($dsn, $cfg['username'], $cfg['password'], $options);
		//$this->pdo->setAttribute(pdo::ATTR_EMULATE_PREPARES, false);
		$sql = "SET NAMES '{$cfg['charset']}';";
		$this->pdo->exec($sql);
		$this->pdo->exec("SET sql_mode='';");
		if(is_string($name)) {
			$this->link[$name] = $this->pdo;
		}
		
		$this->logging($sql);
	}

	public function prepare($sql) {
		$sqlsafe = SqlPaser::checkquery($sql);
		if (is_error($sqlsafe)) {
			trigger_error($sqlsafe['message'], E_USER_ERROR);
			return false;
		}
		$statement = $this->pdo->prepare($sql);
		$this->logging($sql);
		return $statement;
	}

	/**
	 * 执行一条非查询语句
	 *
	 * @param string $sql
	 * @param array or string $params
	 * @return mixed
	 *		  成功返回受影响的行数
	 *		  失败返回FALSE
	 */
	public function query($sql, $params = array()) {
		$sqlsafe = SqlPaser::checkquery($sql);
		if (is_error($sqlsafe)) {
			trigger_error($sqlsafe['message'], E_USER_ERROR);
			return false;
		}
		//为了不影响 last insertid 把缓存提前执行，可能插入失败后也会清空缓存
		if (in_array(strtolower(substr($sql, 0, 6)), array('update', 'delete', 'insert', 'replac'))) {
			$this->cacheNameSpace($sql, true);
		}
		$starttime = microtime();
		if (empty($params)) {
			$result = $this->pdo->exec($sql);
			$this->logging($sql);
			return $result;
		}
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		
		$this->logging($sql, $params);
		
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			return $statement->rowCount();
		}
	}

	/**
	 * 执行SQL返回第一个字段
	 *
	 * @param string $sql
	 * @param array $params
	 * @param int $column 返回查询结果的某列，默认为第一列
	 * @return mixed
	 */
	public function fetchcolumn($sql, $params = array(), $column = 0) {
		$cachekey = $this->cacheKey($sql, $params);
		if (($cache = $this->cacheRead($cachekey)) !== false) {
			return $cache['data'];
		}
		$starttime = microtime();
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		
		$this->logging($sql, $params);
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			$data = $statement->fetchColumn($column);
			$this->cacheWrite($cachekey, $data);
			return $data;
		}
	}

	/**
	 * 执行SQL返回第一行
	 *
	 * @param string $sql
	 * @param array $params
	 * @return mixed
	 */
	public function fetch($sql, $params = array()) {
		$cachekey = $this->cacheKey($sql, $params);
		if (($cache = $this->cacheRead($cachekey)) !== false) {
			return $cache['data'];
		}
		$starttime = microtime();
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		
		$this->logging($sql, $params);
		
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			$data = $statement->fetch(pdo::FETCH_ASSOC);
			$this->cacheWrite($cachekey, $data);
			return $data;
		}
	}

	/**
	 * 执行SQL返回全部记录
	 *
	 * @param string $sql
	 * @param array $params
	 * @return mixed
	 */
	public function fetchall($sql, $params = array(), $keyfield = '') {
		$cachekey = $this->cacheKey($sql, $params);
		if (($cache = $this->cacheRead($cachekey)) !== false) {
			return $cache['data'];
		}
		$starttime = microtime();
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		
		$this->logging($sql, $params);
		
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			if (empty($keyfield)) {
				$result = $statement->fetchAll(pdo::FETCH_ASSOC);
			} else {
				$temp = $statement->fetchAll(pdo::FETCH_ASSOC);
				$result = array();
				if (!empty($temp)) {
					foreach ($temp as $key => &$row) {
						if (isset($row[$keyfield])) {
							$result[$row[$keyfield]] = $row;
						} else {
							$result[] = $row;
						}
					}
				}
			}
			$this->cacheWrite($cachekey, $result);
			return $result;
		}
	}

	public function get($tablename, $params = array(), $fields = array(), $orderby = array()) {
		$select = SqlPaser::parseSelect($fields);
		$condition = SqlPaser::parseParameter($params, 'AND');
		$orderbysql = SqlPaser::parseOrderby($orderby);

		$sql = "{$select} FROM " . $this->tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . " $orderbysql LIMIT 1";
		return $this->fetch($sql, $condition['params']);
	}

	public function getall($tablename, $params = array(), $fields = array(), $keyfield = '', $orderby = array(), $limit = array()) {
		$select = SqlPaser::parseSelect($fields);
		$condition = SqlPaser::parseParameter($params, 'AND');

		$limitsql = SqlPaser::parseLimit($limit);
		$orderbysql = SqlPaser::parseOrderby($orderby);

		$sql = "{$select} FROM " .$this->tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . $orderbysql . $limitsql;
		return $this->fetchall($sql, $condition['params'], $keyfield);
	}

	public function getslice($tablename, $params = array(), $limit = array(), &$total = null, $fields = array(), $keyfield = '', $orderby = array()) {
		$select = SqlPaser::parseSelect($fields);
		$condition = SqlPaser::parseParameter($params, 'AND');
		$limitsql = SqlPaser::parseLimit($limit);

		if (!empty($orderby)) {
			if (is_array($orderby)) {
				$orderbysql = implode(',', $orderby);
			} else {
				$orderbysql = $orderby;
			}
		}
		$sql = "{$select} FROM " . $this->tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . (!empty($orderbysql) ? " ORDER BY $orderbysql " : '') . $limitsql;
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : ''), $condition['params']);
		return $this->fetchall($sql, $condition['params'], $keyfield);
	}

	public function getcolumn($tablename, $params = array(), $field = '') {
		$result = $this->get($tablename, $params, $field);
		if (!empty($result)) {
			if (strexists($field, '(')) {
				return array_shift($result);
			} else {
				return $result[$field];
			}
		} else {
			return false;
		}
	}

	/**
	 * 更新记录
	 *
	 * @param string $table
	 * @param array $data
	 *		要更新的数据数组
	 *			array(
	 *				'字段名' => '值'
	 *			)
	 * @param array $params
	 *			更新条件
	 *			array(
	 *				'字段名' => '值'
	 *			)
	 * @param string $glue
	 *			可以为AND OR
	 * @return mixed
	 */
	public function update($table, $data = array(), $params = array(), $glue = 'AND') {
		$fields = SqlPaser::parseParameter($data, ',');
		$condition = SqlPaser::parseParameter($params, $glue);
		$params = array_merge($fields['params'], $condition['params']);
		$sql = "UPDATE " . $this->tablename($table) . " SET {$fields['fields']}";
		$sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
		return $this->query($sql, $params);
	}

	/**
	 * 更新记录
	 *
	 * @param string $table
	 * @param array $data
	 *		要更新的数据数组
	 *		array(
	 *			'字段名' => '值'
	 *		)
	 * @param boolean $replace
	 *		是否执行REPLACE INTO
	 *		默认为FALSE
	 * @return mixed
	 */
	public function insert($table, $data = array(), $replace = FALSE) {
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$condition = SqlPaser::parseParameter($data, ',');
		return $this->query("$cmd " . $this->tablename($table) . " SET {$condition['fields']}", $condition['params']);
	}

	/**
	 * 返回lastInsertId
	 *
	 */
	public function insertid() {
		return $this->pdo->lastInsertId();
	}

	/**
	 * 删除记录
	 *
	 * @param string $table
	 * @param array $params
	 *		更新条件
	 *		array(
	 *			'字段名' => '值'
	 *		)
	 * @param string $glue
	 *		可以为AND OR
	 * @return mixed
	 */
	public function delete($table, $params = array(), $glue = 'AND') {
		$condition = SqlPaser::parseParameter($params, $glue);
		$sql = "DELETE FROM " . $this->tablename($table);
		$sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
		return $this->query($sql, $condition['params']);
	}
	
	/**
	 * 检测一条记录是否存在
	 * @param unknown $tablename
	 * @param array $params
	 */
	public function exists($tablename, $params = array()) {
		$row = $this->get($tablename, $params);
		if (empty($row) || !is_array($row) || count($row) == 0) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * 
	 * @param unknown $tablename
	 * @param array $params
	 */
	public function count($tablename, $params = array(), $cachetime = 30) {
		$total = pdo_getcolumn($tablename, $params, 'count(*)');
		return intval($total);
	}

	/**
	 * 启动一个事务，关闭自动提交
	 *
	 */
	public function begin() {
		$this->pdo->beginTransaction();
	}

	/**
	 * 提交一个事务，恢复自动提交
	 * @return boolean
	 */
	public function commit() {
		$this->pdo->commit();
	}

	/**
	 * 回滚一个事务，恢复自动提交
	 * @return boolean
	 */
	public function rollback() {
		$this->pdo->rollBack();
	}

	/**
	 * 执行SQL文件
	 */
	public function run($sql, $stuff = 'ims_') {
		if(!isset($sql) || empty($sql)) return;

		$sql = str_replace("\r", "\n", str_replace(' ' . $stuff, ' ' . $this->tablepre, $sql));
		$sql = str_replace("\r", "\n", str_replace(' `' . $stuff, ' `' . $this->tablepre, $sql));
		$ret = array();
		$num = 0;
		$sql = preg_replace("/\;[ \f\t\v]+/", ';', $sql);
		foreach(explode(";\n", trim($sql)) as $query) {
			$ret[$num] = '';
			$queries = explode("\n", trim($query));
			foreach($queries as $query) {
				$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
			}
			$num++;
		}
		unset($sql);
		foreach($ret as $query) {
			$query = trim($query);
			if($query) {
				$this->query($query, array());
			}
		}
	}

	/**
	 * 查询字段是否存在
	 * 成功返回TRUE，失败返回FALSE
	 *
	 * @param string $tablename
	 * 		查询表名
	 * @param string $fieldname
	 * 		查询字段名
	 * @return boolean
	 */
	public function fieldexists($tablename, $fieldname) {
		$isexists = $this->fetch("DESCRIBE " . $this->tablename($tablename) . " `{$fieldname}`", array());
		return !empty($isexists) ? true : false;
	}

	/**
	 * 查询字段类型是否匹配
	 * 成功返回TRUE，失败返回FALSE，字段存在，但类型错误返回-1
	 *
	 * @param string $tablename
	 * 		查询表名
	 * @param string $fieldname
	 * 		查询字段名
	 * @param string $datatype
	 * 		查询字段类型
	 * @param string $length
	 * 		查询字段长度
	 * @return boolean
	 */
	public function fieldmatch($tablename, $fieldname, $datatype = '', $length = '') {
		$datatype = strtolower($datatype);
		$field_info = $this->fetch("DESCRIBE " . $this->tablename($tablename) . " `{$fieldname}`", array());
		if (empty($field_info)) {
			return false;
		}
		if (!empty($datatype)) {
			$find = strexists($field_info['Type'], '(');
			if (empty($find)) {
				$length = '';
			}
			if (!empty($length)) {
				$datatype .= ("({$length})");
			}
			return strpos($field_info['Type'], $datatype) === 0 ? true : -1;
		}
		return true;
	}

	/**
	 * 查询索引是否存在
	 * 成功返回TRUE，失败返回FALSE
	 * @param string $tablename
	 * 		查询表名
	 * @param array $indexname
	 * 		查询索引名
	 * @return boolean
	 */
	public function indexexists($tablename, $indexname) {
		if (!empty($indexname)) {
			$indexs = $this->fetchall("SHOW INDEX FROM " . $this->tablename($tablename), array(), '');
			if (!empty($indexs) && is_array($indexs)) {
				foreach ($indexs as $row) {
					if ($row['Key_name'] == $indexname) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * 返回完整数据表名(加前缀)(返回是主库的数据表前缀+表明)
	 * @param string $table 表名
	 * @return string
	 */
	public function tablename($table) {
		return "`{$this->tablepre}{$table}`";
	}

	/**
	 * 获取pdo操作错误信息列表
	 * @param bool $output 是否要输出执行记录和执行错误信息
	 * @param array $append 加入执行信息，如果此参数不为空则 $output 参数为 false
	 * @return array
	 */
	public function debug($output = true, $append = array()) {
		if(!empty($append)) {
			$output = false;
			array_push($this->errors, $append);
		}
		if($output) {
			print_r($this->errors);
		} else {
			if (!empty($append['error'][1])) {
				$traces = debug_backtrace();
				$ts = '';
				foreach($traces as $trace) {
					$trace['file'] = str_replace('\\', '/', $trace['file']);
					$trace['file'] = str_replace(IA_ROOT, '', $trace['file']);
					$ts .= "file: {$trace['file']}; line: {$trace['line']}; <br />";
				}
				$params = var_export($append['params'], true);
				if (!function_exists('message')) {
					load()->web('common');
					load()->web('template');
				}
				WeUtility::logging('SQL Error', "SQL: <br/>{$append['sql']}<hr/>Params: <br/>{$params}<hr/>SQL Error: <br/>{$append['error'][2]}<hr/>Traces: <br/>{$ts}");
				trigger_error("SQL: <br/>{$append['sql']}<hr/>Params: <br/>{$params}<hr/>SQL Error: <br/>{$append['error'][2]}<hr/>Traces: <br/>{$ts}", E_USER_WARNING);
			}
		}
		return $this->errors;
	}
	
	private function logging($sql, $params = array(), $message = '') {
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['params'] = $params;
			$info['error'] = empty($message) ? $this->pdo->errorInfo() : '';
			$this->debug(false, $info);
		}
		return true;
	}

	/**
	 * 判断某个数据表是否存在
	 * @param string $table 表名（不加表前缀）
	 * @return bool
	 */
	public function tableexists($table) {
		if(!empty($table)) {
			$data = $this->fetch("SHOW TABLES LIKE '{$this->tablepre}{$table}'", array());
			if(!empty($data)) {
				$data = array_values($data);
				$tablename = $this->tablepre . $table;
				if(in_array($tablename, $data)) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	private function performance($sql, $runtime = 0) {
		global $_W;
		if ($runtime == 0) {
			return false;
		}
		if (strexists($sql, 'core_performance')) {
			return false;
		}
		//将超时SQL语句存入数据库
		if (empty($_W['config']['setting']['maxtimesql'])) {
			$_W['config']['setting']['maxtimesql'] = 5;
		}
		if ($runtime > $_W['config']['setting']['maxtimesql']) {
			$sqldata = array(
				'type' => '2',
				'runtime' => $runtime,
				'runurl' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
				'runsql' => $sql,
				'createtime' => time()
			);
			$this->insert('core_performance', $sqldata);
		}
		return true;
	}

	private function cacheRead($cachekey) {
		global $_W;
		if (empty($cachekey) || $_W['config']['setting']['cache'] != 'memcache' || empty($_W['config']['setting']['memcache']['sql'])) {
			return false;
		}
		$data = cache_read($cachekey, true);
		if (empty($data) || empty($data['data'])) {
			return false;
		}
		return $data;
	}

	private function cacheWrite($cachekey, $data) {
		global $_W;
		if (empty($data) || empty($cachekey) || $_W['config']['setting']['cache'] != 'memcache' || empty($_W['config']['setting']['memcache']['sql'])) {
			return false;
		}
		$cachedata = array(
			'data' => $data,
			'expire' => TIMESTAMP + 2592000,
		);
		cache_write($cachekey, $cachedata, 0, true);
		return true;
	}

	private function cacheKey($sql, $params) {
		global $_W;
		if ($_W['config']['setting']['cache'] != 'memcache' || empty($_W['config']['setting']['memcache']['sql'])) {
			return false;
		}
		$namespace = $this->cacheNameSpace($sql);
		if (empty($namespace)) {
			return false;
		}
		return $namespace . ':' . md5($sql . serialize($params));
	}

	/**
	 * SQL缓存以表为为单位增加缓存命名空间，当更新、删除或是插入语句时批量删除此表的缓存
	 * @param string $sql
	 * @param boolean $forcenew 是否强制更新命名空间
	 */
	private function cacheNameSpace($sql, $forcenew = false) {
		global $_W;
		if ($_W['config']['setting']['cache'] != 'memcache' || empty($_W['config']['setting']['memcache']['sql'])) {
			return false;
		}
		$skip_tablename = array(
			$this->tablename('core_cache'),
			$this->tablename('core_queue'),
			$this->tablename('mc_member'),
			$this->tablename('mc_mapping_fans'),
		);
		//获取SQL中的表名
		$table_prefix = str_replace('`', '', tablename(''));
		preg_match_all('/(?!from|insert into|replace into|update) `?('.$table_prefix.'[a-zA-Z0-9_-]+)/i', $sql, $match);
		$tablename = implode(':', $match[1]);
		if (empty($tablename) || in_array("`{$tablename}`", $skip_tablename)) {
			return false;
		}
		$tablename = str_replace($this->tablepre, '', $tablename);
		//获取命名空间
		$db_cache_key = 'we7:dbkey:'.$tablename;
		$namespace = $this->getColumn('core_cache', array('key' => $db_cache_key), 'value');
		if (empty($namespace) || $forcenew) {
			$namespace = random(8);
			$this->delete('core_cache', array('key LIKE' => "%{$tablename}%", 'key !=' => $db_cache_key));
			$this->insert('core_cache', array('key' => $db_cache_key, 'value' => $namespace), true);
		}
		return $tablename . ':' . $namespace;
	}
}

/**
 * 格式化SQL语句
 *
 */
class SqlPaser {
	private static $checkcmd = array('SELECT', 'UPDATE', 'INSERT', 'REPLAC', 'DELETE');
	private static $disable = array(
		'function' => array('load_file', 'floor', 'hex', 'substring', 'if', 'ord', 'char', 'pi', 'benchmark', 'reverse', 'strcmp', 'datadir', 'updatexml', 'extractvalue', 'name_const', 'multipoint', 'database', 'user'),
		'action' => array('@', 'intooutfile', 'intodumpfile', 'unionselect', 'uniondistinct', 'information_schema', 'current_user', 'current_date'),
		'note' => array('/*','*/','#','--'),
	);

	public static function checkquery($sql) {
		$cmd = strtoupper(substr(trim($sql), 0, 6));
		if (in_array($cmd, self::$checkcmd)) {
			$mark = $clean = '';
			$sql = str_replace(array('\\\\', '\\\'', '\\"', '\'\''), '', $sql);
			if (strpos($sql, '/') === false && strpos($sql, '#') === false && strpos($sql, '-- ') === false && strpos($sql, '@') === false && strpos($sql, '`') === false) {
				$cleansql = preg_replace("/'(.+?)'/s", '', $sql);
			} else {
				$cleansql = self::stripSafeChar($sql);
			}

			$cleansql = preg_replace("/[^a-z0-9_\-\(\)#\*\/\"]+/is", "", strtolower($cleansql));
			if (is_array(self::$disable['function'])) {
				foreach (self::$disable['function'] as $fun) {
					if (strpos($cleansql, $fun . '(') !== false) {
						return error(1, 'SQL中包含禁用函数 - ' . $fun);
					}
				}
			}

			if (is_array(self::$disable['action'])) {
				foreach (self::$disable['action'] as $action) {
					if (strpos($cleansql, $action) !== false) {
						return error(2, 'SQL中包含禁用操作符 - ' . $action);
					}
				}
			}

			if (is_array(self::$disable['note'])) {
				foreach (self::$disable['note'] as $note) {
					if (strpos($cleansql, $note) !== false) {
						return error(3, 'SQL中包含注释信息');
					}
				}
			}
		} elseif (substr($cmd, 0, 2) === '/*') {
			return error(3, 'SQL中包含注释信息');
		}
	}

	private static function stripSafeChar($sql) {
		$len = strlen($sql);
		$mark = $clean = '';
		for ($i = 0; $i < $len; $i++) {
			$str = $sql[$i];
			switch ($str) {
				case '\'':
					if (!$mark) {
						$mark = '\'';
						$clean .= $str;
					} elseif ($mark == '\'') {
						$mark = '';
					}
					break;
				case '/':
					if (empty($mark) && $sql[$i + 1] == '*') {
						$mark = '/*';
						$clean .= $mark;
						$i++;
					} elseif ($mark == '/*' && $sql[$i - 1] == '*') {
						$mark = '';
						$clean .= '*';
					}
					break;
				case '#':
					if (empty($mark)) {
						$mark = $str;
						$clean .= $str;
					}
					break;
				case "\n":
					if ($mark == '#' || $mark == '--') {
						$mark = '';
					}
					break;
				case '-':
					if (empty($mark) && substr($sql, $i, 3) == '-- ') {
						$mark = '-- ';
						$clean .= $mark;
					}
					break;
				default:
					break;
			}
			$clean .= $mark ? '' : $str;
		}
		return $clean;
	}
	
	/**
	 * 将数组格式化为具体的字符串
	 * 增加支持 大于 小于, 不等于, not in, +=, -=等操作符
	 *
	 * @param array $params
	 * 		要格式化的数组
	 * @param string $glue
	 * 		字符串分隔符
	 * @return array
	 * 		array['fields']是格式化后的字符串
	 */
	public static function parseParameter($params, $glue = ',', $alias = '') {
		$result = array('fields' => ' 1 ', 'params' => array());
		$split = '';
		$suffix = '';
		$allow_operator = array('>', '<', '<>', '!=', '>=', '<=', '+=', '-=', 'LIKE', 'like');
		if (in_array(strtolower($glue), array('and', 'or'))) {
			$suffix = '__';
		}
		if (!is_array($params)) {
			$result['fields'] = $params;
			return $result;
		}
		if (is_array($params)) {
			$result['fields'] = '';
			foreach ($params as $fields => $value) {
				//update或是insert语句，值为null时按空处理
				if ($glue == ',') {
					$value = $value === null ? '' : $value;
				}
				$operator = '';
				if (strpos($fields, ' ') !== FALSE) {
					list($fields, $operator) = explode(' ', $fields, 2);
					if (!in_array($operator, $allow_operator)) {
						$operator = '';
					}
				}
				if (empty($operator)) {
					$fields = trim($fields);
					if (is_array($value) && !empty($value)) {
						$operator = 'IN';
					} elseif ($value === null) {
						$operator = 'IS';
					} else {
						$operator = '=';
					}
				} elseif ($operator == '+=') {
					$operator = " = `$fields` + ";
				} elseif ($operator == '-=') {
					$operator = " = `$fields` - ";
				} elseif ($operator == '!=' || $operator == '<>') {
					//如果是数组不等于情况，则转换为NOT IN
					if (is_array($value) && !empty($value)) {
						$operator = 'NOT IN';
					} elseif ($value === null) {
						$operator = 'IS NOT';
					}
				}
				
				//当条件为having时，可以使用聚合函数
				$select_fields = self::parseFieldAlias($fields, $alias);
				if (is_array($value) && !empty($value)) {
					$insql = array();
					//忽略数组的键值，防止SQL注入
					$value = array_values($value);
					foreach ($value as $v) {
						$placeholder = self::parsePlaceholder($fields, $suffix);
						$insql[] = $placeholder;
						$result['params'][$placeholder] = is_null($v) ? '' : $v;
					}
					$result['fields'] .= $split . "$select_fields {$operator} (".implode(",", $insql).")";
					$split = ' ' . $glue . ' ';
				} else {
					$placeholder = self::parsePlaceholder($fields, $suffix);
					$result['fields'] .= $split . "$select_fields {$operator} " . (is_null($value) ? 'NULL' : $placeholder);
					$split = ' ' . $glue . ' ';
					if (!is_null($value)) {
						$result['params'][$placeholder] = is_array($value) ? '' : $value;
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * 处理字段占位符
	 * @param string $field
	 * @param string $suffix
	 */
	private static function parsePlaceholder($field, $suffix = '') {
		static $params_index = 0;
		$params_index++;
	
		$illegal_str = array('(', ')', '.', '*');
		$placeholder = ":{$suffix}" . str_replace($illegal_str, '_', $field) . "_{$params_index}";
		return $placeholder;
	}
	
	private static function parseFieldAlias($field, $alias = '') {
		if (strexists($field, '.') || strexists($field, '*')) {
			return $field;
		}
		if (strexists($field, '(')) {
			$select_fields = str_replace(array('(', ')'), array('(' . (!empty($alias) ? "`{$alias}`." : '') .'`',  '`)'), $field);
		} else {
			$select_fields = (!empty($alias) ? "`{$alias}`." : '') . "`$field`";
		}
		return $select_fields;
	}
	
	/**
	 * 格式化select字段
	 * @param array $field 字段
	 * @param string $alias 表别名
	 */
	public static function parseSelect($field = array(), $alias = '') {
		if (empty($field) || $field == '*') {
			return ' SELECT *';
		}
		if (!is_array($field)) {
			$field = array($field);
		}
		$select = array();
		$index = 0;
		foreach ($field as $field_row) {
			if (strexists($field_row, '*')) {
				if (!strexists(strtolower($field_row), 'as')) {
					$field_row .= " AS '{$index}'";
				}
			} elseif (strexists(strtolower($field_row), 'select')) {
				//当前可能包含子查询，但不推荐此写法
				if ($field_row[0] != '(') {
					$field_row = "($field_row) AS '{$index}'";
				}
			} elseif (strexists($field_row, '(')) {
				$field_row = str_replace(array('(', ')'), array('(' . (!empty($alias) ? "`{$alias}`." : '') . '`',  '`)'), $field_row);
				//如果聚合函数没有指定AS字段，则添加当前索引为AS
				if (!strexists(strtolower($field_row), 'as')) {
					$field_row .= " AS '{$index}'";
				}
			} else {
				$field_row = (!empty($alias) ? "`{$alias}`." : '') . '`'. $field_row. '`';
			}
			$select[] = $field_row;
			$index++;
		}
		return " SELECT " . implode(',', $select);
	}
	
	public static function parseLimit($limit, $inpage = true) {
		$limitsql = '';
		if (empty($limit)) {
			return $limitsql;
		}
		if (is_array($limit)) {
			$limit[0] = max(intval($limit[0]), 1);
			!empty($limit[1]) && $limit[1] = max(intval($limit[1]), 1);
			if (empty($limit[0]) && empty($limit[1])) {
				$limitsql = '';
			} elseif (!empty($limit[0]) && empty($limit[1])) {
				$limitsql = " LIMIT " . $limit[0];
			} else {
				$limitsql = " LIMIT " . ($inpage ? ($limit[0] - 1) * $limit[1] : $limit[0]) . ', ' . $limit[1];
			}
		} else {
			$limit = trim($limit);
			if (preg_match('/^(?:limit)?[\s,0-9]+$/i', $limit)) {
				$limitsql = strexists(strtoupper($limit), 'LIMIT') ? " $limit " : " LIMIT $limit";
			}
		}
		return $limitsql;
	}
	
	public static function parseOrderby($orderby, $alias = '') {
		$orderbysql = '';
		if (empty($orderby)) {
			return $orderbysql;
		}
	
		if (!is_array($orderby)) {
			$orderby = explode(',', $orderby);
		}
		foreach ($orderby as $i => &$row) {
			$row = strtolower($row);
			if (substr($row, -3) != 'asc' && substr($row, -4) != 'desc') {
				unset($orderby[$i]);
			}
			$row = (!empty($alias) ? "`{$alias}`." : '') . $row;
		}
		$orderbysql = implode(',', $orderby);
		return !empty($orderbysql) ? " ORDER BY $orderbysql " : '';
	}
	
	public static function parseGroupby($statement, $alias = '') {
		if (empty($statement)) {
			return $statement;
		}
		if (!is_array($statement)) {
			$statement = explode(',', $statement);
		}
		foreach ($statement as $i => &$row) {
			$row = (!empty($alias) ? "`{$alias}`." : '') . '`' . strtolower($row) . '`';
			if (strexists($row, ' ')) {
				unset($statement[$i]);
			}
		}
		$statementsql = implode(', ', $statement);
		return !empty($statementsql) ? " GROUP BY $statementsql " : '';
	}
}