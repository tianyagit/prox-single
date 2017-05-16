<?php
/**
 * 管理公众号
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

define('FRAME', 'system');
load()->model('system');
load()->model('wxapp');

$dos = array('delete', 'display', 'add_module_version', 'del_module_version', 'get_available_apps', 'getpackage');
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
		$version_exist = pdo_get('wxapp_versions', array('uniacid' => $account['uniacid']));

		if (!empty($version_exist) && ($version_exist['design_method'] != 3 || ((!empty($version_exist['multiid']) || !empty($version_exist['template']) || !empty($version_exist['quickmenu'])) && $version_exist['design_method'] == 3))) {
			$wxapp_version_lists = pdo_getall('wxapp_versions', array('uniacid' => $account['uniacid']));
			$wxapp_info = pdo_get('account_wxapp', array('uniacid' => $account['uniacid']));
		} else {
			if (!empty($version_exist)) {
				$wxapp_version_lists = pdo_getall('wxapp_versions', array('uniacid' => $account['uniacid']));
				foreach ($wxapp_version_lists as &$module_val) {
					$module_val['modules'] = iunserializer($module_val['modules']);
					$module_val['modules'][0]['module_info'] = module_fetch($module_val['modules'][0]['name']);
					$module_val['modules'] = $module_val['modules'][0];
				}
				unset($module_val);
			} else {
				$wxapp_version_lists = array();
			}
			$wxapp_modules = wxapp_supoort_wxapp_modules();
		}
	}
	template('wxapp/manage');
}

if ($do == 'add_module_version') {
	if (empty($_GPC['module']) || !is_array($_GPC['module'])) {
		iajax(1, '模块数据错误！');
	}
	$have_permission = false;
	$wxapp_modules = wxapp_supoort_wxapp_modules();
	$modulename_arr = array();
	foreach ($wxapp_modules as $module) {
		$modulename_arr[] = $module['name'];
	}
	$add_module = trim($_GPC['module']['name']);
	$module_version = trim($_GPC['module']['version']);
	$have_permission = in_array($add_module, $modulename_arr);
	if (!empty($have_permission)) {
		//判断该模块是否已存在
		$wxapp_all_versions = wxapp_version_all($uniacid);
		if (!empty($wxapp_all_versions)) {
			foreach ($wxapp_all_versions as $version_val) {
				if (!empty($version_val['modules'])) {
					$have_modules = array();
					$modules_info = iunserializer($version_val['modules']);
					foreach ($modules_info as $info_val) {
						if ($info_val['name'] == $add_module) {
							iajax(1, '该模块版本已存在！或删除该模块版本后新建！');
							break;
						}
					}
					
				}
			}
		}
		$wxapp_info = wxapp_fetch($uniacid);
		$wxapp_info = $wxapp_info['version'];
		$new_version = array();
		if (!empty($wxapp_info['version'])) {
			$wxapp_info['version'] = explode('.', $wxapp_info['version']);
			$new_version = wxapp_version_parser($wxapp_info['version'][0], $wxapp_info['version'][1], $wxapp_info['version'][2]+1);
		} else {
			$new_version = wxapp_version_parser(1, 0, 0);
		}
		$new_version = implode('.', $new_version);
		$new_module_data[] = array(
			'name' => $add_module,
			'version' => $module_version
		);
		$data = array('modules' => iserializer($new_module_data), 'uniacid' => $uniacid, 'createtime' => TIMESTAMP, 'version' => $new_version, 'design_method' => 3);
		pdo_insert('wxapp_versions', $data);
		iajax(0, '添加成功！', referer());
	} else {
		iajax(1, '没有此模块的权限！');
	}
}

if ($do == 'del_module_version') {
	$id = intval($_GPC['versionid']);
	$modulename = trim($_GPC['modulename']);
	if (empty($id) || empty($modulename)) {
		iajax(1, '参数错误！');
	}
	$version_exist = pdo_get('wxapp_versions', array('id' => $id, 'uniacid' => $uniacid));
	if (empty($version_exist)) {
		iajax(1, '模块版本不存在！');
	}
	$result = pdo_delete('wxapp_versions', array('id' => $id, 'uniacid' => $uniacid));
	if (!empty($result)) {
		iajax(0, '删除成功！', referer());
	} else {
		iajax(1, '删除失败，请稍候重试！');
	}
}

if ($do == 'delete') {
	$id = intval($_GPC['id']);
	$version_info = pdo_get('wxapp_versions', array('id' => $id));
	if (!empty($version_info)) {
		$allversions = wxapp_version_all($uniacid);
		if (count($allversions) <= 1) {
			itoast('请至少保留一个版本！', referer(), 'error');
		}
		pdo_delete('wxapp_versions', array('id' => $id));
	} else {
		itoast('版本不存在', referer(), 'error');
	}
	itoast('删除成功', referer(), 'success');
}

if($do == 'getpackage') {
	$versionid = intval($_GPC['versionid']);
	if(empty($versionid)) {
		itoast('参数错误！', '', '');
	}

	$request_cloud_data = array();
	$account_wxapp_info = pdo_get('account_wxapp', array('uniacid' => $uniacid));
	$wxapp_version_info = pdo_get('wxapp_versions', array('uniacid' => $uniacid, 'id' => $versionid));
	if (empty($wxapp_version_info)) {
		itoast('版本不存在！', referer(), 'error');
	}
	$request_cloud_data['name'] = $account_wxapp_info['name'];
	$zipname = $request_cloud_data['name'];
	$request_cloud_data['modules'] = iunserializer($wxapp_version_info['modules'], true);
	$request_cloud_data['siteInfo'] = array(
			'uniacid' => $account_wxapp_info['uniacid'],
			'acid' => $account_wxapp_info['acid'],
			'multiid' => $wxapp_version_info['multiid'],
			'version' => $wxapp_version_info['version'],
			'siteroot' => $_W['siteroot'].'app/index.php',
			'design_method' => $wxapp_version_info['design_method']
		);
	$request_cloud_data['tabBar'] = json_decode($wxapp_version_info['quickmenu'], true);
	$result = wxapp_getpackage($request_cloud_data);

	if(is_error($result)) {
		itoast($result['message'], '', '');
	}else {
		header('content-type: application/zip');
		header('content-disposition: attachment; filename="'.$zipname.'.zip"');
		echo $result;
	}
	exit;
}