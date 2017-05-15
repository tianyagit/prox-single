<?php
/**
 * 创建小程序
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('wxapp');

$dos = array('design_method', 'post', 'get_wxapp_modules', 'getpackage', 'getlink');
$do = in_array($do, $dos) ? $do : 'post';
$_W['page']['title'] = '小程序 - 新建版本';


if ($do == 'design_method') {
	template('wxapp/design_method');
}

if ($do == 'getlink') {
	if (!empty($_GPC['module'])) {
		foreach ($_GPC['module'] as $key=>&$val) {
			$eids .= ',' . $val['url'];
			$selected_modules[$val['module']] = $val;
		}
	}
	$eids = explode(',', $eids);
	if (!empty($eids)) {
		foreach ($eids as $k => $eid) {
			if (!empty($eid)) {
				$bindings_info = pdo_get('modules_bindings', array('eid' => $eid));
				$show_urls[$eid] = $bindings_info;
				$show_urls[$eid]['module'] = $selected_modules[$bindings_info['module']];
			}
		}
	}
	iajax(0, $show_urls, '');
}

if($do == 'post') {
	$uniacid = intval($_GPC['uniacid']);
	$design_method = intval($_GPC['design_method']);
	
	if (checksubmit()) {
		if (empty($_GPC['name'])) {
			itoast('请填写小程序名称', url('wxapp/post'), 'error');
		}
		if ($design_method == WXAPP_TEMPLATE && empty($_GPC['select_modules'])) {
			itoast('请选择要打包的模块应用', url('wxapp/post'), 'error');
		}
		//新建小程序公众号
		if (empty($uniacid)) {
			$account_wxapp_data = array(
				'name' => trim($_GPC['name']),
				'account' => trim($_GPC['account']),
				'original' => trim($_GPC['original']),
				'level' => 1,
				'key' => trim($_GPC['key']),
				'secret' => trim($_GPC['secret']),
				'type' => ACCOUNT_TYPE_APP_NORMAL,
			);
			$uniacid = wxapp_account_create($account_wxapp_data);
			if(is_error($uniacid)) {
				itoast('添加小程序信息失败', url('wxapp/post'), 'error');
			}
		}
		
		//小程序版本信息，打包多模块时，每次更改需要重建版本
		//打包单模块时，每添加一个模块算是一个版本
		$wxapp_version = array(
			'uniacid' => $uniacid,
			'multiid' => '0',
			'version' => implode('.', wxapp_version_parser($_GPC['version0'], $_GPC['version1'], $_GPC['version2'])),
			'modules' => '',
			'design_method' => $design_method,
			'quickmenu' => '',
			'createtime' => TIMESTAMP,
			'template' => '',
		);
		
		//多模块打包，每个版本对应一个微官网
		if ($design_method == WXAPP_TEMPLATE) {
			$multi_data = array(
				'uniacid' => $uniacid,
				'title' => $account_wxapp_data['name'],
				'styleid' => 0,
			);
			pdo_insert('site_multi', $multi_data);
			$wxapp_version['multiid'] = pdo_insertid();
		}
		//打包模块
		if (!empty($_GPC['select_modules'])) {
			$select_modules = array();
			foreach ($_GPC['select_modules'] as $module) {
				$module = module_fetch($module);
				$select_modules[] = array($module['name'], $module['version']);
			}
			$wxapp_version['modules'] = serialize($select_modules);
		}
		pdo_insert('wxapp_versions', $wxapp_version);
		message('小程序创建成功！跳转后请自行下载打包程序', url('wxapp/display/switch', array('uniacid' => $uniacid)), 'success');
	}
	
	if (!empty($uniacid)) {
		$wxapp_info = wxapp_fetch($uniacid);

		$new_version = array();
		if(!empty($wxapp_info['version'])){
			$new_version = wxapp_version_parser($wxapp_info['version'][0], $wxapp_info['version'][1], $wxapp_info['version'][2]+1);
		}
	} else {
		$new_version = wxapp_version_parser(1, 0, 0);
	}
	template('wxapp/create-post');
}
if($do == 'getpackage') {
	if (empty($_GPC['single'])) {
		$versionid = intval($_GPC['versionid']);
		$uniacid = $_W['uniacid'];
		if(empty($versionid)) {
			itoast('参数错误！', '', '');
		}
	} else {
		$uniacid = intval($_GPC['uniacid']);
		if(empty($uniacid)) {
			itoast('参数错误！', '', '');
		}
	}

	$request_cloud_data = array();
	$account_wxapp_info = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	if (empty($_GPC['single'])) {
		$wxapp_version_info = pdo_get('wxapp_versions', array('uniacid' => $uniacid, 'id' => $versionid));
		$request_cloud_data['name'] = $account_wxapp_info['name'];
		$zipname = $request_cloud_data['name'];
		$request_cloud_data['modules'] = json_decode($wxapp_version_info['modules'], true);
		$request_cloud_data['siteInfo'] = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $account_wxapp_info['acid'],
				'multiid' => $wxapp_version_info['multiid'],
				'version' => $wxapp_version_info['version'],
				'siteroot' => $_W['siteroot'].'app/index.php'
			);
		$request_cloud_data['tabBar'] = json_decode($wxapp_version_info['quickmenu'], true);
		$result = wxapp_getpackage($request_cloud_data);
	} else {
		$wxapp_version_info = pdo_get('wxapp_versions', array('uniacid' => $uniacid));
		$moduleinfo = json_decode($wxapp_version_info['modules'], true);
		$modulename = array_keys($moduleinfo);
		$zipname = $modulename[0];
		$request_cloud_data['module'] = array(
			'name' => $modulename[0],
			'zipname' => $account_wxapp_info['name'],
		);
		$request_cloud_data['siteInfo'] = array(
				'uniacid' => $uniacid,
				'acid' => $account_wxapp_info['acid'],
				'siteroot' => $_W['siteroot'].'app/index.php'
			);
		$result = wxapp_getpackage($request_cloud_data, true);
	}

	if(is_error($result)) {
		itoast($result['message'], '', '');
	}else {
		header('content-type: application/zip');
		header('content-disposition: attachment; filename="'.$zipname.'.zip"');
		echo $result;
	}
	exit;
}

//获取所有支持小程序的模块
if($do == 'get_wxapp_modules') {
	$wxapp_modules = wxapp_supoort_wxapp_modules();
	iajax(0, $wxapp_modules, '');
}