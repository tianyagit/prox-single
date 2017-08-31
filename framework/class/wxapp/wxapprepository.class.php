<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/30
 * Time: 18:23
 */
load()->classs('query');
class WxAppRepository {

	const PLATFORM_TABLE = 'three_open_platform';
	const PLATFOMR_APP_TABLE = 'three_open_platform_wxapp';

	private $three_appid;
	public function __construct($three_appid) {
		$this->three_appid = $three_appid;
	}
	/**
	 *  获取平台信息
	 * @param $component_appid
	 */
	public function getThreePlatform() {
		$query = new Query();
		$threeplatform = $query->from(self::PLATFORM_TABLE)->where('appid',$this->three_appid)->get();
		if(is_null($threeplatform)) {
			throw new WxAppCloudException('未找到第三方平台');
		}
		if(!$threeplatform['ticket']) {
			throw new WxAppCloudException('第三方平台ticket 错误');
		}
		return $threeplatform;
	}

	/**
	 *  缓存平台token
	 * @param $component_token
	 */
	public function updateThreePlatformToken($three_token, $expires_in = 7200) {
		return pdo_update(self::PLATFORM_TABLE,
			array('access_token'=>$three_token,
				'token_createtime'=>time(),
				'expires_in'=>$expires_in),
			array('appid'=>$this->three_appid));
	}


	/**
	 * @param $ticket 更新第三方平台ticket
	 */
	public function updateThreePlatformTicket($ticket) {
		return pdo_update('three_open_platform',
			array('ticket'=>$ticket),
			array('appid'=>$this->three_appid)
			);
	}


	/*
	 *  更新或创建授权APP数据
	 */
	public function updateOrCreate($data, $auth_app_id, $is_auth =1) {
		$auth_app = $this->getAuthApp($auth_app_id);
		if($auth_app) {
			return $this->updateAuthApp($data, $auth_app['authorizer_appid']);
		}
		return $this->createAuthApp($data, $is_auth);

	}
	/**
	 *  更新认证小程序数据
	 */
	public function updateAuthApp($data, $wxapp_id) {
		$dbdata = array(
			'authorizer_access_token'=>$data['authorizer_access_token'],
			'authorizer_refresh_token'=>$data['authorizer_refresh_token'],
			'expires_in'=> $data['expires_in'],
		);
		return pdo_update(self::PLATFOMR_APP_TABLE, $dbdata, array('authorizer_appid'=>$wxapp_id));
	}

	/**
	 * @param $data 微信开放平台返回的数据
	 * @param $three_primary_id //第三方平台主键ID
	 * @param int $is_auth //是否已授权
	 */
	public function createAuthApp($data, $is_auth = 1) {
		$dbdata = array(
			'authorizer_appid' => $data['authorizer_appid'],
			'authorizer_access_token'=>$data['authorizer_access_token'],
			'authorizer_refresh_token'=>$data['authorizer_refresh_token'],
			'authorizer_token_createtime' => time(),
			'expires_in'=> $data['expires_in'],
			'three_appid'=> $this->three_appid,
			'is_auth' =>$is_auth
		);
		return pdo_insert(self::PLATFOMR_APP_TABLE, $dbdata);
	}
	/**
	 *  获取已授权 app 的token
	 * @param $component_appid
	 * @param $auth_app_id //授权小程序的APPID
	 */
	public function getAuthApp($auth_app_id) {
		$query = new Query();
		return $query->from(self::PLATFOMR_APP_TABLE)->where('authorizer_appid',$auth_app_id)->get();
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