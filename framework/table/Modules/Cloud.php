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
		'title',
		'title_initial',
		'logo',
		'version',
		'install_status',
		'account_support',
		'wxapp_support',
		'webapp_support',
		'phoneapp_support',
		'welcome_support',
		'main_module_name',
		'main_module_logo',
		'has_new_version',
		'has_new_branch',
	);
	protected $default = array(
		'name' => '',
		'title' => '',
		'title_initial' => '',
		'logo' => '',
		'version' => '',
		'install_status' => 0,
		'account_support' => 1,
		'wxapp_support' => 1,
		'webapp_support' => 1,
		'phoneapp_support' => 1,
		'welcome_support' => 1,
		'main_module_name' => '',
		'main_module_logo' => '',
		'has_new_version' => 0,
		'has_new_branch' => 0,
	);
	
	public function getByName($name) {
		if (empty($name)) {
			return array();
		}
		return $this->query->where('name', $name)->get('name');
	}
	
	/**
	 * 增加不在回收站的条件
	 */
	public function searchWithoutRecycle() {
		return $this->query->from('modules_cloud', 'a')->select('a.*')->leftjoin('modules_recycle', 'b')->on(array('a.name' => 'b.name'))->where('b.name', 'NULL');
	}
	
	public function getAccountUninstallTotal() {
		return $this->searchWithoutRecycle()->where('a.account_support', MODULE_SUPPORT_ACCOUNT)->where('install_status', array(MODULE_LOCAL_UNINSTALL, MODULE_CLOUD_UNINSTALL))->getcolumn('COUNT(*)');
	}
	
	public function getWxappUninstallTotal() {
		return $this->query->where('wxapp_support', MODULE_SUPPORT_ACCOUNT)->count();
	}
	
	public function getWebappUninstallTotal() {
		return $this->query->where('webapp_support', MODULE_SUPPORT_ACCOUNT)->count();
	}
	
	public function getPhoneappUninstallTotal() {
		return $this->query->where('phoneapp_support', MODULE_SUPPORT_ACCOUNT)->count();
	}
	
	public function deleteByName($modulename) {
		return $this->query->where('name', $modulename)->delete();
	}
}