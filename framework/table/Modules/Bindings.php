<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Modules;

class Bindings extends \We7Table {
	protected $tableName = 'modules_bindings';
	protected $primaryKey = 'eid';
	protected $field = array(
		'module',
		'entry',
		'call',
		'title',
		'do',
		'state',
		'direct',
		'url',
		'icon',
		'displayorder',
	);
	protected $default = array(
		'module' => '',
		'entry' => '',
		'call' => '',
		'title' => '',
		'do' => '',
		'state' => '',
		'direct' => 0,
		'url' => '',
		'icon' => 'fa fa-puzzle-piece',
		'displayorder' => 0,
	);
	
	public function deleteByName($modulename) {
		return $this->query->where('module', $modulename)->delete();
	}
}