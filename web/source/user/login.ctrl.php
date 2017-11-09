<?php
/**
 * 用户登录
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
define('IN_GW', true);

load()->model('user');
load()->classs('weixin.account');
load()->classs('qq.platform');
$qq = new QqPlatform();

if (checksubmit() || $_W['isajax']) {
	_login($_GPC['referer']);
}

if ($_GPC['login_type'] == 'qq') {
	_login($_GPC['referer'], 'qq', $_GPC['state'], $_GPC['code']);
}

$qq_login_url = $qq->getAuthLoginUrl();
$setting = $_W['setting'];
if(!empty($setting['wechat_platform']) && !empty($setting['wechat_platform']['authstate'])) {
	if ($_GPC['login_type'] == 'wechat' && !empty($_GPC['code']) && $_GPC['state'] == $_W['token']) {
		_login($_GPC['referer'], 'wechat', $_GPC['state'], $_GPC['code']);
	}
	$account = array(
		'acid' => 'invent',
		'key' => $setting['wechat_platform']['appid'],
		'secret' => $setting['wechat_platform']['appsecret'],
	);
	$account_obj = new WeiXinAccount($account);
	$wechat_login_url = $account_obj->getWechatLoginUrl($_W['siteurl'] . 'login_type=wechat', $_W['token']);
}
template('user/login');

function _login($forward = '', $login_type = '', $state = '', $code = '') {
	global $_GPC, $_W;
	user_expire_notice();
	$member = array();
	if (empty($login_type)) {
		$username = trim($_GPC['username']);
		pdo_query('DELETE FROM'.tablename('users_failed_login'). ' WHERE lastupdate < :timestamp', array(':timestamp' => TIMESTAMP-300));
		$failed = pdo_get('users_failed_login', array('username' => $username, 'ip' => CLIENT_IP));
		if ($failed['count'] >= 5) {
			itoast('输入密码错误次数超过5次，请在5分钟后再登录',referer(), 'info');
		}
		if (!empty($_W['setting']['copyright']['verifycode'])) {
			$verify = trim($_GPC['verify']);
			if (empty($verify)) {
				itoast('请输入验证码', '', '');
			}
			$result = checkcaptcha($verify);
			if (empty($result)) {
				itoast('输入验证码错误', '', '');
			}
		}
		if (empty($username)) {
			itoast('请输入要登录的用户名', '', '');
		}
		$member['username'] = $username;
		$member['password'] = $_GPC['password'];
		if (empty($member['password'])) {
			itoast('请输入密码', '', '');
		}
	} else {
		$member = user_third_login($state, $code, $login_type);
		if (is_error($member)) {
			itoast($member['message']);
		}
	}

	$record = user_single($member);
	if (!empty($record)) {
		if ($record['status'] == USER_STATUS_CHECK || $record['status'] == USER_STATUS_BAN) {
			itoast('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！', '', '');
		}
		$_W['uid'] = $record['uid'];
		$_W['isfounder'] = user_is_founder($record['uid']);
		$_W['user'] = $record;

		if (empty($_W['isfounder']) || user_is_vice_founder()) {
			if (!empty($record['endtime']) && $record['endtime'] < TIMESTAMP) {
				itoast('您的账号有效期限已过，请联系网站管理员解决！', '', '');
			}
		}
		if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
			itoast('站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason'], '', '');
		}
		$cookie = array();
		$cookie['uid'] = $record['uid'];
		$cookie['lastvisit'] = $record['lastvisit'];
		$cookie['lastip'] = $record['lastip'];
		$cookie['hash'] = md5($record['password'] . $record['salt']);
		$session = authcode(json_encode($cookie), 'encode');
		isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
		$status = array();
		$status['uid'] = $record['uid'];
		$status['lastvisit'] = TIMESTAMP;
		$status['lastip'] = CLIENT_IP;
		user_update($status);

		if (empty($forward)) {
			$forward = user_login_forward($_GPC['forward']);
		}
		// 只能跳到本域名下
		$forward = check_url_not_outside_link($forward);

		if ($record['uid'] != $_GPC['__uid']) {
			isetcookie('__uniacid', '', -7 * 86400);
			isetcookie('__uid', '', -7 * 86400);
		}
		pdo_delete('users_failed_login', array('id' => $failed['id']));
		itoast("欢迎回来，{$record['username']}。", $forward, 'success');
	} else {
		if (empty($failed)) {
			pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => $username, 'count' => '1', 'lastupdate' => TIMESTAMP));
		} else {
			pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
		}
		itoast('登录失败，请检查您输入的用户名和密码！', '', '');
	}
}