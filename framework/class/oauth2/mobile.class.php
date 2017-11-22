<?php
/**
 * 手机登录
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class Mobile extends OAuth2Client {
	public function __construct($ak, $sk) {
		parent::__construct($ak, $sk);
	}

	public function showLoginUrl($calback_url = '') {

	}

	public function user() {
		global $_GPC, $_W;
		$mobile = trim($_GPC['username']);
		$member['password'] = $_GPC['password'];

		pdo_query('DELETE FROM'.tablename('users_failed_login'). ' WHERE lastupdate < :timestamp', array(':timestamp' => TIMESTAMP-300));
		$failed = pdo_get('users_failed_login', array('username' => $mobile, 'ip' => CLIENT_IP));
		if ($failed['count'] >= 5) {
			return error('-1', '输入密码错误次数超过5次，请在5分钟后再登录');
		}
		if (!empty($_W['setting']['copyright']['verifycode'])) {
			$verify = trim($_GPC['verify']);
			if (empty($verify)) {
				return error('-1', '请输入验证码');
			}
			$result = checkcaptcha($verify);
			if (empty($result)) {
				return error('-1', '输入验证码错误');
			}
		}
		if (empty($mobile)) {
			return error('-1', '请输入要登录的手机号');
		}
		if (!preg_match(REGULAR_MOBILE, $mobile)) {
			return error(-1, '手机号格式不正确');
		}
		if (empty($member['password'])) {
			return error('-1', '请输入密码');
		}

		$user_table = table('users');
		$user_profile = $user_table->userProfileMobile($mobile);

		if (empty($user_profile)) {
			return error(-1, '手机号未注册');
		}
		$member['uid'] = $user_profile['uid'];
		return $member;
	}

	public function validateMobile() {
		global $_GPC;
		$mobile = $_GPC['mobile'];
		if (empty($mobile)) {
			return error(-1, '手机号不能为空');
		}
		if (!preg_match(REGULAR_MOBILE, $mobile)) {
			return error(-1, '手机号格式不正确');
		}
		$user_table = table('users');
		$mobile_exists = $user_table->userProfileMobile($mobile);
		if (!empty($mobile_exists)) {
			return error(-1, '手机号已存在');
		}
		return true;
	}

	public function register() {
		global $_GPC;
		$member = array();
		$profile = array();
		$smscode = trim($_GPC['smscode']);
		$mobile = trim($_GPC['mobile']);

		if (empty($smscode)) {
			return error(-1, '短信验证码不能为空');
		}

		$user_table = table('users');
		$code_info = $user_table->userVerifyCode($mobile, $smscode);
		if (empty($code_info)) {
			itoast('短信验证码不正确', '', '');
		}
		if ($code_info['createtime'] + 120 < TIMESTAMP) {
			itoast('短信验证码已过期，请重新获取', '', '');
		}
		$member['username'] = $mobile;
		$member['openid'] = $mobile;
		$member['register_type'] = USER_REGISTER_TYPE_MOBILE;

		$profile['mobile'] = $mobile;

		return array(
			'member' => $member,
			'profile' => $profile
		);
	}
}