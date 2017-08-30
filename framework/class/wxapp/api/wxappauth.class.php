<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/30
 * Time: 13:35
 */
class WxAppAuth {
	const API_COMPONENT_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
	const PRE_AUTH_CODE = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode';
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
	 * 获取第三方平台component_access_token
	 * @return mixed
	 *
	 */
	public function getComponentAccessToken() {
		$postdata = array('component_appid'=>$this->component_appid,
			'component_appsecret'=>$this->component_appsecret,
			'component_verify_ticket'=> $this->component_verify_ticket);
		return $this->post(self::API_COMPONENT_TOKEN, $postdata);
	}


	public function getPreAuthCode($component_access_token) {
		$postdata = $this->post(self::PRE_AUTH_CODE.'?component_access_token='.$component_access_token,
			array('component_appid'=> $this->component_appid));
		return $postdata;
	}



	private function post($url, $data) {
		$response = ihttp_post($url, $data);
		return $this->parseResponse($response);
	}

	private function get($url) {
		$resp = ihttp_get($url);
		return $this->parseResponse($resp);
	}
	/**
	 *
	 */
	private function parseResponse($response){
		if(is_error($response)) {
			throw new WxAppCodeException(WxAppCodeException::SYSTEM_ERROR);
		}
		$content = $response['content'];
		$json = json_decode($content);
		if($json['errcode'] != '0') {
			throw new WxAppCodeException($json['errcode']);
		}
		return $json;
	}
}