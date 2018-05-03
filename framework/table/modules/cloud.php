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
		'install_status',
		'account_support',
		'wxapp_support',
		'webapp_support',
		'phoneapp_support',
		'welcome_support',
	);
	protected $default = array(
		'name' => '',
		'has_new_branch' => 0,
		'has_new_version' => 0,
		'install_status' => 0,
		'account_support' => 1,
		'wxapp_support' => 1,
		'webapp_support' => 1,
		'phoneapp_support' => 1,
		'welcome_support' => 1,
	);
	
	public function getByName($name) {
		if (empty($name)) {
			return array();
		}
		return $this->query->where('name', $name)->getall('name');
	}
	
	public function getAccountUninstallTotal() {
		return $this->query->where('account_support', MODULE_SUPPORT_ACCOUNT)->count();
	}
	
	public function getWxappUninstallTotal() {
		return $this->query->where('wxapp_support', MODULE_SUPPORT_ACCOUNT)->count();
	}
	
	public function getWebappUninstallTotal() {
		return $this->query->where('webapp_support', MODULE_SUPPORT_ACCOUNT)->count();
	}
	
	public function getPhoneappUninstallTotal() {
		return $this->query->where('phone_support', MODULE_SUPPORT_ACCOUNT)->count();
	}
}