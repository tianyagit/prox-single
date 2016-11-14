<?php
/**
 * 设置模块启用停用，并显示模块到快捷菜单中
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('account');

$dos = array('display', 'setting', 'shortcut', 'enable');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

if($do != 'setting') {
	uni_user_permission_check('profile_module');
}
$modulelist = uni_modules(false);

if($do == 'display') {
	$_W['page']['title'] = '公众号 - 应用模块 - 更多应用';
	$pinyinlist = array();
	$shortcuts = pdo_getall('uni_account_modules', array('uniacid' => $_W['uniacid'], 'display' => STATUS_ON), array('module'), 'module', 'displayorder DESC');
	if(!empty($modulelist)) {
		foreach($modulelist as $i => &$module) {
			if (!empty($_W['setting']['permurls']['modules']) && !in_array($module['name'], $_W['setting']['permurls']['modules']) || $module['issystem']) {
				unset($modulelist[$i]);
				continue;
			}
			$module['first_pinyin'] = get_first_char($module['title']);
			$module['shortcut'] = !empty($shortcuts[$module['name']]);
			$module['official'] = empty($module['issystem']) && (strexists($module['author'], 'WeEngine Team') || strexists($module['author'], '微擎团队'));
			$preview = '../addons/' . $module['name'] . '/preview-custom.jpg';
			if(!file_exists($preview)) {
				$preview = $path . '/preview.jpg';
			}
			$module['preview'] = $preview;
			$pinyinlist[$module['first_pinyin']] = $module['first_pinyin'];
		}
		unset($module);
	}
	sort($pinyinlist);
	template('profile/module');
} elseif ($do == 'shortcut') {
	$status = intval($_GPC['shortcut']);
	$modulename = $_GPC['modulename'];
	$module = module_fetch($modulename);
	if(empty($module)) {
		message('抱歉，你操作的模块不能被访问！');
	}
	
	$module_status = pdo_get('uni_account_modules', array('module' => $modulename, 'uniacid' => $_W['uniacid']), array('id', 'display'));
	if (empty($module_status)) {
		$data = array(
			'uniacid' => $_W['uniacid'],
			'module' => $modulename,
			'enabled' => STATUS_OFF,
			'display' => $status,
			'settings' => '',
		);
		pdo_insert('uni_account_modules', $data);
	} else {
		$data = array(
			'display' => $status ? STATUS_ON : STATUS_OFF,
		);
		pdo_update('uni_account_modules', $data, array('id' => $module_status['id']));
	}
	if ($status) {
		message('添加模块快捷操作成功！', referer(), 'success');
	} else {
		message('取消模块快捷操作成功！', referer(), 'success');
	}
} elseif ($do == 'enable') {
	$modulename = $_GPC['modulename'];
	if(empty($modulelist[$modulename])) {
		message('抱歉，你操作的模块不能被访问！');
	}
	pdo_update('uni_account_modules', array(
		'enabled' => empty($_GPC['enabled']) ? STATUS_OFF : STATUS_ON,
	), array(
		'module' => $modulename,
		'uniacid' => $_W['uniacid']
	));
	cache_build_account_modules();
	message('模块操作成功！', referer(), 'success');
} elseif ($do == 'top') {
	$modulename = $_GPC['modulename'];
	$module = $modulelist[$modulename];
	if(empty($module)) {
		message('抱歉，你操作的模块不能被访问！');
	}
	
	$max_displayorder = pdo_getcolumn('uni_account_modules', array(), 'MAX(displayorder) as displayorder');
	print_r($max_displayorder);exit;
} elseif ($do == 'setting') {
	//@@todo 模块设置处还未优化
	$modulename = $_GPC['modulename'];
	$module = $modulelist[$modulename];
	if(empty($module)) {
		message('抱歉，你操作的模块不能被访问！');
	}
	//@@todo 权限判断还没有优化
	if(!uni_user_module_permission_check($modulename.'_settings', $modulename)) {
		message('您没有权限进行该操作');
	}
	define('CRUMBS_NAV', 1);
	$ptr_title = '参数设置';
	$module_types = module_types();
	
	$config = $module['config'];
	if (($module['settings'] == 2) && !is_file(IA_ROOT."/addons/{$module['name']}/developer.cer")) {
		
		if (empty($_W['setting']['site']['key']) || empty($_W['setting']['site']['token'])) {
			message('站点未注册，请先注册站点。', url('cloud/profile'), 'info');
		}
		
		if (empty($config)) {
			$config = array();
		}
		
		load()->model('cloud');
		load()->func('communication');
		
		$pro_attach_url = tomedia('pro_attach_url');
		$pro_attach_url = str_replace('pro_attach_url', '', $pro_attach_url);
		
		$module_simple = array_elements(array('name', 'type', 'title', 'version', 'settings'), $module);
		$module_simple['pro_attach_url'] = $pro_attach_url;
		
		$iframe = cloud_module_setting_prepare($module_simple, 'setting');
		$result = ihttp_post($iframe, array('inherit_setting' => base64_encode(iserializer($config))));
		if (is_error($result)) {
			message($result['message']);
		}
		$result = json_decode($result['content'], true);
		if (is_error($result)) {
			message($result['message']);
		}
		
		$module_simple = array_elements(array('name', 'type', 'title', 'version', 'settings'), $module);
		$module_simple['pro_attach_url'] = $pro_attach_url;
		
		$iframe = cloud_module_setting_prepare($module_simple, 'setting');
		template('profile/module_setting');
		exit();
	}
	$obj = WeUtility::createModule($module['name']);
	$obj->settingsDisplay($config);
	exit();
}