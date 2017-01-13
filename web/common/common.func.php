<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');

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
	var url = ".(!empty($redirect) ? 'parent.location.href' : "''").";
	var modalobj = util.message('".$msg."', '', '".$type."');
	if (url) {
		modalobj.on('hide.bs.modal', function(){\$('.modal').each(function(){if(\$(this).attr('id') != 'modal-message') {\$(this).modal('hide');}});top.location.reload()});
	}
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
	
	$message = array();
	if (is_array($msg)){
		$message['title'] = 'MYSQL 错误';
		$message['msg'] = 'php echo cutstr(' . $msg['sql'] . ', 300, 1);';
	} else{
		$message['title'] = $caption;
		$message['msg'] = $msg;
	}
	$message['type'] = $label;
	$redirect = $redirect ? $redirect : referer();
	$message['redirect'] = $redirect;
	$message['msg'] = rawurlencode($message['msg']);
	isetcookie("message", stripslashes(json_encode($message, JSON_UNESCAPED_UNICODE)), 600);
	
	if ($label == 'success'){
		header('Location: ' . $redirect);
		exit;
	}
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
	$frames = cache_load('system_frame');
	if(empty($frames)) {
		cache_build_frame_menu();
		$frames = cache_load('system_frame');
	}
	
	//模块权限，创始人有所有模块权限
	$modules = uni_modules(false);
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
						'is_display' => 1,
					);
				}
			}
		}
	} elseif (!empty($modules)) {
		$new_modules = array_reverse($modules);
		$i = 0;
		foreach ($new_modules as $module) {
			if (!empty($module['issystem'])) {
				continue;
			}
			if ($i == 5) {
				break;
			}
			$frames['account']['section']['platform_module']['menu']['platform_' . $module['name']] = array(
				'title' => $module['title'],
				'icon' =>  tomedia("addons/{$module['name']}/icon.jpg"),
				'url' => url('home/welcome/ext', array('m' => $module['name'])),
				'is_display' => 1,
			);
			$i++;
		}
	}
	if (array_diff(array_keys($modules), $sysmodules)) {
		$frames['account']['section']['platform_module']['menu']['platform_module_more'] = array(
			'title' => '更多应用',
			'url' => url('profile/module'),
			'is_display' => 1,
		);
	} else {
		$frames['account']['section']['platform_module']['is_display'] = false;
	}
	//进入模块界面后权限
	$modulename = trim($_GPC['m']);
	$eid = intval($_GPC['eid']);
	if ((!empty($modulename) || !empty($eid)) && !in_array($modulename, system_modules())) {
		if(empty($modulename) && !empty($eid)) {
			$modulename = pdo_getcolumn('modules_bindings', array('eid' => $eid), 'module');
		}
		$module = module_fetch($modulename);
		$entries = module_entries($modulename);
		$frames['account']['section'] = array();
		if($module['isrulefields'] || !empty($entries['cover']) || !empty($entries['mine'])) {
			if (!empty($module['isrulefields'])) {
				$url = url('platform/reply', array('m' => $modulename));
			}
			if (empty($url) && !empty($entries['cover'])) {
				$url = url('platform/cover', array('eid' => $entries['cover'][0]['eid']));
			}
			$frames['account']['section']['platform_module_common']['menu']['platform_module_entry'] = array(
				'title' => "<i class='wi wi-reply'></i> 应用入口",
				'url' => $url,
				'is_display' => 1,
			);
		}
		if($module['settings']) {
			$frames['account']['section']['platform_module_common']['menu']['platform_module_settings'] = array(
				'title' => "<i class='fa fa-cog'></i> 参数设置",
				'url' => url('profile/module/setting', array('m' => $modulename)),
				'is_display' => 1,
			);
		}
		if($entries['home']) {
			$frames['account']['section']['platform_module_common']['menu']['platform_module_home'] = array(
				'title' => "<i class='fa fa-home'></i> 微站首页导航",
				'url' => url('site/nav/home', array('m' => $modulename)),
				'is_display' => 1,
			);
		}
		if($entries['profile']) {
			$frames['account']['section']['platform_module_common']['menu']['platform_module_profile'] = array(
				'title' => "<i class='fa fa-user'></i> 个人中心导航",
				'url' => url('site/nav/profile', array('m' => $modulename)),
				'is_display' => 1,
			);
		}
		if($entries['shortcut']) {
			$frames['account']['section']['platform_module_common']['menu']['platform_module_shortcut'] = array(
				'title' => "<i class='fa fa-plane'></i> 快捷菜单",
				'url' => url('site/nav/shortcut', array('m' => $modulename)),
				'is_display' => 1,
			);
		}
		if (!empty($entries['cover'])) {
			foreach ($entries['cover'] as $key => $menu) {
				$frames['account']['section']['platform_module_common']['menu']['platform_module_cover'][] = array(
					'title' => "{$menu['title']}",
					'url' => url('platform/cover', array('eid' => $menu['eid'])),
					'is_display' => 0,
				);
			}
		}
		if (!empty($entries['menu'])) {
			$frames['account']['section']['platform_module_menu']['title'] = '业务菜单';
			foreach($entries['menu'] as $key => $row) {
				if(empty($row)) continue;
				foreach($row as $li) {
					$frames['account']['section']['platform_module_menu']['menu']['platform_module_menu'.$row['eid']] = array(
						'title' => "<i class='wi wi-appsetting'></i> {$row['title']}",
						'url' => url('site/entry/', array('eid' => $row['eid'])),
						'is_display' => 1,
					);
				}
			}
		}
	}
	//从数据库中获取用户权限，并附加上系统管理中的权限
	//仅当系统管理时才使用预设权限
	if (!empty($_W['role']) && ($_W['role'] == ACCOUNT_MANAGE_NAME_OPERATOR || $_W['role'] == ACCOUNT_MANAGE_NAME_MANAGER)) {
		$user_permission = uni_user_permission('system');
	}
	//@@todo 店员界面菜单
	if (!empty($_W['role']) && $_W['role'] == 'clerk') {
		
	}
	
	if (!empty($user_permission)) {
		foreach ($frames as $nav_id => $section) {
			if (empty($section['section'])) {
				continue;
			}
			foreach ($section['section'] as $section_id => $secion) {
				$section_show = false;
				foreach ($secion['menu'] as $menu_id => $menu) {
					if (!in_array($menu['permission_name'], $user_permission)) {
						$frames[$nav_id]['section'][$section_id]['menu'][$menu_id]['is_display'] = false;
					} else {
						$section_show = true;
					}
				}
				if (!isset($frames[$nav_id]['section'][$section_id]['is_display'])) {
					$frames[$nav_id]['section'][$section_id]['is_display'] = $section_show;
				}
			}
		}
	}
	
	foreach ($frames as $menuid => $menu) {
		if (!empty($menu['founder']) && empty($_W['isfounder'])) {
			continue;
		}
		$top_nav[] = array(
			'title' => $menu['title'],
			'name' => $menuid,
			'url' => $menu['url'],
			'blank' => $menu['blank'],
		);
	}
	return !empty($framename) ? $frames[$framename] : $frames;
}

