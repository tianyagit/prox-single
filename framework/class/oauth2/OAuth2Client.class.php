<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
abstract class OAuth2Client
{
	protected $ak;
	protected $sk;

	public function __construct($ak, $sk)
	{
		$this->ak = $ak;
		$this->sk = $sk;
	}

	public static function supportLoginType(){
		return array('System', 'QQ', 'Wechat');
	}

	public static function create($type, $appid, $appsecret) {
		$types = self::supportLoginType();
		if (in_array($type, $types)) {
			load()->classs('oauth2/' . $type);
			return new $type($appid, $appsecret);
		}
		return null;
	}

	abstract function showLoginUrl($calback_url = '');

	abstract function user();

	public function we7user() {
		load()->model('user');
		global $_GPC;
		$user = $this->user();
		if (is_error($user)) {
			return $user;
		}
		if (in_array($_GPC['login_type'], array('QQ', 'Wechat'))) {
			$user = user_third_info_register($user);
		}
		return $user;
	}


}