<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */

//小程序打包的模块内重定向URL
$uniacid_resource_exist = strpos($_SERVER['HTTP_REFERER'], '&uniacid_resource=wxapp');
if ( !empty($uniacid_resource_exist) && (($controller == 'platform' && ($action == 'reply' || $action == 'cover')) || ($controller == 'profile' && $action == 'module' && $a == 'setting') || ($controller == 'site' && ($action == 'nav' || $action == 'entry'))) ) {
	header('Location: ./web/index.php?' . $_SERVER['QUERY_STRING']. 'uniacid_source=wxapp&uniacid='. $_GPC['__uniacid']);
}

load()->model('user');
load()->func('tpl');
$_W['token'] = token();
$session = json_decode(base64_decode($_GPC['__session']), true);
if(is_array($session)) {
	$user = user_single(array('uid'=>$session['uid']));
	if(is_array($user) && $session['hash'] == md5($user['password'] . $user['salt'])) {
		$_W['uid'] = $user['uid'];
		$_W['username'] = $user['username'];
		$user['currentvisit'] = $user['lastvisit'];
		$user['currentip'] = $user['lastip'];
		$user['lastvisit'] = $session['lastvisit'];
		$user['lastip'] = $session['lastip'];
		$_W['user'] = $user;
		$founders = explode(',', $_W['config']['setting']['founder']);
		$_W['isfounder'] = in_array($_W['uid'], $founders);
		unset($founders);
	} else {
		isetcookie('__session', false, -100);
	}
	unset($user);
}
unset($session);

if(!empty($_GPC['__uniacid'])) {
	$cache_key = cache_system_key("{$_W['username']}:lastaccount");
	$cache_lastaccount = cache_load($cache_key);
	if (in_array($controller, array('wxapp'))) {
		$uniacid = $cache_lastaccount['wxapp'];
	} else {
		if ( (!empty($_GPC['uniacid_source']) && $_GPC['uniacid_source'] == 'wxapp') ) {
			$uniacid = intval($_GPC['uniacid']);
		} else {
			$uniacid = $cache_lastaccount['account'];
		}
	}
	$_W['uniacid'] = $uniacid;
	$_W['uniaccount'] = $_W['account'] = uni_fetch($_W['uniacid']);
	$_W['acid'] = $_W['account']['acid'];
	$_W['weid'] = $_W['uniacid'];
}
if(!empty($_W['uid'])) {
	$_W['role'] = uni_permission($_W['uid']);
}
$_W['template'] = 'default';
if(!empty($_W['setting']['basic']['template'])) {
	$_W['template'] = $_W['setting']['basic']['template'];
}
load()->func('compat.biz');