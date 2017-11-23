<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
abstract class OAuth2Client {
	protected $ak;
	protected $sk;
	protected $login_type;

	public function __construct($ak, $sk) {
		$this->ak = $ak;
		$this->sk = $sk;
	}

	public function getLoginType($login_type) {
		$this->login_type = $login_type;
	}

	public static function supportLoginType(){
		return array('system', 'qq', 'wechat', 'mobile');
	}

	public static function create($type, $appid = '', $appsecret = '') {
		$types = self::supportLoginType();
		if (in_array($type, $types)) {
			load()->classs('oauth2/' . $type);
			$type_name = ucfirst($type);
			$obj = new $type_name($appid, $appsecret);
			$obj->getLoginType($type);
			return $obj;
		}
		return null;
	}

	abstract function showLoginUrl($calback_url = '');

	abstract function user();

	public function we7user() {
		global $_W;
		load()->model('user');
		$user = $this->user();
		if (is_error($user)) {
			return $user;
		}
		if (in_array($this->login_type, array('qq', 'wechat'))) {
			if (empty($_W['user'])) {
				$user = user_third_info_register($user);
			} else {
				$user = user_third_info_bind($user);
			}
		}
		return $user;
	}

	abstract function register();

	public function registerNoThird() {
		load()->model('user');
		$register = array();
		if (in_array($this->login_type, array('system', 'mobile'))) {
			$register = $this->register();
			if (is_error($register)) {
				return $register;
			}
			$register = user_register_nothird($register);
		}
		return $register;
	}

}