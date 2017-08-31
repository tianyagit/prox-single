<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/31
 * Time: 17:40
 */
class WxApiException extends Exception {

	const SYSTEM_ERROR = -1;
	const NOT_THREE_ERROR = 86000; //不是由第三方代小程序进行调用
	const NOT_THREE_COMMIT_ERROR = 86001; //不存在第三方的已经提交的代码
	const LABEL_STYLE_ERROR = 85006; //标签格式错误
	const PAGE_PATH_ERROR = 85007; //页面路径错误
	const CATEGORY_ERROR = 85008; //类目填写错误
	const EXITS_AUDIT_VERSION_ERROR = 85009; //已经有正在审核的版本
	const ITEM_LIST_ERROR  = 85010; // item_list有项目为空
	const TITLT_ERROR  = 85011; // item_list有项目为空
	const AUDIT_COUNT_ERROR = 85023; // 审核列表填写的项目数不在1-5以内
	const NOT_NICKNAME_ERROR = 86002; // 小程序还未设置昵称、头像、简介。请先设置完后再重新提交。

}
class WxAppBaseApi {


	private $access_token = null;
	public function __construct($access_token = null) {
		$this->access_token = $access_token;
	}

	public function setAccessToken($access_token) {
		$this->access_token = $access_token;
	}
	/**
	 *  获取Access token
	 * @return null
	 */
	protected function accessToken() {
		return $this->access_token;
	}

	protected function post($url, $data) {
		if(is_array($data)) {
			$data = json_encode($data);
		}
		$response = ihttp_request($url, $data, array('Content-Type' => 'application/json'));
		return $this->parseResponse($response);
	}

	protected function get($url) {
		$resp = ihttp_get($url);
		return $this->parseResponse($resp);
	}
	/**
	 *
	 */
	protected function parseResponse($response){
		if(is_error($response)) {
			throw new WxAppAuthApiException('系统错误',WxAppAuthApiException::SYSTEM_ERROR);
		}
		if($response['headers']['Content-Type'] == 'image/jpeg') {
			return $response['content'];
		}
		$content = $response['content'];
		$json = json_decode($content, JSON_UNESCAPED_UNICODE);
		if(isset($json['errcode']) && $json['errcode'] != '0') {
			throw new WxApiException($json['errmsg'], $json['errcode']);
		}
		return $json;
	}
}