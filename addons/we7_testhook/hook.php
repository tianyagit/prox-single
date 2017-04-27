<?php
defined('IN_IA') or exit('Access Denied');

include "model.php";

class We7_testhookModuleHook extends WeModuleHook {
	public function hookMobileTest() {
		//return 'testplugincontent';
		include $this->template('testplugin');
	}

	public function hookWebTest() {
		include $this->template('testplugin');
	}
	
	public function hookWebUserlist($hook) {
		include $this->template('userlist');
	}
	
	public function hookMobileMyprofile($hook) {
		include $this->template('myprofile');
	}
}