<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/30
 * Time: 18:23
 */
load()->classs('query');
class WxAppRepository {

	private $component_appid;

	public function __construct($component_appid = null) {
		$this->component_appid = $component_appid;
	}
	public function getComponentToken() {
		return "W63A88sZjcs-VYpwsM8oA2FA_4Zdwr76Iw3AWERfbH5tqdgPmtBRzGYOaru4zQYRF0B4xeIAU3SqiOwsIhwM6ks8KZCH2jvDuBUe4YaEgCIBlZ_XgJl0U88cgo48mR17CCBfAFAKKJ";
	}

	/**
	 *  缓存平台token
	 * @param $component_token
	 */
	public function saveComponentToken($component_token) {

	}

	/**
	 *  获取授权的小程序 token
	 * @param $auth_app_id
	 */
	public function getAppToken($auth_app_id) {
		$query = new Query();
		$data = $query->from('account_wxapp')->where('key', $auth_app_id)->get();
		return $data['token'];
	}

	/**
	 *  保存微信小程序token
	 * @param $auth_app_id
	 * @param $token
	 */
	public function saveAppToken($auth_app_id, $token) {
//		update account_wxapp set token = $token  where key = $auth_app_id
		return pdo_update('account_wxapp', array('token'=> $token), array('uniacid'=> 1402));
	}
}