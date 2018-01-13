<?php
/**
 * 小程序下载
 * [WeEngine System] Copyright (c) 2014 WE7.CC.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('wxapp');
load()->classs('cloudapi');
load()->classs('uploadedfile');

$dos = array('front_download', 'domainset', 'code_uuid', 'code_gen', 'code_token', 'qrcode', 'checkscan',
	'commitcode', 'preview', 'getpackage', 'entrychoose', 'set_wxapp_entry',
	'custom', 'custom_save', 'custom_default', 'custom_convert_img');
$do = in_array($do, $dos) ? $do : 'front_download';

$_W['page']['title'] = '小程序下载 - 小程序 - 管理';

$version_id = intval($_GPC['version_id']);
$wxapp_info = wxapp_fetch($_W['uniacid']);


// 是否是模块打包小程序
$is_module_wxapp = false;
if (!empty($version_id)) {
	$version_info = wxapp_version($version_id);
	$is_single_module_wxapp = $version_info['type'] == WXAPP_CREATE_MODULE; //是否单应用打包
}

/* xstart */
if (IMS_FAMILY == 'x') {
	// 入口选择
	if ($do == 'entrychoose') {
		$entrys = $version_info['cover_entrys'];
		template('wxapp/version-front-download');
	}
	// 设置入口
	if ($do == 'set_wxapp_entry') {
		$entry_id = intval($_GPC['entry_id']);
		$result = wxapp_update_entry($version_id, $entry_id);
		iajax(0, '设置入口成功');
	}
}
/* xend */

// 自定义appjson 入口
if ($do == 'custom') {
	$default_appjson = wxapp_code_current_appjson($version_id);
	$default_appjson = json_encode($default_appjson);
	template('wxapp/version-front-download');
}
// 使用默认appjson
if ($do == 'custom_default') {
	$result = wxapp_code_set_default_appjson($version_id);
	iajax($result);
}

// 保存自定义appjson
if ($do == 'custom_save') {
	$json = $_GPC['json'];
	$result = wxapp_code_save_appjson($version_id, $json);
	iajax($result, '');
}

if ($do == 'custom_convert_img') {
	$attchid = intval($_GPC['att_id']);
	$filename = wxapp_code_path_convert($attchid);
	iajax(0, $filename);
}

if ($do == 'domainset') {
	$appurl = $_W['siteroot'].'app/index.php';
	$uniacid = 0;
	if ($version_info) {
		$wxapp = pdo_get('account_wxapp', array('uniacid' => $version_info['uniacid']));
		if ($wxapp && !empty($wxapp['appdomain'])) {
			$appurl = $wxapp['appdomain'];
		}
		if (!starts_with($appurl, 'https')) { //不是https 开头强制改为https开头
			$appurl = str_replace('http', 'https', $appurl);
		}
		$uniacid = $version_info['uniacid'];
	}
	if ($_W['ispost']) {
		$files = UploadedFile::createFromGlobal();
		$appurl = $_GPC['appurl'];
		if (!starts_with($appurl, 'https')) {
			itoast('域名必须以https开头');

			return;
		}

		/** @var $file UploadedFile */
		$file = isset($files['file']) ? $files['file'] : null;
		if ($file && $file->isOk() && $file->allowExt('txt')) {
			$file->moveTo(IA_ROOT.'/'.$file->getClientFilename()); //上传业务域名
		}

		if ($version_info) {
			$update = pdo_update('account_wxapp', array('appdomain' => $appurl),
				array('uniacid' => $uniacid));
			itoast('更新小程序域名成功'); //新 旧域名一样 返回$update 为0
		}
	}
	template('wxapp/version-front-download');
}

if ($do == 'front_download') {
	$appurl = $_W['siteroot'].'/app/index.php';
	$uptype = $_GPC['uptype'];
	$wxapp_versions_info = wxapp_version($version_id);
	if (!in_array($uptype, array('auto', 'normal'))) {
		$uptype = 'auto';
	}
	template('wxapp/version-front-download');
}

// 获取上传代码uuid
if ($do == 'code_uuid') {
	$data = wxapp_code_generate($version_id);
	echo json_encode($data);
}

if ($do == 'code_gen') {
	$code_uuid = $_GPC['code_uuid'];
	$data = wxapp_check_code_isgen($code_uuid);
	echo json_encode($data);
}

if ($do == 'code_token') {
	$tokendata = wxapp_code_token();
	echo json_encode($tokendata);
}

if ($do == 'qrcode') {
	$code_token = $_GPC['code_token'];
	header('Content-type: image/jpg'); //有的站必须指定content-type才能显示
	echo wxapp_code_qrcode($code_token);
	exit;
}

if ($do == 'checkscan') {
	$code_token = $_GPC['code_token'];
	$last = $_GPC['last'];
	$data = wxapp_code_check_scan($code_token, $last);
	echo json_encode($data);
}

if ($do == 'preview') {
	$code_token = $_GPC['code_token'];
	$code_uuid = $_GPC['code_uuid'];
	$data = wxapp_code_preview_qrcode($code_uuid, $code_token);
	echo json_encode($data);
}

// 上传代码
if ($do == 'commitcode') {
	$user_version = $_GPC['user_version'];
	$user_desc = $_GPC['user_desc'];
	$code_token = $_GPC['code_token'];
	$code_uuid = $_GPC['code_uuid'];
	$data = wxapp_code_commit($code_uuid, $code_token, $user_version, $user_desc);
	echo json_encode($data);
}

if ($do == 'getpackage') {
	if (empty($version_id)) {
		itoast('参数错误！', '', '');
	}
	$account_wxapp_info = wxapp_fetch($version_info['uniacid'], $version_id);
	if (empty($account_wxapp_info)) {
		itoast('版本不存在！', referer(), 'error');
	}
	$siteurl = $_W['siteroot'].'app/index.php';
	if (!empty($account_wxapp_info['appdomain'])) {
		$siteurl = $account_wxapp_info['appdomain'];
	}

	$request_cloud_data = array(
			'name' => $account_wxapp_info['name'],
			'modules' => $account_wxapp_info['version']['modules'],
			'siteInfo' => array(
					'name' => $account_wxapp_info['name'],
					'uniacid' => $account_wxapp_info['uniacid'],
					'acid' => $account_wxapp_info['acid'],
					'multiid' => $account_wxapp_info['version']['multiid'],
					'version' => $account_wxapp_info['version']['version'],
					'siteroot' => $siteurl,
					'design_method' => $account_wxapp_info['version']['design_method'],
			),
			'tabBar' => json_decode($account_wxapp_info['version']['quickmenu'], true),
	);
	$result = wxapp_getpackage($request_cloud_data);

	if (is_error($result)) {
		itoast($result['message'], '', '');
	} else {
		header('content-type: application/zip');
		header('content-disposition: attachment; filename="'.$request_cloud_data['name'].'.zip"');
		echo $result;
	}
	exit;
}
