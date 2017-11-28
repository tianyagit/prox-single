<?php
/**
 * 用户登录
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
define('IN_GW', true);

load()->model('user');
load()->classs('oauth2/oauth2client');
load()->model('setting');

if (checksubmit() || $_W['isajax']) {
	_login($_GPC['referer']);
}

if (in_array($_GPC['login_type'], array('qq', 'wechat'))) {
	_login($_GPC['referer']);
}

$setting = $_W['setting'];
$_GPC['login_type'] = !empty($_GPC['login_type']) ? $_GPC['login_type'] : (!empty($_W['setting']['copyright']['login_type']) ? 'mobile': 'system');
$login_urls = user_support_urls();
template('user/login');

function _login($forward = '') {
	global $_GPC, $_W;
	$setting_sms_sign = setting_load('site_sms_sign');
	$status = !empty($setting_sms_sign['site_sms_sign']['status']) ? $setting_sms_sign['site_sms_sign']['status'] : '';
	if (!empty($status)) {
		user_expire_notice();
	}
	if (empty($_GPC['login_type'])) {
		$_GPC['login_type'] = 'system';
	}

	if (!empty($_W['user']) && in_array($_GPC['login_type'], array('qq', 'wechat'))) {
		$member = OAuth2Client::create($_GPC['login_type'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appid'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appsecret'])->bind();
	} else {
		$member = OAuth2Client::create($_GPC['login_type'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appid'], $_W['setting']['thirdlogin'][$_GPC['login_type']]['appsecret'])->login();
	}

	if (is_error($member)) {
		itoast($member['message'], url('user/login'), '');
	}

	$record = user_single($member);
	if (!empty($record)) {
		if ($record['status'] == USER_STATUS_CHECK || $record['status'] == USER_STATUS_BAN) {
			itoast('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！', url('user/login'), '');
		}
		$_W['uid'] = $record['uid'];
		$_W['isfounder'] = user_is_founder($record['uid']);
		$_W['user'] = $record;

		/* xstart */
		if (IMS_FAMILY == 'x') {
			if (empty($_W['isfounder']) || user_is_vice_founder()) {
				if (!empty($record['endtime']) && $record['endtime'] < TIMESTAMP) {
					itoast('您的账号有效期限已过，请联系网站管理员解决！', '', '');
				}
			}
		}
		/* xend */

		/* vstart */
		if (IMS_FAMILY == 'v') {
			if (empty($_W['isfounder'])) {
				if (!empty($record['endtime']) && $record['endtime'] < TIMESTAMP) {
					itoast('您的账号有效期限已过，请联系网站管理员解决！', '', '');
				}
			}
		}
		/* vend */
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
		$forward = safe_url_not_outside($forward);

		if ($record['uid'] != $_GPC['__uid']) {
			isetcookie('__uniacid', '', -7 * 86400);
			isetcookie('__uid', '', -7 * 86400);
		}
		$failed = pdo_get('users_failed_login', array('username' => trim($_GPC['username']), 'ip' => CLIENT_IP));
		pdo_delete('users_failed_login', array('id' => $failed['id']));
		user_account_expire_message_record();
		itoast("欢迎回来，{$record['username']}。", $forward, 'success');
	} else {
		if (empty($failed)) {
			pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => trim($_GPC['username']), 'count' => '1', 'lastupdate' => TIMESTAMP));
		} else {
			pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
		}
		itoast('登录失败，请检查您输入的账号和密码！', '', '');
	}
}