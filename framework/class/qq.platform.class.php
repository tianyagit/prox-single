<?php
/**
 * qq第三方授权登录
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->func('communication');

define('QQ_PLATFORM_API_OAUTH_LOGIN_URL', 'https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=%s&redirect_uri=%s&state=%s&scope=%s');
define('QQ_PLATFORM_API_GET_ACCESS_TOKEN', 'https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=%s&client_secret=%s&code=%s&redirect_uri=%s');
define('QQ_PLATFORM_API_GET_OPENID', 'https://graph.qq.com/oauth2.0/me?access_token=%s');
define('QQ_PLATFORM_API_GET_USERINFO', 'https://graph.qq.com/user/get_user_info?access_token=%s&oauth_consumer_key=%s&openid=%s');
class QqPlatform {
	public $appid;
	public $appsecret;
	public $scope;
	public $redirect_uri;

	function __construct() {
		global $_W;
		$setting = setting_load('platform');
		$this->appid = $setting['qq_platform']['appid'];
		$this->appsecret = $setting['qq_platform']['appsecret'];
		$this->scope = 'get_user_info';
		$this->redirect_uri = $_W['siteroot'] . 'web/callback.php';
	}

	function getAuthLoginUrl() {
		session_start();
		$state = md5(uniqid(rand(), TRUE));
		$_SESSION['__qqcode'] = $state;
		$authurl = sprintf(QQ_PLATFORM_API_OAUTH_LOGIN_URL, $this->appid, $this->redirect_uri, $state, $this->scope);
		return $authurl;
	}

	function getAccessToken($state, $code) {
		if (empty($state) || empty($code)) {
			return error(-1, '参数错误');
		}
		session_start();
		if ($state != $_SESSION['__qqcode']) {
			return error(-1, '重新登陆');
		}
		$access_url = sprintf(QQ_PLATFORM_API_GET_ACCESS_TOKEN, $this->appid, $this->appsecret, $code, urlencode($this->redirect_uri));
		$response = ihttp_get($access_url);
		if (strexists($response['content'], 'callback') !== false){
			return error(-1, $response['content']);
		}

		parse_str($response['content'], $result);
		return $result;
	}

	function getOpenid($token) {
		if (empty($token)) {
			return error(-1, '参数错误');
		}
		$openid_url = sprintf(QQ_PLATFORM_API_GET_OPENID, $token);
		$response = ihttp_get($openid_url);
		if (strexists($response['content'], "callback") !== false) {
			$lpos = strpos($response['content'], "(");
			$rpos = strrpos($response['content'], ")");
			$content = substr($response['content'], $lpos + 1, $rpos - $lpos -1);
		}
		$result = json_decode($content, ture);
		if (isset($result->error)) {
			return error(-1, $result['content']);
		}
		return $result['openid'];
	}
	
	function getUserInfo($openid, $token) {
		if (empty($openid) || empty($token)) {
			return error(-1, '参数错误');
		}
		$openid_url = sprintf(QQ_PLATFORM_API_GET_USERINFO, $token, $this->appid, $openid);
		$response = ihttp_get($openid_url);
		$user_info = json_decode($response['content'], true);

		if ($user_info['ret'] != 0) {
			return error(-1, $user_info['ret'] . ',' . $user_info['msg']);
		}
		return $user_info;
	}
}