<?php

/**
 * 站内商城模块定义
 *
 * @author WeEngine Team
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class StoreModule extends WeModule {
	public function welcomeDisplay() {
		header('Location: ' . $this->createWebUrl('goodsbuyer', array('direct' => 1)));
		exit();
	}
}
