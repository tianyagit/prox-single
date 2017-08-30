<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/30
 * Time: 18:31
 */
class WxAppCode {


	private $tokenRepo = null;
	private $token = null;
	private $codeApi = null;
	public function __construct() {
		$this->tokenRepo = new WxAppRepository();
		$this->token  = $this->tokenRepo->getAppToken('');
		$this->codeApi = new WxAppCodeApi($this->token);
	}

	/**
	 *  提交代码
	 */
	public function commitCode($template_id, $appid, $user_version='v0.0.1', $user_desc = '提交代码') {
		$extjson = array('ext'=>array('a'=>1,'b'=>2), 'extAppid'=> $appid);
		$this->codeApi->commitCode($template_id, $extjson, $user_version, $user_desc);
	}

	/**
	 *  获取小程序二维码
	 * @return mixed
	 */
	public function getQrcode() {
		return $this->codeApi->getQrcode();
	}
}