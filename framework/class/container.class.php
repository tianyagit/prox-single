<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/2
 * Time: 11:48
 */
class Container  implements ArrayAccess
{
	private $bindings = array();
	private $resolved = array();// 已make 过的示例

	private $instance = array();
	/**
	 *
	 * @param $abstract
	 * @param array $parameters
	 */
	public function make($abstract) {

		list($impl, $share) = $this->bindings[$abstract];
		if($impl instanceof Closure) {
			$object =  $impl($this);
		}
		$object =  $this->createClass($abstract);
		if ($share) {
			$instance[$abstract] = $object;
		}
		return $object;
	}


	public function bind($abstract, Closure $closure = null, $shared = false)
	{
		$this->bindings[$abstract] = array($closure , $shared);
	}

	public function alias($key, $abstract) {

	}

	/**
	 * @param ReflectionClass $reflectClass
	 * @return object
	 * @throws Exception
	 */
	private function createClass($abstract) {
		$reflectClass = new ReflectionClass($abstract);
		if ( ! $reflectClass->isInstantiable()) {
			throw new Exception('不能实例化');
		}
		$construct = $reflectClass ->getConstructor(); //获取构造函数
		$parameters = $construct->getParameters();// 获取构造函数参数
		$paramInstance = array();
		foreach ($parameters as $parameter) {
			$paramClass = $parameter->getClass();
			$paramInstance[] = $this->make($paramClass->getName()); //调用make 函数
		}
		return $reflectClass->newInstanceArgs($paramInstance);
	}


	/**
	 *  psr 11 接口实现
	 * @param $id
	 * @return mixed
	 */
	public function get($id) {
		return $this[$id];
	}

	/**
	 *  psr 11 接口实现
	 * @param $id
	 * @return bool
	 */
	public function has($id) {
		return isset($this[$id]);
	}


	/**
	 * Determine if a given offset exists.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->bound($key);
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->make($key);
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->bind($key, $value instanceof Closure ? $value : function () use ($value) {
			return $value;
		});
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key]);
	}

	/**
	 * Dynamically access container services.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this[$key];
	}

	/**
	 * Dynamically set container services.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this[$key] = $value;
	}
}