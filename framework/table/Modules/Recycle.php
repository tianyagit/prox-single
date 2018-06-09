<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Modules;

class Recycle extends \We7Table {
	protected $tableName = 'modules_recycle';
	protected $primaryKey = 'id';
	protected $field = array(
		'name',
		'type',
	);
	protected $default = array(
		'name' => '',
		'type' => 0,
	);

	public function getByName($modulename) {
		return $this->query->where('name', $modulename)->get();
	}

	public function deleteByName($modulename) {
		return $this->query->where('name', $modulename)->delete();
	}

	public function addModule($modulename, $type = 1) {
		return $this->fill(array(
			'name' => $modulename,
			'type' => $type,
		))->save();
	}

	public function searchWithModules() {
		return $this->query->from('modules', 'a')->select('a.*')->leftjoin('modules_recycle', 'b')->on(array('a.name' => 'b.name'));

	}
}