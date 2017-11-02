<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/10/30
 * Time: 11:24.
 */
class Validator {
	private $defaults = array(
		'required' => ':attribute字段是必须',
		'integer' => ':attribute必须是整数',
		'string' => ':attribute必须是字符串',
		'array' => ':attribute必须是数组',
		'min' => ':attribute不能小于%s',
		'max' => ':attribute不能大于%s',
		'size' => ':attribute 大小必须是 %s',
		'url' => ':attribute不是有效的url',
		'email' => ':attribute不是有效的邮箱',
		'mobile' => ':attribute不是有效的手机号',
		'file' => ':attribute必须是一个文件',
		'image' => ':attribute必须是一个图片',
		'ip' => ':attribute不是有效的ip',
		'numeric' => ':attribute必须是数字',
		'in' => ':attribute 必须在 %s 内',
		'date' => ':attribute 必须是有效的日期',
		'regex' => ':attribute 不正确', //regex:pattern
		'same' => ':attribute 和 $s 不一样', //some:field
	);
	private $validates = array();

	/**
	 *  验证规则.
	 *
	 * @var array
	 */
	private $rules = array();
	/**
	 *  验证失败后的消息.
	 *
	 * @var array
	 */
	private $messages = array();
	/**
	 * @var array 数据
	 * @since version
	 */
	private $data = array();

	/** 所有的错误消息
	 * @var array
	 * @since version
	 */
	private $errors = array();


	public function __construct($data, $rules = array(), $messages = array()) {
		$this->data = $data;
		$this->rules = $this->parseRule($rules);
		$this->messages = $messages;
	}

	/**
	 * 添加规则
	 * @param $key
	 * @param null $callable
	 *
	 *
	 * @since version
	 */
	public function addRule($key, $callable = null) {
		if (!$key) {
			throw new InvalidArgumentException('无效的参数');
		}
		$this->rules[$key] = $this->parseSingleRule($callable);
	}

	/**
	 *  是否验证失败.
	 *
	 * @return bool
	 */
	public function isError() {
		return !empty($this->errors);
	}

	/**
	 * 错误明细
	 * @return array
	 *
	 * @since version
	 */
	public function errors() {
		return $this->errors;
	}


	/**
	 * 解析rule
	 * @param $rules
	 * @return array
	 * @throws InvalidArgumentException
	 */
	protected function parseRule($rules) {
		$result = array();
		if (count($rules) == 0) {
			throw new InvalidArgumentException('无效的rules');
		}
		foreach ($rules as $key => $rule) {
			$result[$key] = $this->parseSingleRule($rule);
		}

		return $result;
	}
	/**
	 *  解析单个规则.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function parseSingleRule($value) {
		if (is_string($value)) {
			$rules = explode('|', $value);
			$result = array();
			foreach ($rules as $dataKey => $rule) {
				$kv = explode(':', $rule);
				$ruleobj = new Rule($dataKey, $kv[0], array());
				$params = array();
				if (count($kv) > 1) {
					$params = explode(',', $kv[1]);
				}
				$result[] = array('name'=>$kv[0], 'params'=>$params);
			}
			return $result;
		}
		if (is_array($value)) {
			return $value;
		}
		throw new InvalidArgumentException('无效的rule配置项');
	}

	public function valid() {
		$this->errors = array();
		foreach ($this->rules as $dataKey => $rules) {
			$value = $this->getValue($dataKey);
			if (is_array($rules)) {
				foreach ($rules as $rule) {
					$callback = $this->createCallback($rule);
					$this->validSingle($callback, $dataKey, $value, $rule);
				}
			}
		}

		return count($this->errors) == 0;
	}

	/**
	 *  单条验证
	 *
	 * @param $callback
	 * @param $key
	 * @param null $value
	 * @param array $params
	 */
	private function validSingle($callback, $dataKey, $value = null, $rule) {
		$valid = call_user_func($callback, $dataKey, $value, $rule['params']);
		if (!$valid) {
			$this->errors[$dataKey][] = $this->getMessage($dataKey, $rule);
		}
	}

