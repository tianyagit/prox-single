<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/12/15
 * Time: 15:09
 */

class DbOperatorTable extends We7Table {

	protected $tableName = 'db_operator';

	protected $primaryKey = 'Id';

	protected $field = array('name', 'age', 'is_delete', 'update_time','type');

	protected $default = array('age'=>5,
		'type'=>'tabletest',
		'name'=>'custom',
		'is_delete'=>0,
		'update_time'=> 'custom');

	protected $rule = array(
		'age'=>'required|min:10|max:99',
		'name'=>'required|min:1|max:6',
		'is_delete'=> array(array('name'=>'in', 'params'=>array(1,0)))
	);


	public function __construct() {
		parent::__construct();
	}

	public function defaultName() {
		return random(5);
	}

	public function defaultUpdateTime() {
		return time();
	}

}