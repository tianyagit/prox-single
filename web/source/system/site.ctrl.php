<?php 
/**
 * 站点相关操作
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
/* sxstart */
if (IMS_FAMILY == 's' || IMS_FAMILY == 'x') {
	load()->model('system');
}
/* sxend */
$dos = array('copyright');
$do = in_array($do, $dos) ? $do : 'copyright';
$_W['page']['title'] = '站点设置 - 工具  - 系统管理';

$settings = $_W['setting']['copyright'];

if(empty($settings) || !is_array($settings)) {
	$settings = array();
} else {
	$settings['slides'] = iunserializer($settings['slides']);
}
/* sxstart */
if (IMS_FAMILY == 's' || IMS_FAMILY == 'x') {
	$path = IA_ROOT . '/web/themes/';
	if(is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($templatepath = readdir($handle))) {
				if ($templatepath != '.' && $templatepath != '..') {
					if(is_dir($path.$templatepath)){
						$template[] = $templatepath;
					}
				}
			}
		}
	}		
}
/* sxend */
if ($do == 'copyright') {
	/* sxstart */
	if (IMS_FAMILY == 's' || IMS_FAMILY == 'x') {
		$template_ch_name = system_template_ch_name();
	}
	/* sxend */
	if (checksubmit('submit')) {
		/* sxstart */
		if (IMS_FAMILY == 's' || IMS_FAMILY == 'x') {
			$data = array(
				'status' => intval($_GPC['status']),
				'verifycode' => intval($_GPC['verifycode']),
				'reason' => trim($_GPC['reason']),
				'sitename' => trim($_GPC['sitename']),
				'url' => (strexists($_GPC['url'], 'http://') || strexists($_GPC['url'], 'https://')) ? $_GPC['url'] : "http://{$_GPC['url']}",
				'statcode' => safe_gpc_html(htmlspecialchars_decode($_GPC['statcode'])),
				'footerleft' => safe_gpc_html(htmlspecialchars_decode($_GPC['footerleft'])),
				'footerright' => safe_gpc_html(htmlspecialchars_decode($_GPC['footerright'])),
				'icon' => trim($_GPC['icon']),
				'flogo' => trim($_GPC['flogo']),
				'background_img' => trim($_GPC['background_img']),
				'slides' => iserializer($_GPC['slides']),
				'notice' => trim($_GPC['notice']),
				'blogo' => trim($_GPC['blogo']),
				'baidumap' => $_GPC['baidumap'],
				'company' => trim($_GPC['company']),
				'companyprofile' => safe_gpc_html(htmlspecialchars_decode($_GPC['companyprofile'])),
				'address' => trim($_GPC['address']),
				'person' => trim($_GPC['person']),
				'phone' => trim($_GPC['phone']),
				'qq' => trim($_GPC['qq']),
				'email' => trim($_GPC['email']),
				'keywords' => trim($_GPC['keywords']),
				'description' => trim($_GPC['description']),
				'showhomepage' => intval($_GPC['showhomepage']),
				'leftmenufixed' => (!empty($_GPC['leftmenu_fixed'])) ? 1 : 0,
				'mobile_status' => $_GPC['mobile_status'],
				'login_type' => $_GPC['login_type'],
				'icp' => safe_gpc_string($_GPC['icp']),
				'bind' => $_GPC['bind']
			);
		}
		/* sxend */
		/* vstart */
		if (IMS_FAMILY == 'v') {
			$data = array(
				'status' => $_GPC['status'],
				'reason' => $_GPC['reason'],
				'icp' => safe_gpc_string($_GPC['icp']),
				'mobile_status' => $_GPC['mobile_status'],
				'login_type' => $_GPC['login_type'],
				'bind' => $_GPC['bind']
			);				
		}
		/* vend */

		$test = setting_save($data, 'copyright');

		/* sxstart */
		if (IMS_FAMILY == 's' || IMS_FAMILY == 'x') {
			$template = trim($_GPC['template']);
			setting_save(array('template' => $template), 'basic');
		}
		/* sxend */

		itoast('更新设置成功！', url('system/site'), 'success');
	}
}

template('system/site');