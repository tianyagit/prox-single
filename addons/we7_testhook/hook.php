<?php
defined('IN_IA') or exit('Access Denied');

include "model.php";

class Ewei_hotelModulePlugin extends WeModulePlugin {
	public function testplugin() {
		$array = array('0' => '12', '1' => 'b', '2' => 'c');
		$array = 112122;
return $array;
		include $this->template('testplugin');
	}

	public function doMobiletestplugin() {
		include $this->template('testplugin');
	}
}