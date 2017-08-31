<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/31
 * Time: 17:55
 */
class WxAppDomainApi extends WxAppBaseApi {


	/**
	 *  获取域名配置
	 * @return mixed
	 */
	public function getDomain() {
		return $this->modifyDomain('get');
	}

	/**
	 *  添加域名
	 * @param array $requestdomain
	 * @param array $wxrequestdomain
	 * @param array $uploaddomain
	 * @param array $downloaddomain
	 * @return mixed
	 */
	public function addDomain(array $requestdomain = array(),
	                          array $wxrequestdomain = array(),
	                          array $uploaddomain = array(),array $downloaddomain = array()) {
		return $this->modifyDomain('add', $requestdomain,
			$wxrequestdomain ,
			$uploaddomain,
			$downloaddomain);
	}

	/**
	 * 删除域名
	 * @param array $requestdomain
	 * @param array $wxrequestdomain
	 * @param array $uploaddomain
	 * @param array $downloaddomain
	 * @return mixed
	 */
	public function delDomain(array $requestdomain = array(),
	                          array $wxrequestdomain = array(),
	                          array $uploaddomain = array(),array $downloaddomain = array()) {
		return $this->modifyDomain('del', $requestdomain,
			$wxrequestdomain ,
			$uploaddomain,
			$downloaddomain);

	}

	/**
	 *  更新域名
	 * @param array $requestdomain
	 * @param array $wxrequestdomain
	 * @param array $uploaddomain
	 * @param array $downloaddomain
	 * @return mixed
	 */
	public function updateDomain(array $requestdomain = array(),
	                          array $wxrequestdomain = array(),
	                          array $uploaddomain = array(),array $downloaddomain = array()) {
		return $this->modifyDomain('set', $requestdomain,
			$wxrequestdomain ,
			$uploaddomain,
			$downloaddomain);

	}

	/**
	 * action
	add添加, delete删除, set覆盖, get获取。当参数是get时不需要填四个域名字段。
	 * @param $action
	 * requestdomain	 request合法域名，当action参数是get时不需要此字段。
	wsrequestdomain	 socket合法域名，当action参数是get时不需要此字段。
	uploaddomain	 uploadFile合法域名，当action参数是get时不需要此字段。
	downloaddomain	 downloadFile合法域名，当action参数是get时不需要此字段。

	 */
	private function modifyDomain($action,array $requestdomain = array(),
	                              array $wxrequestdomain = array(),
	                              array $uploaddomain = array(),array $downloaddomain = array()) {
		$postdata = array('action'=>$action,
			'requestdomain'=>$requestdomain,
			'wxrequestdomain'=> $wxrequestdomain,
			'uploaddomain' => $uploaddomain,
			'downloaddomain'=>$downloaddomain
		);
		return $this->post('https://api.weixin.qq.com/wxa/modify_domain?access_token'.$this->accessToken(),$postdata);
	}
}