	/***
	 *  获取验证的回调函数
	 * @param $rule
	 *
	 * @return array|null
	 *
	 * @since version
	 */
	private function createCallback($rule) {
		if(isset($rule['callback'])) {
			return $rule['callback'];
		}
		if (isset($this->defaults[$rule['name']])) {
			return array($this, 'valid' . ucfirst($rule['name']));
		}

		return null;
	}

	/**
	 *  获取值
	 * @param $key
	 * @return mixed|null
	 */
	private function getValue($key) {
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}


	protected function getMessage($dataKey, $rule) {
		$message = $this->getErrorMessage($dataKey, $rule['name']);
		if ($message) {
			$message = str_replace(':attribute', $dataKey, $message);
			$message = vsprintf($message , $rule['params']);//sprintf($message, $rule['params']);
		}
		return $message;
	}

	protected function getErrorMessage($dataKey, $ruleName) {
		$dr = $dataKey.'.'.$ruleName;
		if ($this->messages[$dr]) {
			return $this->messages[$dr];
		}
		if (isset($this->messages[$dataKey])) {
			return $this->messages[$dataKey];
		}

		return isset($this->defaults[$ruleName]) ? $this->defaults[$ruleName] : '错误';
	}


	/**
	 *  验证参数必须.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 */
	public function validRequired($key, $value) {
		if (!isset($this->data[$key])) {
			return false;
		}
		if (is_null($value)) {
			return false;
		}
		if (is_array($value)) {
			return count($value) != 0;
		}
		if (is_string($value)) {
			return $value !== '';
		}

		return false;
	}

	/**
	 *  校验整数
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function validInteger($key, $value) {
		return is_int($value);
	}

	/**
	 *  校验数字
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function validNumeric($key, $value) {
		return is_numeric($value);
	}

	/**
	 *  校验字符串
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function validString($key, $value) {
		return is_string($value);
	}

	/**
	 *  校验数组
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function validArray($key, $value) {
		return is_array($value);
	}

	/**
	 *  校验文件
	 * @param $key
	 * @param $value
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function validFile($key, $value) {
		return is_file($value);
	}

	public function validImage($key, $value) {
		if ($value instanceof UploadedFile) {
			return $value->isImage();
		}

		return false;
	}

	public function validEmail($key, $value) {
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	public function validMobile($key, $value) {
		return $this->validRegex($key, $value, array('/^1[34578]\d{9}$/'));
	}

	/**
	 * 正则验证
	 *
	 * @param $key
	 * @param $value
	 * @param $params
	 *
	 * @return int
	 */
	public function validRegex($key, $value, $params) {
		return preg_match($params[0], $value);
	}

	/**
	 *  验证ip是否正确.
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return bool|mixed
	 */
	public function validIp($key, $value) {
		if (!is_null($value)) {
			return filter_var($value, FILTER_VALIDATE_IP);
		}

		return false;
	}

	/**
	 *  大小校验.
	 *
	 * @param $key
	 * @param $value
	 * @param $params
	 *
	 * @return bool
	 */
	public function validSize($key, $value, $params) {
		return $this->getSize($key, $value) == $params[0];
	}

	/**
	 *  校验max.
	 *
	 * @param $key
	 */
	public function validMax($key, $value, $params) {
		$size = $this->getSize($key, $value);

		return $size >= $params[0];
	}

	/**
	 *  校验max.
	 *
	 * @param $key
	 * @param $value
	 * @param $params
	 *
	 * @return bool
	 */
	public function validMin($key, $value, $params) {
		$size = $this->getSize($key, $value);

		return $size >= $params[0];
	}

	public function validUrl($key, $value, $params) {
		return filter_var($value, FILTER_VALIDATE_URL);
	}

	public function validDate($key, $value, $param) {
		return strtotime($value);
	}

	protected function getSize($key, $value) {
		if (is_numeric($value)) {
			return $value;
		} elseif (is_array($value)) {
			return count($value);
		} elseif ($value instanceof SplFileInfo) {
			return $value->getSize() / 1024;
		}

		return strlen($value);
	}
}

class Rule {
	public $dataKey = null; //数据键
	public $type = null; //校验类型
	public $params = array();//校验参数

	public function __construct($dataKey, $type, $params) {
		$this->dataKey = $dataKey;
		$this->type = $type;
		$this->params = $params;
	}

	public function kt() {
		return $this->dataKey . '.' . $this->type;
	}
}
