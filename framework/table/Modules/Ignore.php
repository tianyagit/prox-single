<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Modules;

class Ignore extends \We7Table {
	protected $tableName = 'modules_ignore';
	protected $primaryKey = '';
	protected $field = array(
		'mid',
		'name',
		'version',
	);
	protected $default = array(
		'mid' => '',
		'name' => '',
		'version' => '',
	);
	
}