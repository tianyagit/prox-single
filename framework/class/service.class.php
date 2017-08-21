<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
 
defined('IN_IA') or exit('Access Denied');

/**
 * @property Query $query
 */
abstract class We7Service {
	protected $query;
	
	public function __construct() {
		//实例化Query对象,并重置查询信息
		$this->query = load()->singleton('Query');
		$this->query->from('');
	}
}