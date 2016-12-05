<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 生成URL，统一生成方便管理
 * @param string $segment
 * @param array $params
 * @return string eg:(./index.php?c=*&a=*&do=*&...)
 */
function url($segment, $params = array()) {
	return wurl($segment, $params);
}

/**
 * 消息提示窗
 * @param string $msg
 * 提示消息内容
 *
 * @param string $redirect
 * 跳转地址
 *
 * @param string $type 提示类型
 * <pre>
 * success  成功
 * error    错误
 * info     提示(灯泡)
 * warning  警告(叹号)
 * ajax     json
 * sql
 * </pre>
 */
function message($msg, $redirect = '', $type = '') {
	global $_W, $_GPC;
	if($redirect == 'refresh') {
		$redirect = $_W['script_name'] . '?' . $_SERVER['QUERY_STRING'];
	}
	if($redirect == 'referer') {
		$redirect = referer();
	}
	if($redirect == '') {
		$type = in_array($type, array('success', 'error', 'info', 'warning', 'ajax', 'sql')) ? $type : 'info';
	} else {
		$type = in_array($type, array('success', 'error', 'info', 'warning', 'ajax', 'sql')) ? $type : 'success';
	}
	if ($_W['isajax'] || !empty($_GET['isajax']) || $type == 'ajax') {
		if($type != 'ajax' && !empty($_GPC['target'])) {
			exit("
<script type=\"text/javascript\">
parent.require(['jquery', 'util'], function($, util){
	var url = ".(!empty($redirect) ? 'parent.location.href' : "''").";
	var modalobj = util.message('".$msg."', '', '".$type."');
	if (url) {
		modalobj.on('hide.bs.modal', function(){\$('.modal').each(function(){if(\$(this).attr('id') != 'modal-message') {\$(this).modal('hide');}});top.location.reload()});
	}
});
</script>");
		} else {
			$vars = array();
			$vars['message'] = $msg;
			$vars['redirect'] = $redirect;
			$vars['type'] = $type;
			exit(json_encode($vars));
		}
	}
	if (empty($msg) && !empty($redirect)) {
		header('location: '.$redirect);
	}
	$label = $type;
	if($type == 'error') {
		$label = 'danger';
	}
	if($type == 'ajax' || $type == 'sql') {
		$label = 'warning';
	}
	include template('common/message', TEMPLATE_INCLUDEPATH);
	exit();
}

/**
 * 验证操作用户是否已登录
 * 
 * @return boolean
 */
function checklogin() {
	global $_W;
	if (empty($_W['uid'])) {
		message('抱歉，您无权进行该操作，请先登录！', url('user/login'), 'warning');
	}
	return true;
}

/**
 * 检查操作员是否已经选择一个公众号作为工作区域
 */
function checkaccount() {
	global $_W;
	if (empty($_W['uniacid'])) {
		message('这项功能需要你选择特定公众号才能使用！', url('account/display'), 'info');
	}
}

//新版buildframes
function buildframes($framename = ''){
	global $_W, $_GPC, $top_nav;
	$frames = require_once IA_ROOT . '/web/common/frames.inc.php';
	//@@todo 还需要进行数据库权限和菜单的组合
	
	//模块权限，创始人有所有模块权限
	load()->model('module');
	$modules = uni_modules();
	$sysmodules = system_modules();

	$account_module = pdo_getall('uni_account_modules', array('uniacid' => $_W['uniacid'], 'shortcut' => STATUS_ON), array('module'), '', 'displayorder DESC');
	if (!empty($account_module)) {
		foreach ($account_module as $module) {
			if (!in_array($module['module'], $sysmodules)) {
				$module = module_fetch($module['module']);
				if (!empty($module)) {
					$frames['account']['section']['platform_module']['menu']['platform_' . $module['name']] = array(
						'title' => $module['title'],
						'icon' =>  tomedia("addons/{$module['name']}/icon.jpg"),
						'url' => url('home/welcome/ext', array('m' => $module['name'])),
					);
				}
			}
		}
	}
	//@@todo 进入模块界面后权限
	$modulename = trim($_GPC['m']);
	$eid = intval($_GPC['eid']);
	if ((!empty($modulename) || !empty($eid)) && !in_array($modulename, system_modules())) {
		if(empty($modulename) && !empty($eid)) {
			$modulename = pdo_getcolumn('modules_bindings', array('eid' => $eid), 'module');
		}
		$module = module_fetch($modulename);
		$entries = module_entries($modulename);
		
		$frames['account']['section'] = array();
		if($module['settings']) {
			$frames['account']['section']['platform_module_common']['menu']['platform_module_settings'] = array(
				'title' => "<i class='fa fa-cog'></i> 参数设置",
				'url' => url('profile/module/setting', array('m' => $modulename)),
			);
		}
		if($entries['home']) {
			$frames['account']['section']['platform_module_common']['menu']['platform_module_home'] = array(
				'title' => "<i class='fa fa-home'></i> 微站首页导航",
				'url' => url('site/nav/home', array('m' => $modulename)),
			);
		}
		if($entries['profile']) {
			$frames['account']['section']['platform_module_common']['menu']['platform_module_profile'] = array(
				'title' => "<i class='fa fa-user'></i> 个人中心导航",
				'url' => url('site/nav/profile', array('m' => $modulename)),
			);
		}
		if($entries['shortcut']) {
			$frames['account']['section']['platform_module_common']['menu']['platform_module_shortcut'] = array(
				'title' => "<i class='fa fa-plane'></i> 快捷菜单",
				'url' => url('site/nav/shortcut', array('m' => $modulename)),
			);
		}
		if($module['isrulefields'] || !empty($entries['cover']) || !empty($entries['mine'])) {
			if (!empty($module['isrulefields'])) {
				$url = url('platform/reply', array('m' => $modulename));
			}
			if (empty($url) && !empty($entries['cover'])) {
				$url = url('platform/cover', array('eid' => $entries['cover'][0]['eid']));
			}
			$frames['account']['section']['platform_module_common']['menu']['platform_module_entry'] = array(
				'title' => "<i class='fa fa-plane'></i> 应用入口",
				'url' => $url,
			);
		}
		if (!empty($entries['menu'])) {
			$frames['account']['section']['platform_module_menu']['title'] = '业务菜单';
			foreach($entries['menu'] as $key => $row) {
				if(empty($row)) continue;
				foreach($row as $li) {
					$frames['account']['section']['platform_module_menu']['menu']['platform_module_menu'.$row['eid']] = array(
						'title' => "<i class='fa fa-plane'></i> {$row['title']}",
						'url' => url('site/nav/shortcut', array('m' => $modulename)),
					);
				}
			}
		}
	}

	//@@todo 操作员界面菜单
	if (!empty($_W['role']) && $_W['role'] == 'clerk') {
		
	}
	foreach ($frames as $menuid => $menu) {
		$top_nav[] = array(
			'title' => $menu['title'],
			'name' => $menuid,
			'url' => $menu['url'],
		);
	}
	return !empty($framename) ? $frames[$framename] : $frames;
}

function system_modules() {
	return array(
		'basic', 'news', 'music', 'userapi', 'recharge', 'images', 'video', 'voice', 'wxcard',
		'custom', 'chats', 'paycenter', 'keyword', 'special', 'welcome', 'default', 'apply', 'reply'
	);
}

/**
 * 在当前URL上拼接查询参数，生成url
 *  @param string $params 需要拼接的参数。例如："time:1,group:2"，会在当前URL上加上&time=1&group=2
 * */
function filter_url($params) {
	global $_W;
	if(empty($params)) {
		return '';
	}
	$query_arr = array();
	$parse = parse_url($_W['siteurl']);
	if(!empty($parse['query'])) {
		$query = $parse['query'];
		parse_str($query, $query_arr);
	}
	$params = explode(',', $params);
	foreach($params as $val) {
		if(!empty($val)) {
			$data = explode(':', $val);
			$query_arr[$data[0]] = trim($data[1]);
		}
	}
	$query_arr['page'] = 1;
	$query = http_build_query($query_arr);
	return './index.php?' . $query;
}

/**
 * 云注册信息完善提示
 */
function site_profile_perfect_tips(){
	global $_W;
	
	if ($_W['isfounder'] && (empty($_W['setting']['site']) || empty($_W['setting']['site']['profile_perfect']))) {
		if (!defined('SITE_PROFILE_PERFECT_TIPS')) {
			$url = url('cloud/profile');
			return <<<EOF
$(function() {
	var html = 
		'<div id="siteinfo-tips" class="upgrade-tips">'+
			'<a href="{$url}" target="_blank">请尽快完善您在微擎云服务平台的站点注册信息。</a>'+
		'</div>';
	$('body').prepend(html);
});
EOF;
			define('SITE_PROFILE_PERFECT_TIPS', true);
		}
	}
	return '';
}
function _forward($c, $a) {
	$file = IA_ROOT . '/web/source/' . $c . '/' . $a . '.ctrl.php';
	return $file;
}

function _calc_current_frames(&$frames) {
	global $controller, $action;
	if(!empty($frames['section']) && is_array($frames['section'])) {
		foreach($frames['section'] as &$frame) {
			if(empty($frame['menu'])) continue;
			foreach($frame['menu'] as &$menu) {
				$query = parse_url($menu['url'], PHP_URL_QUERY);
				parse_str($query, $urls);
				if(empty($urls)) continue;
				if(defined('ACTIVE_FRAME_URL')) {
					$query = parse_url(ACTIVE_FRAME_URL, PHP_URL_QUERY);
					parse_str($query, $get);
				} else {
					$get = $_GET;
					$get['c'] = $controller;
					$get['a'] = $action;
				}
				if(!empty($do)) {
					$get['do'] = $do;
				}

				$diff = array_diff_assoc($urls, $get);
				if(empty($diff)) {
					$menu['active'] = ' active';
				}
			}
		}
	}
}