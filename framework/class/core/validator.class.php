<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/10/30
 * Time: 11:24
 */
class Validator {



	private $defaults = array(
		'required'=> '%key%字段是必须',
		'integer'=>'%key%必须是整数',
		'string'=>'%key%必须是字符串',
		'array'=>'%key%必须是数组',
		'min'=>'%key%不能小于%s%',
		'max'=>'%key%不能大于%s%',
		'size'=>'%key%不能大于%s',
		'url'=> '%key%不是有效的url',
		'email'=> '%key%不是有效的邮箱',
		'mobile'=>'%key%不是有效的手机号',
		'file'=> '%key%必须是一个文件',
		'image'=> '%key%必须是一个图片',
		'ip'=> '%key%不是有效的ip',
		'numeric'=>'%key%必须是数字',
		'in'=> '%s 必须在 %n 内',
		'date'=> '%key% 必须是有效的日期',
		'regex'=>'%key% 不正确', //regex:pattern
		'same'=> '%key 和 %nextKey% 不一样', //some:field
	);
	private $validates = array();

	/**
	 *  验证规则
	 * @var array
	 */
	private $rules = array();
	/**
	 *  验证失败后的消息
	 * @var array
	 */
	private $messages = array();

	private $data = array();

	private $errors = array();

	public function __construct($data, $rules = array(), $messages = array()) {
		$this->data = $data;
		$this->rules = $this->parseRule($rules);
		$this->messages = $messages;
	}

	/**
	 *  解析rule
	 * @param $rules
	 * @return InvalidArgumentException
	 */
	protected function parseRule($rules) {
		if(count($this->rules) == 0) {
			return new InvalidArgumentException('无效的rules');
		}
		foreach ($rules as $key=>$rule) {
			$this->rules[$key][] = $this->parseSingleRule($rule);
		}
	}

	public function addRule($key, $callable = null) {
		if(!$this->key) {
			throw new InvalidArgumentException('无效的参数');
		}
		$this->rules[$key] = $this->parseSingleRule($callable);
	}

	/**
	 *  解析单个规则
	 * @param $value
	 * @return mixed
	 */
	protected function parseSingleRule($value) {
		if(is_string($value)) {
			$rules = explode('|', $value);
			$result = array();
			foreach ($rules as $rule) {
				$kv = explode(':', $rule);
				$rulearray = array();
				$rulearray['type'] = $kv[0];
				if(count($kv) > 1) {
					$rulearray['params'] = explode(',' ,$kv[1]);
				}
				$result[] = $rulearray;
			}
			return $result;
		}
		if(is_array($value) || is_callable($value)) {
			return $value;
		}
		throw new InvalidArgumentException('无效的rule配置项');
	}

	/**
	 *  是否验证失败
	 * @return bool
	 */
	public function isError() {
		return !empty($this->errors);
	}

	public function errors() {
		return $this->errors;
	}

	/**
	 *  验证参数必须
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public function validRequired($key, $value) {
		return isset($this->data[$key]);
	}

	public function validArray($key, $value) {
		return is_array($value);
	}

	public function validInteger($key, $value) {
		return is_int($value);
	}


	public function validNumeric($key, $value) {
		return is_numeric($value);
	}

	public function validString($key, $value) {
		return is_string($value);
	}

	public function validFile($key, $value) {
		return is_file($value);
	}

	public function validImage($key, $value) {
		if($value instanceof UploadedFile) {
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
	 * @param $key
	 * @param $value
	 * @param $params
	 * @return int
	 */
	public function validRegex($key, $value, $params) {
		return preg_match($params[0], $value);
	}

	/**
	 *  验证ip是否正确
	 * @param $key
	 * @param $value
	 * @return bool|mixed
	 */
	public function validIp($key, $value) {
		if(!is_null($value)) {
			return filter_var($value, FILTER_VALIDATE_IP);
		}
		return false;
	}

	/**
	 *  大小校验
	 * @param $key
	 * @param $value
	 * @param $params
	 * @return bool
	 */
	public function validSize($key, $value, $params) {
		return $this->getSize($key, $value) == $params[0];
	}

	/**
	 *  校验max
	 * @param $key
	 */
	public function validMax($key, $value, $params) {
		$size = $this->getSize($key, $value);
		return $size >= $params[0];
	}

	/**
	 *  校验max
	 * @param $key
	 * @param $value
	 * @param $params
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

	protected function getSize($key, $value)
	{
		if (is_numeric($value) ) {
			return $value;
		} elseif (is_array($value)) {
			return count($value);
		} elseif ($value instanceof SplFileInfo) {
			return $value->getSize() / 1024;
		}

		return strlen($value);
	}

	private function val($key) {
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	/**
	 *
	 */
	public function valid() {
		$this->errors = array();
		foreach ($this->rules as $key=>$rule) {
			$value = $this->getValue($key);
			if(is_array($rule)) {
				foreach ($rule as $singleRule) {
					$callback = $this->createCallback($singleRule);
					$this->validSingle($callback, $key, $value);
				}
			}
			if(is_callable($rule)) {
				$this->validSingle($rule, $key, $value);
			}
		}
		return count($this->errors) == 0;
	}

	/**
	 *  单条验证
	 * @param $callback
	 * @param $key
	 * @param null $value
	 * @param array $params
	 */
	protected function validSingle($callback, $key, $value = null, $params = array()) {
		$valid = call_user_func_array($callback, $key, $value, $params);
		if(!$valid) {
			$this->errors[$key][] = $this->getErrorMessage($key);
		}
	}

	private function createCallback($rule) {
		if(isset($this->defaults[$rule['type']])) {
			return array($this, 'valid'.ucfirst($rule['type']));
		}
		return null;
	}

	private function getValue($key) {
		return isset($this->data[$key] ? $this->data[$key] : null);
	}


	protected function getErrorMessage($key) {
		return $this->defaults[$key];
	}
}

