<?php
/**
 *  公众号成员管理API
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/31
 * Time: 17:47
 */

class WxAppMemberApi extends WxAppBaseApi {

	const BING_TESTER = 'https://api.weixin.qq.com/wxa/bind_tester';
	const UNBIND_TESTER = 'https://api.weixin.qq.com/wxa/unbind_tester';

	public function bindTester($wechatid) {
		$postdata = array('wechatid'=>$wechatid);
		return $this->post(self::BING_TESTER.'?access_token='.$this->accessToken(),$postdata);
	}

	public function unbindTester($wechatid) {
		$postdata = array('wechatid'=>$wechatid);
		return $this->post(self::UNBIND_TESTER.'?access_token='.$this->accessToken(),$postdata);
	}
}