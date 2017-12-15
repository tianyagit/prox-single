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

	protected $defaults = array('age'=>5);

	protected $rules = array(
		'age'=>'required|min:10|max:99',
		'name'=>'required|min:1|max:6'
	);

	public function __construct() {
		parent::__construct();
		$this->defaults['name'] = function() {
			return random(8);
		};
	}

}