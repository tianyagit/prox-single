<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/31
 * Time: 18:07
 * @property-read WxAppCodeApi $code
 * @property-read WxAppAuthApi $auth
 * @property-read WxAppMemberApi $member
 * @property-read WxAppDomainApi $domain
 */
class WxAppApi {
	private $access_token;
	public function __construct($access_token) {
		$this->access_token = $access_token;
	}

	public function __get($name) {
		$api = null;
		switch ($name) {
			case 'code': $api = new WxAppCodeApi($this->access_token); break;
			case 'member': $api = new WxAppMemberApi($this->access_token); break;
			case 'domain': $api = new WxAppDomainApi($this->access_token); break;
		}
		return $api;
	}
}