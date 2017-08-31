<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/30
 * Time: 15:30
 *  只需调用2个方法
 * @method redirect()
 * @method user()
 */
class WxAppOAuth {

	const OAUTH_URL = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?';

	const USER_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info';
	/**
	 * @var $authApi WxAppAuthApi 授权API
	 */
	private $authApi = null;
	private $tokenRepo = null;
	private $component_appid;
	public function __construct($component_appid, $component_appsecret, $component_verify_ticket) {
		$this->component_appid = $component_appid;
		$this->authApi = new WxAppAuthApi($component_appid, $component_appsecret, $component_verify_ticket);
		$this->tokenRepo = new WxAppRepository($component_appid);
	}

	/**
	 *  获取授权url
	 * @param $redirect_url
	 */
	public function redirect($redirect_uri) {
		$token = $this->getComponentAccessToken();
		list($pre_auth_code,$expires_in) = $this->authApi->getPreAuthCode($token); //获取auth_code
		$params = array(
			'component_appid'=> $this->component_appid,
			'pre_auth_code' => $pre_auth_code,
			'redirect_uri'=> $redirect_uri
		);
		return self::OAUTH_URL.http_build_query($params);
	}

	/**
	 *  获取
	 * @param $auth_code
	 * @return mixed
	 *
	 *  Array ( [authorization_info] => Array
	 * ( [authorizer_appid] => wxb0e582aaff9a169d
	 * [authorizer_access_token] => dM8wOI6Dl1uAiTELKoJDp4TrDg2xGJvIH7EM3tnAEZ94gS_oY-CegvXyVw2kMPvM0aIEemVz5JA7w58NUkeFbLQTUWOoKPnwEdLhVsiRCuuonnGDUycc6-SbVqXMzlMUWCFgAHDSYB
	 * [expires_in] => 7200
	 * [authorizer_refresh_token] => refreshtoken@@@MGk9PSwfGZmcjwDRTwGMNwirEwuO2jvpt61hBBHAsDk
	 * [func_info] => Array ( [0] => Array (
	 * [funcscope_category] => Array ( [id] => 17 ) ) [1] => Array ( [funcscope_category] => Array ( [id] => 19 ) ) ) ) )
	 */
	public function authData($auth_code) {
		$token = $this->getComponentAccessToken();
		return $this->authApi->getApiQueryCode($auth_code, $token);
	}

	/**
	 *  刷新token
	 */
	public function refreshToken() {

	}

	/**
	 *  此处需缓存
	 *  获取平台token
	 */
	private function getComponentAccessToken() {
		$token  = $this->tokenRepo->getComponentToken();
		if (is_null($token)) { //需判断是否过期
			$token = $this->authApi->getComponentAccessToken();
		}
		$this->tokenRepo->saveComponentToken($token);
		return $token;
	}
}