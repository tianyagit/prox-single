<?php
/**
 * 创建小程序
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('wxapp');

$dos = array('post', 'getapps', 'getpackage', 'getlink','developer');
$do = in_array($do, $dos) ? $do : 'post';
$_W['page']['title'] = '小程序 - 新建版本';
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

if($do == 'post' || $do == 'developer') {
	$uniacid = intval($_GPC['uniacid']);
	$is_developer = $do == 'developer' ? 1 : 0;
	$wxapp_info = wxapp_info($uniacid);
	$version_nums = wxapp_version_update($uniacid);
	if(!empty($_GPC['wxappval'])) {
		$submit_val = json_decode(ihtml_entity_decode($_GPC['wxappval']), true);
		if($submit_val['wxapp_type']){
			$wxapp_type = intval($submit_val['wxapp_type']);
		} else {
			$wxapp_type = WXAPP_MULTI;
		}
		if($wxapp_type == WXAPP_SINGLE){
			$version = "";
		} else {
			$version_0 = intval($submit_val['version0']);
			$version_1 = intval($submit_val['version1']);
			$version_2 = intval($submit_val['version2']);
			$version = sprintf("%u.%u.%u",$version_0,$version_1,$version_2);
		}
		$request_cloud_data = array();
		//底部菜单
		if(is_array($submit_val['menus'])){
			$bottom_menu = array();
			foreach ($submit_val['menus'] as $menu_val) {
				$menu_val['defaultImage'] = $menu_val['defaultImage'] == './resource/images/bottom-default.png' ? $_W['siteroot'] . 'web/resource/images/bottom-default.png' : $menu_val['defaultImage'];
				$menu_val['selectedImage'] = $menu_val['selectedImage'] == './resource/images/bottom-default.png' ? $_W['siteroot'] . 'web/resource/images/bottom-default.png' : $menu_val['selectedImage'];
				$bottom_menu[] = array(
					'pagePath' => $menu_val['module']['url'],
					'iconPath' => $menu_val['defaultImage'],
					'selectedIconPath' => $menu_val['selectedImage'],
					'text' => $menu_val['name']
				);
			}
		}
		//小程序所关联模块信息
		if(is_array($submit_val['modules']) && !empty($submit_val['modules'][0])){
			$modules = array();
			foreach ($submit_val['modules'] as $module_val) {
				$modules[$module_val['module']] = $module_val['version'];
				$modules_connection[$module_val['module']] = $uniacid;
			}
		}
		//新建小程序公众号
		if (empty($uniacid)) {
			$name = trim($submit_val['name']);
			$description = '微信小程序体验版';
			$data = array(
				'name' => $name,
				'description' => $description,
				'groupid' => 0,
			);
			if (!pdo_insert('uni_account', $data)) {
				itoast('添加公众号失败', '', '');
			}
			$uniacid = pdo_insertid();
			if($wxapp_type == WXAPP_MULTI){
				$multi['uniacid'] = $uniacid;
				$multi['title'] = $name;
				$multi['styleid'] = 0;
				pdo_insert('site_multi', $multi);
				$multi_id = pdo_insertid();
			} else {
				$multi_id = "";
			}

			$update['name'] = $name;
			$update['account'] = trim($submit_val['account']);
			$update['original'] = trim($submit_val['original']);
			$update['level'] = intval(1);
			$update['key'] = trim($submit_val['key']);
			$update['secret'] = trim($submit_val['secret']);
			$update['type'] = ACCOUNT_TYPE_APP_NORMAL;
			if (empty($acid)) {
				$acid = wxapp_account_create($uniacid, $update, $wxapp_type);
				if(is_error($acid)) {
					itoast('添加小程序信息失败', url('wxapp/post'), 'error');
				}
				if (empty($_W['isfounder'])) {
					pdo_insert('uni_account_users', array('uniacid' => $uniacid, 'uid' => $_W['uid'], 'role' => 'owner'));//公众号用户表
				}
				pdo_update('uni_account', array('default_acid' => $acid), array('uniacid' => $uniacid));//更新默认子公众号
			}
		}
		//关联了模块需生成小程序版本记录
		if(is_array($submit_val['modules']) && !empty($submit_val['modules'][0])){
			$multi_info = pdo_get('site_multi', array('uniacid' => $uniacid), array('id', 'uniacid'));
			$request_cloud_data = array(
				'name' => $submit_val['name'],
				'modules' => $modules,
				'siteInfo' => array(
					'uniacid' => $uniacid,
					'acid' => $acid,
					'multiid'  => $multi_info['id'],
					'version'  => $version,
					'siteroot' => $_W['siteroot'].'app/index.php'
				),
			);
			if($submit_val['showmenu']) {
				$request_cloud_data['tabBar'] = array(
					'color' => $submit_val['buttom']['color'],
					'selectedColor' => $submit_val['buttom']['selectedColor'],
					'borderStyle' => $submit_val['buttom']['boundary'],
					'backgroundColor' => $submit_val['buttom']['bgcolor'],
					'list' => $bottom_menu
				);
			}
			$wxapp_version['uniacid'] = $uniacid;
			$wxapp_version['multiid'] = $multi_info['id'];
			$wxapp_version['version'] = $version;
			$wxapp_version['modules'] = json_encode($request_cloud_data['modules']);
			$wxapp_version['connection'] = json_encode($modules_connection);
			$wxapp_version['design_method'] = intval($submit_val['type']);
			$wxapp_version['quickmenu'] = json_encode($request_cloud_data['tabBar']);
			$wxapp_version['createtime'] = time();
			switch ($wxapp_version['design_method']) {
				case 1:
					break;
				case 2:
					$wxapp_version['template'] = intval($submit_val['template']);
					break;
				case 3:
					$wxapp_version['redirect'] = json_encode($submit_val['tomodule']);
					break;
			}
			pdo_insert('wxapp_versions', $wxapp_version);
			$versionid = pdo_insertid();
		}
		message('小程序创建成功！跳转后请自行下载打包程序', url('wxapp/display/switch', array('uniacid' => $uniacid)), 'success');
	}
	template('wxapp/create-post');
}
if($do == 'getpackage') {
	$versionid = intval($_GPC['versionid']);
	if(empty($versionid)) {
		itoast('参数错误！', '', '');
	}
	$request_cloud_data = array();
	$account_wxapp_info = pdo_get('account_wxapp', array('uniacid' => $_W['uniacid']));
	$wxapp_version_info = pdo_get('wxapp_versions', array('uniacid' => $_W['uniacid'], 'id' => $versionid));
	$request_cloud_data['name'] = $account_wxapp_info['name'];
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

	if(is_error($result)) {
		itoast($result['message'], '', '');
	}else {
		header('content-type: application/zip');
		header('content-disposition: attachment; filename="'.$request_cloud_data['name'].'.zip"');
		echo $result;
	}
	exit;
}
if($do == 'getapps') {
	$apps = array();
	$apps = cache_load('packageapps');
	if(empty($apps)) {
		$module_list = uni_modules();
		foreach ($module_list as $key => $module) {
			if($module['type'] != 'system' && !empty($module['version'])) {
								if($module['issystem']) {
					$path = '../framework/builtin/' . $module['name'];
				} else {
					$path = '../addons/' . $module['name'];
				}
				$cion = $path . '/icon-custom.jpg';
				if(!file_exists($cion)) {
					$cion = $path . '/icon.jpg';
					if(!file_exists($cion)) {
						$cion = './resource/images/nopic-small.jpg';
					}
				}
				if ($module['wxapp_support'] == 2) {
					$arr = pdo_getall('modules_bindings', array('module' => $module['name'], 'entry' => 'page'), array('module', 'title', 'url', 'eid'), 'eid');
					$rst['bindings'] = array_keys($arr);
					$rst['module'] = $module['name'];
					$rst['module_title'] = $module['title'];
					$rst['module_icon'] = $cion;
					$rst['version'] = $module['version'];
					$apps[] = $rst;
				}
				// $m = module_entries($module['name'], array('home'));
				// if(!empty($m['home'])) {
				// 	foreach($m['home'] as $val) {
				// 		$rst = array();
				// 		if(isset($val['eid']) && !empty($val['eid'])) {
				// 			$rst = module_entry($val['eid']);
				// 			$rst['module_title'] = $module['title'];
				// 			$rst['module_icon'] = $cion;
				// 			$rst['version'] = $module['version'];
				// 			$apps[] = $rst;
				// 		}
				// 	}	
				// }
			}
		}
		cache_write('packageapps', $apps);				
	}
	iajax(0, $apps, '');
}