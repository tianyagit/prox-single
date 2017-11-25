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
	
	abstract function login();

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
				$user = $this->userBind($user);
			}
		}
		return $user;
	}

	abstract function bind($userInfo);
	abstract function unbind();
	
	abstract function register();

	public function registerNoThird() {
		load()->model('user');
		$register = array();
		if (in_array($this->login_type, array('system', 'mobile'))) {
			$register = $this->register();
			if (is_error($register)) {
				return $register;
			}
		}
		return $register;
	}

	/**
	 * 非第三方注册  system  mobile
	 * @param $register
	 * @return array
	 */
	public function userRegisterNothird($register) {
		global $_GPC, $_W;
		load()->model('user');
		$member = $register['member'];
		$profile = $register['profile'];
		$member['password'] = $_GPC['password'];
		$owner_uid = intval($_GPC['owner_uid']);

		$register_type = $_GPC['register_type'];

		if(istrlen($member['password']) < 8) {
			return error(-1, '必须输入密码，且密码长度不得低于8位。');
		}

		if(!empty($_W['setting']['register']['code']) || $register_type == 'mobile') {
			if (!checkcaptcha($_GPC['code'])) {
				return error(-1, '你输入的验证码不正确, 请重新输入.');
			}
		}
		$member['status'] = !empty($_W['setting']['register']['verify']) ? 1 : 2;
		$member['remark'] = '';
		$member['groupid'] = intval($_W['setting']['register']['groupid']);
		if (empty($member['groupid'])) {
			$member['groupid'] = pdo_fetchcolumn('SELECT id FROM '.tablename('users_group').' ORDER BY id ASC LIMIT 1');
			$member['groupid'] = intval($member['groupid']);
		}
		$group = user_group_detail_info($member['groupid']);

		$timelimit = intval($group['timelimit']);
		if($timelimit > 0) {
			$member['endtime'] = strtotime($timelimit . ' days');
		}
		$member['starttime'] = TIMESTAMP;
		if (!empty($owner_uid)) {
			$member['owner_uid'] = pdo_getcolumn('users', array('uid' => $owner_uid, 'founder_groupid' => ACCOUNT_MANAGE_GROUP_VICE_FOUNDER), 'uid');
		}
		$user_id = user_register($member);
		if ($register_type == 'mobile') {
			pdo_update('users', array('username' => $member['username'] . $user_id . rand(100,999)), array('uid' => $user_id));
		}
		if($user_id > 0) {
			unset($member['password']);
			$member['uid'] = $user_id;
			if (!empty($profile)) {
				$profile['uid'] = $user_id;
				$profile['createtime'] = TIMESTAMP;
				pdo_insert('users_profile', $profile);
			}
			$message_notice_log = array(
				'message' => $member['username'] . date("Y-m-d H:i:s") . '注册成功',
				'uid' => $user_id,
				'type' => MESSAGE_REGISTER_TYPE,
				'status' => $member['status'],
				'create_time' => TIMESTAMP
			);
			pdo_insert('message_notice_log', $message_notice_log);
			if ($member['register_type'] == USER_REGISTER_TYPE_MOBILE) {
				pdo_insert('users_bind', array('uid' => $user_id, 'bind_sign' => $member['openid'], 'third_type' => $member['register_type'], 'third_nickname' => $member['username']));
			}
			return error(0, '注册成功'.(!empty($_W['setting']['register']['verify']) ? '，请等待管理员审核！' : '，请重新登录！'));
		}
		return error(-1, '增加用户失败，请稍候重试或联系网站管理员解决！');
	}

	/**
	 * 第三方账号绑定  绑定qq  wechat
	 * @param $user
	 */
	public function userThirdInfoBind($user_info) {
		global $_W;
		$user_table = table('users');
		$user_id = pdo_getcolumn('users', array('openid' => $user_info['openid']), 'uid');
		$user_bind_info = $user_table->userBindInfo($user_info['openid'], $user_info['register_type']);

		if (!empty($user_id) || !empty($user_bind_info)) {
			return error(-1, '已被其他用户绑定，请更换账号');
		}
		pdo_insert('users_bind', array('uid' => $_W['uid'], 'bind_sign' => $user_info['openid'], 'third_type' => $user_info['register_type'], 'third_nickname' => strip_emoji($user_info['nickname'])));
		return true;
	}
}