<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/31
 * Time: 9:27
 */
load()->classs('wxapp/api/wxappbaseapi');
load()->classs('wxapp/api/wxappauthapi');//授权权限API
load()->classs('wxapp/api/wxappcodeapi'); //代码上传API
load()->classs('wxapp/wxapprepository'); // token ticket存储获取仓库
class WxAppCloudException extends Exception {}
class WxAppCloud {
	const OAUTH_URL = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?'; //登录授权页面
	const USER_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info';


	private $authApi = null; // 认证API
	private $repo = null; //token 仓库
	private $three_appid = null; //第三方平台appid
	private $three = null; //第三方平台数据
	public function __construct($three_appid) {
		$this->three_appid = $three_appid;
		$this->repo = new WxAppRepository($three_appid);
		$this->three = $this->repo->getThreePlatform();
		if(is_null($this->three)) {
			throw new WxAppCloudException('未找到第三方平台',-1);
		}
		$this->authApi = new WxAppAuthApi($this->three['appid'], $this->three['appsecret'], $this->three['ticket']);
	}
	// 更新第三方平台ticket
	public static function updateThreePlatform($xml) {
		$xmlobj = isimplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$decodeXML = aes_decode($xmlobj->Encrypt, 'MrQMqLsKCpUxOeNd2McXN4g54WLyqrUBED7BnvxWQhB');
		$data = isimplexml_load_string($decodeXML, 'SimpleXMLElement', LIBXML_NOCDATA);
		/**
		 * * <xml><AppId><![CDATA[wx991ec14508b7d1e7]]></AppId>
		<CreateTime>1504073875</CreateTime>
		<InfoType><![CDATA[component_verify_ticket]]></InfoType>
		<ComponentVerifyTicket><![CDATA[ticket@
		 */
		if($data->InfoType == 'component_verify_ticket') {
			$repo = new WxAppRepository($data->AppId);
			$repo->updateThreePlatformTicket($data->ComponentVerifyTicket);
		}
	}
	/**
	 *  去授权
	 * @param $redirect_uri
	 * @return string 返回授权URL
	 */
	public function redirect($redirect_uri) {
		$token = $this->getThreeAccessToken();
		list($pre_auth_code,$expires_in) = $this->authApi->getPreAuthCode($token); //获取auth_code
		$params = array(
			'component_appid'=> $this->three_appid,
			'pre_auth_code' => $pre_auth_code,
			'redirect_uri'=> $redirect_uri
		);
		return self::OAUTH_URL.http_build_query($params);
	}

	/**
	 *  获取授权后的数据
	 * @param $auth_code
	 * @return mixed
	 */
	public function authData($auth_code) {
		$token = $this->getThreeAccessToken();
		$data = $this->authApi->getApiQueryCode($auth_code, $token);
		$appid = $data['authorization_info']['authorizer_appid'];
		$this->repo->updateOrCreate($data['authorization_info'], $appid);
		return $data;
	}

	/**
	 *  上传代码
	 * @param $templateId
	 * @param $wxapp_id
	 * @param $user_version
	 * @param $user_desc
	 */
	public function commitCode($template_id, $wxapp_id, $user_version, $user_desc) {
		$token = $this->getAuthAppAccessToken($wxapp_id);
		$codeApi = new WxAppCodeApi($token);
		$extjson = array('ext'=>array('a'=>1,'b'=>2), 'extAppid'=> $wxapp_id);
		$codeApi->commitCode($template_id, $extjson, $user_version, $user_desc);
	}

	/**
	 *  获取二维码
	 * @param $wxapp_id
	 * @return mixed
	 */
	public function getQrCode($wxapp_id) {
		$token = $this->getAuthAppAccessToken($wxapp_id);
		$codeApi = new WxAppCodeApi($token);
		return $codeApi->getQrcode();
	}

	/**
	 * 提交审核
	 * @param $wxapp_id
	 */
	public function submitAudit($wxapp_id, $data) {
		$codeApi = $this->getCodeApi($wxapp_id);
		$codeApi->submitAudit($data);
	}
	/**
	 * 上线
	 */
	public function release($wxapp_id) {
		$codeApi = $this->getCodeApi($wxapp_id);
		$codeApi->release();
	}

	/**
	 *  获取当前小程序的可选类目
	 * @param $wxapp_id
	 */
	public function getCategory($wxapp_id) {
		$codeApi = $this->getCodeApi($wxapp_id);
		return $codeApi->getCategory();
	}

	/**
	 *  获取当前小程序的可选类目
	 * @param $wxapp_id
	 */
	public function getPage($wxapp_id) {
		$codeApi = $this->getCodeApi($wxapp_id);
		return $codeApi->getPage();
	}


	private function getCodeApi($wxapp_id) {
		$token = $this->getAuthAppAccessToken($wxapp_id);
		$codeApi = new WxAppCodeApi($token);
		return $codeApi;
	}
	/**
	 *  获取平台 token
	 * @param $component_id
	 */
	public function getThreeAccessToken() {
		$createtime  = $this->three['token_createtime'];
		$access_token  = $this->three['access_token'];
		$expires_in  = $this->three['expires_in'];//过期时间
		if(empty($access_token) || (time()-$createtime)>= $expires_in) { //7200 少一点
			list($access_token, $expires_in) =  $this->authApi->getComponentAccessToken();
			$this->repo->updateThreePlatFormToken($access_token, $expires_in);
		}
		return $access_token;
	}

	/**
	 *  获取授权APP Access Token
	 */
	public function getAuthAppAccessToken($wxapp_id) {
		$wxapp = $this->repo->getAuthApp($wxapp_id);
		return $wxapp['authorizer_access_token'];
	}


}