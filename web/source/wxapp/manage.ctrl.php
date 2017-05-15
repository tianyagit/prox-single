<?php
/**
 * 管理公众号
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
define('FRAME', 'system');
load()->model('system');
load()->model('wxapp');

$dos = array('delete', 'display', 'single_change_module', 'single_del_module', 'get_available_apps', 'getpackage');
$do = in_array($do, $dos) ? $do : 'display';

$uniacid = intval($_GPC['uniacid']);
$acid = intval($_GPC['acid']);
if (empty($uniacid)) {
	itoast('请选择要编辑的小程序', referer(), 'error');
}

$state = uni_permission($_W['uid'], $uniacid);
//只有创始人、主管理员、管理员才有权限
if ($state != ACCOUNT_MANAGE_NAME_OWNER && $state != ACCOUNT_MANAGE_NAME_FOUNDER && $state != ACCOUNT_MANAGE_NAME_MANAGER) {
	itoast('无权限操作！', referer(), 'error');
}

if ($do == 'display') {
	$account = account_fetch($acid);
	if (is_error($account)) {
		itoast($account['message'], url('account/manage', array('account_type' => 4)), 'error');
	} else {
		$wxapp_version_lists = pdo_get('wxapp_versions', array('uniacid' => $account['uniacid']));
		if ($wxapp_version_lists['design_method'] != 3 || ((!empty($wxapp_version_lists['multiid']) || !empty($wxapp_version_lists['version']) || !empty($wxapp_version_lists['template']) || !empty($wxapp_version_lists['redirect']) || !empty($wxapp_version_lists['quickmenu'])) && $wxapp_version_lists['design_method'] == 3)) {
			$wxapp_version_lists = pdo_getall('wxapp_versions', array('uniacid' => $account['uniacid']));
			$wxapp_info = pdo_get('account_wxapp', array('uniacid' => $account['uniacid']));
		} else {
			if (!empty($wxapp_version_lists['modules'])) {
				$connect_module = array_keys(json_decode($wxapp_version_lists['modules'], true));
				$current_module_info = module_fetch($connect_module[0]);
			} else {
				$current_module_info = array();
			}
			$wxapp_modules = wxapp_owned_moudles($account['uniacid']);
		}
	}
	template('wxapp/manage');
}

if ($do == 'single_change_module') {
	if (empty($_GPC['module'])) {
		iajax(1, '模块数据错误！');
	}
	$have_permission = false;
	$wxapp_modules = wxapp_owned_moudles($uniacid);
	$modulename_arr = array();
	foreach ($wxapp_modules as $module) {
		$modulename_arr[] = $module['name'];
	}
	$have_permission = in_array($_GPC['module']['name'], $modulename_arr);
	if (!empty($have_permission)) {
		$data = array(
			'uniacid' => $uniacid,
			'modules' => json_encode(array($_GPC['module']['name'] => $_GPC['module']['version'])),
			'createtime' => TIMESTAMP
		);
		pdo_delete('wxapp_versions', array('uniacid' => $uniacid));
		pdo_insert('wxapp_versions', $data);
		iajax(0, '添加成功！');
	} else {
		iajax(1, '没有此模块的权限！');
	}
}

if ($do == 'single_del_module') {
	$result = pdo_update('wxapp_versions', array('modules' => ''), array('uniacid' => $uniacid));
	if (!empty($result)) {
		iajax(0, '删除成功！');
	} else {
		iajax(1, '删除失败，请稍候重试！');
	}
}

if ($do == 'delete') {
	$id = intval($_GPC['id']);
	$version_info = pdo_get('wxapp_versions', array('id' => $id));
	if (!empty($version_info)) {
		pdo_delete('wxapp_versions', array('id' => $id));
	} else {
		itoast('版本不存在', referer(), 'error');
	}
	itoast('删除成功', referer(), 'success');
}

if ($do == 'get_available_apps') {
	$apps = wxapp_owned_moudles($uniacid);
	iajax(0, $apps, '');
}

if($do == 'getpackage') {
	$versionid = intval($_GPC['versionid']);
	if(empty($versionid)) {
		itoast('参数错误！', '', '');
	}

	$request_cloud_data = array();
	$account_wxapp_info = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	if (empty($_GPC['single'])) {
		$wxapp_version_info = pdo_get('wxapp_versions', array('uniacid' => $uniacid, 'id' => $versionid));
		$request_cloud_data['name'] = $account_wxapp_info['name'];
		// @@todo 云服务参数
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