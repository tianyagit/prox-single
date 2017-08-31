<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/30
 * Time: 13:35
 */
class WxAppAuthApi extends WxAppBaseApi {
	// 获取平台token
	const API_COMPONENT_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
	//获取pre_auth_code
	const PRE_AUTH_CODE = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode';
	//获取授权信息
	const API_QUEYR_AUTH = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth';

	private $component_appid; //第三方平台APPID
	private $component_appsecret; //第三方平台appsecret
	private $component_verify_ticket;// ticket


	/**
	 * WxAppAuth constructor.
	 * @param $component_appid
	 * @param $component_appsecret
	 * @param $component_verify_ticket ticket@@@7CiP6eLB1jG1jG_MEQXDOi3dmWe2uBvOX_y-OsbxlPh9R0Ds9HtwApjNYutja0mtM5i5XdrOIFb4kl_uezA8_Q
	 */
	public function __construct($component_appid, $component_appsecret, $component_verify_ticket) {
		$this->component_appid = $component_appid;
		$this->component_appsecret = $component_appsecret;
		$this->component_verify_ticket = $component_verify_ticket;
	}

	/**
	 * 6NXvH1QOgDRNtfZhTpqx6SWlmRk6R8Bw4jyJ7IzTWyF4erLuAxLJRICYb32eIHP_s-_MsU7t7jrr9zJsWtjznL_Mf3U7EOr6Hgtl1LHZLDMEZGhAJAQLS
	 * 获取第三方平台component_access_token
	 * @return mixed
	 */
	public function getComponentAccessToken() {
		$postdata = array('component_appid'=>$this->component_appid,
			'component_appsecret'=>$this->component_appsecret,
			'component_verify_ticket'=> $this->component_verify_ticket);
		$postdata = json_encode($postdata);
		$result =  $this->post(self::API_COMPONENT_TOKEN, $postdata);
		return array($result['component_access_token'], $result['expires_in']);
	}

	/**
	 *  获取Pre_Auth_code
	 * @param $component_access_token
	 * @return array
	 */
	public function getPreAuthCode($component_access_token) {

		$data = array('component_appid'=> $this->component_appid);
		$result = $this->post(self::PRE_AUTH_CODE.'?component_access_token='.$component_access_token,
			$data);
		return array($result['pre_auth_code'], $result['expires_in']);
	}

	//https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=xxxx

	/**
	 * 使用授权码换取公众号或小程序的接口调用凭据和授权信息
	 *  小程序回调后返回auth_code
	 */
	public function getApiQueryCode($authorization_code, $component_access_token) {
		$data = array('component_appid'=> $this->component_appid,
			'authorization_code'=>$authorization_code);
		$result = $this->post(self::API_QUEYR_AUTH.'?component_access_token='.$component_access_token,
			$data);
		return $result;
	}

}

class WxAppAuthApiException extends Exception {

}