function system_modules() {
	return array(
		'basic', 'news', 'music', 'userapi', 'recharge', 'images', 'video', 'voice', 'wxcard',
		'custom', 'chats', 'paycenter', 'keyword', 'special', 'welcome', 'default', 'apply', 'reply', 'core'
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
 * 系统菜单中预设的附加权限
 */
function frames_menu_append() {
	$system_menu_default_permission = array(
		'founder' => array(),
		'manager' => array(
			'system_account',
			'system_platform',
			'system_module',
			'system_module_group',
			'system_my',
			'system_setting_updatecache',
		),
		'operator' => array(
			'system_account',
			'system_my',
			'system_setting_updatecache',
		),
		'clerk' => array(),
	);
	return $system_menu_default_permission;
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
	    '<div class="we7-body-alert">'+
            '<div class="container">'+
                '<div class="alert alert-info">'+
                    '<i class="wi wi-info-sign"></i>'+
                    '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true" class="wi wi-error-sign"></span><span class="sr-only">Close</span></button>'+
                    '<a href="{$url}" target="_blank">请尽快完善您在微擎云服务平台的站点注册信息。</a>'+
                '</div>'+
            '</div>'+
        '</div>';
	$('body').prepend(html);
});
EOF;
			define('SITE_PROFILE_PERFECT_TIPS', true);
		}
	}
	return '';
}