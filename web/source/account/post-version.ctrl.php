<?php
/**
 * 管理公众号--使用者管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('system');
load()->model('wxapp');

$dos = array('delete', 'display', 'editmodule', 'delmodule', 'get_single_package');
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
		if ($account['wxapp_type'] == WXAPP_MULTI) {
			$wxapp_version_lists = pdo_getall('wxapp_versions', array('uniacid' => $account['uniacid']));
			$wxapp_info = pdo_get('account_wxapp', array('uniacid' => $account['uniacid']));
		} elseif($account['wxapp_type'] == WXAPP_SINGLE) {
			$wxapp_version_lists = pdo_get('wxapp_versions', array('uniacid' => $account['uniacid']));
			if (!empty($wxapp_version_lists['modules'])) {
				$connect_module = array_keys(json_decode($wxapp_version_lists['modules'], true));
				$current_module_info = module_fetch($connect_module[0]);
			} else {
				$current_module_info = array();
			}
			$wxapp_modules = wxapp_owned_moudles($account['uniacid']);
		}
	}
	template('account/manage-version-wxapp');
}

if ($do == 'editmodule') {
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

if ($do == 'delmodule') {
	$result = pdo_update('wxapp_versions', array('modules' => ''), array('uniacid' => $uniacid));
	if (!empty($result)) {
		iajax(0, '删除成功！');
	} else {
		iajax(1, '删除失败，请稍候重试！');
	}
}

if ($do == 'get_single_package') {
	if(empty($uniacid) || !is_numeric($uniacid)) {
		itoast('参数错误！', '', '');
	}
	$request_cloud_data = array();
	$account_wxapp_info = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	$wxapp_version_info = pdo_get('wxapp_versions', array('uniacid' => $uniacid));
	$request_cloud_data['name'] = $account_wxapp_info['name'];
	$request_cloud_data['modules'] = json_decode($wxapp_version_info['modules'], true);
	$request_cloud_data['siteInfo'] = array(
			'uniacid' => $uniacid,
			'acid' => $account_wxapp_info['acid'],
			'siteroot' => $_W['siteroot'].'app/index.php'
		);
	$result = wxapp_getpackage($request_cloud_data);

	if(is_error($result)) {
		itoast($result['message'], '', '');
	}else {
		header('content-type: application/zip');
		header('content-disposition: attachment; filename="'.$request_cloud_data['name'].'.zip"');
		echo $result;
	}
	exit;
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