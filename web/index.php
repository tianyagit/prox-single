<?php
/**
 * 路由控制器
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
define('IN_SYS', true);
require '../framework/bootstrap.inc.php';
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';

if (!empty($_GPC['state'])) {
	$login_callback_params = OAuth2Client::supportParams($_GPC['state']);
	if (!empty($login_callback_params)) {
		$controller = 'user';
		$action = 'login';
		$_GPC['login_type'] = $login_callback_params['from'];
		$_GPC['handle_type'] = $login_callback_params['mode'];
	}
}

if (empty($_W['isfounder']) && !empty($_W['user']) && ($_W['user']['status'] == USER_STATUS_CHECK || $_W['user']['status'] == USER_STATUS_BAN)) {
	message('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！', url('user/login'), 'info');
}
$acl = require IA_ROOT . '/web/common/permission.inc.php';

// navs
$_W['page'] = array();
$_W['page']['copyright'] = $_W['setting']['copyright'];
// navs end;

if (($_W['setting']['copyright']['status'] == 1) && empty($_W['isfounder']) && $controller != 'cloud' && $controller != 'utility' && $controller != 'account') {
	$_W['siteclose'] = true;
	if ($controller == 'account' && $action == 'welcome') {
		template('account/welcome');
		exit();
	}
	if ($controller == 'user' && $action == 'login') {
		if (checksubmit()) {
			require _forward($controller, $action);
		}
		template('user/login');
		exit();
	}
	isetcookie('__session', '', - 10000);
	/* vstart */
	if (IMS_FAMILY == 'v') {
		message('站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason'], url('user/login'), 'info');
	}
	/* vend */
	/* sxstart */
	if (IMS_FAMILY == 's' || IMS_FAMILY == 'x') {
		message('站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason'], url('account/welcome'), 'info');
	}
	/* sxend */
}

$controllers = array();
$handle = opendir(IA_ROOT . '/web/source/');
if (!empty($handle)) {
	while ($dir = readdir($handle)) {
		if ($dir != '.' && $dir != '..') {
			$controllers[] = $dir;
		}
	}
}
if (!in_array($controller, $controllers)) {
	$controller = 'home';
}

$init = IA_ROOT . "/web/source/{$controller}/__init.php";
if (is_file($init)) {
	require $init;
}

$actions = array();
$actions_path = file_tree(IA_ROOT . '/web/source/' . $controller);
foreach ($actions_path as $action_path) {
	$action_name = str_replace('.ctrl.php', '', basename($action_path));

	$section = basename(dirname($action_path));
	if ($section !== $controller) {
		$action_name = $section . '-' .$action_name;
	}
	$actions[] = $action_name;
}

if (empty($actions)) {
	header('location: ?refresh');
}

//section可以省略，如果不在列表中，加上同名section后看是否可以使用
if (!in_array($action, $actions)) {
	$action = $action . '-' . $action;
}
if (!in_array($action, $actions)) {
	$action = $acl[$controller]['default'] ? $acl[$controller]['default'] : $actions[0];
}

if (is_array($acl[$controller]['direct']) && in_array($action, $acl[$controller]['direct'])) {
	// 如果这个目标被配置为不需要登录直接访问, 则直接访问
	require _forward($controller, $action);
	exit();
}
checklogin();
// 判断非创始人是否拥有目标权限
if ($_W['role'] != ACCOUNT_MANAGE_NAME_FOUNDER) {
	if ($_W['role'] == ACCOUNT_MANAGE_NAME_UNBIND_USER) {
		itoast('', url('user/third-bind'));
	}
	if (empty($_W['uniacid'])) {
		if (defined('FRAME') && FRAME == 'account') {
			itoast('', url('account/display', array('type' => ACCOUNT_TYPE_SIGN)), 'info');
		}
		if (defined('FRAME') && FRAME == 'wxapp') {
			itoast('', url('account/display', array('type' => WXAPP_TYPE_SIGN)), 'info');
		}
	}
	$acl = permission_build();
	if (empty($acl[$controller][$_W['role']]) || (!in_array($controller.'*', $acl[$controller][$_W['role']]) && !in_array($action, $acl[$controller][$_W['role']]))) {
		message('不能访问, 需要相应的权限才能访问！');
	}
}
// 用户权限判断
require _forward($controller, $action);

define('ENDTIME', microtime());
// 将运行速度过慢页面存入日志表
if (empty($_W['config']['setting']['maxtimeurl'])) {
	$_W['config']['setting']['maxtimeurl'] = 10;
}
if ((ENDTIME - STARTTIME) > $_W['config']['setting']['maxtimeurl']) {
	$data = array(
		'type' => '1',
		'runtime' => ENDTIME - STARTTIME,
		'runurl' => $_W['sitescheme'] . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		'createtime' => TIMESTAMP
	);
	pdo_insert('core_performance', $data);
}
function _forward($c, $a) {
	$file = IA_ROOT . '/web/source/' . $c . '/' . $a . '.ctrl.php';
	if (!file_exists($file)) {
		list($section, $a) = explode('-', $a);
		$file = IA_ROOT . '/web/source/' . $c . '/' . $section . '/' . $a . '.ctrl.php';
	}
	return $file;
}
function _calc_current_frames(&$frames) {
	global $controller, $action;
	if (!empty($frames['section']) && is_array($frames['section'])) {
		foreach ($frames['section'] as &$frame) {
			if (empty($frame['menu'])) {
				continue;
			}
			foreach ($frame['menu'] as $key => &$menu) {
				$query = parse_url($menu['url'], PHP_URL_QUERY);
				parse_str($query, $urls);
				if (empty($urls)) {
					continue;
				}
				if (defined('ACTIVE_FRAME_URL')) {
					$query = parse_url(ACTIVE_FRAME_URL, PHP_URL_QUERY);
					parse_str($query, $get);
				} else {
					$get = $_GET;
					$get['c'] = $controller;
					$get['a'] = $action;
				}
				if (!empty($do)) {
					$get['do'] = $do;
				}
				$diff = array_diff_assoc($urls, $get);
				if (empty($diff) || $get['c'] == 'profile' && $get['a'] == 'reply-setting' && $key == 'platform_reply') {
					$menu['active'] = ' active';
				}
			}
		}
	}
}