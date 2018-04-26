<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
namespace We7\Table\Modules;

class Cloud extends \We7Table {
	protected $tableName = 'modules_cloud';
	protected $primaryKey = 'id';
	protected $field = array(
		'name',
		'has_new_branch',
		'has_new_version',
		'lastupdatetime',
	);
	
	public function getByName($name) {
		if (empty($name)) {
			return array();
		}
		return $this->query->where('name', $name)->getall('name');
	}
	
	public function getHasUpgradeModule() {
		$list = $this->query->where('has_new_version', '1')->whereor('has_new_branch', '1')->getall('name');
		print_r($list);exit;
	}
}