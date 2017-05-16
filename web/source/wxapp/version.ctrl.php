<?php
/**
 * 管理小程序
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');
load()->model('wxapp');

$dos = array('edit', 'get_categorys', 'save_category', 'del_category', 'switch_version', 'account_list', 'save_connection');
$do = in_array($do, $dos) ? $do : 'edit';
$_W['page']['title'] = '小程序 - 管理';
$uniacid = intval($_GPC['uniacid']);
if (empty($uniacid)) {
	itoast('请选择要操作的小程序', referer(), 'error');
}

if ($do == 'del_category') {
	$id = $_GPC['id'];
	$result = pdo_delete('site_category', array('id' => $id));
}

if ($do == 'get_categorys') {
	$multiid = intval($_GPC['multiid']);
	$categorys = pdo_getall('site_category', array('uniacid' => $uniacid, 'multiid' => $multiid));
	return iajax(1, $categorys, '');
}

if ($do == 'save_category') {
	$post =  $_GPC['post'];
	$multiid = intval($_GPC['multiid']);
	foreach ($post as $category) {
		if (!empty($category['id'])) {
			$update = array('name' => $category['name'], 'displayorder' => $category['displayorder'], 'linkurl' => $category['linkurl']);
			pdo_update('site_category', $update, array('uniacid' => $uniacid, 'id' => $category['id']));
		} else {
			if (!empty($category['name'])) {
				$insert = $category;
				$insert['uniacid'] = $uniacid;
				$insert['multiid'] = $multiid;
				unset($insert['$$hashKey']);
				pdo_insert('site_category', $insert);
			}
		}
	}
	return iajax(1, 1, '');
}

if ($do == 'edit') {
	$multiid = intval($_GPC['multiid']);
	$operate = $_GPC['operate'];
	$version_id = intval($_GPC['version_id']);
	if ($operate == 'delete') {
		$type = $_GPC['type'];
		$id = intval($_GPC['id']);
		pdo_delete('site_'.$type, array('id' => $id));
		itoast('删除成功', url('wxapp/version/edit', array('multiid' => $multiid, 'uniacid' => $uniacid, 'version_id' => $version_id, 'wxapp' => $type)), 'success');
	}
	if (checksubmit('submit')) {
		$slide = $_GPC['slide'];
		$nav = $_GPC['nav'];
		$recommend = $_GPC['recommend'];
		$id = intval($_GPC['id']);
		if (!empty($slide)) {
			if (empty($id)) {
				$slide['uniacid'] = $_GPC['uniacid'];
				$slide['multiid'] = $multiid;
				pdo_insert('site_slide', $slide);
				itoast('添加幻灯片成功', url('wxapp/version/edit', array('multiid' => $multiid, 'uniacid' => $_GPC['uniacid'], 'version_id' => $version_id, 'wxapp' => 'slide')), 'success');
			} else {
				$result = pdo_update('site_slide', $slide, array('uniacid' => $_GPC['uniacid'], 'multiid' => $multiid, 'id' => $id));
				itoast('更新幻灯片成功', url('wxapp/version/edit', array('multiid' => $multiid, 'uniacid' => $_GPC['uniacid'], 'version_id' => $version_id, 'wxapp' => 'slide')), 'success');
			}
		}
		if (!empty($nav)) {
			if (empty($id)) {
				$nav['uniacid'] = $_GPC['uniacid'];
				$nav['multiid'] = $multiid;
				$nav['status'] = 1;
				pdo_insert('site_nav', $nav);
				itoast('添加导航图标成功', url('wxapp/version/edit', array('wxapp' => 'nav', 'multiid' => $multiid, 'uniacid' => $_GPC['uniacid'], 'version_id' => $version_id, 'wxapp' => 'nav')), 'success');
			} else {
				pdo_update('site_nav', $nav, array('uniacid' => $_GPC['uniacid'], 'multiid' => $multiid, 'id' => $id));
				itoast('更新导航图标成功', url('wxapp/version/edit', array('wxapp' => 'nav', 'multiid' => $multiid, 'uniacid' => $_GPC['uniacid'], 'version_id' => $version_id, 'wxapp' => 'nav')), 'success');
			}
		}
		if (!empty($recommend)) {
			if (empty($id)) {
				$recommend['uniacid'] = $_GPC['uniacid'];
				$result = pdo_insert('site_article', $recommend);
				itoast('添加推荐图片成功', url('wxapp/version/edit', array('wxapp' => 'recommend', 'multiid' => $multiid, 'uniacid' => $_GPC['uniacid'], 'version_id' => $version_id, 'wxapp' => 'recommend')), 'success');
			} else {
				pdo_update('site_article', $recommend, array('uniacid' => $_GPC['uniacid'], 'id' => $id));
				itoast('更新推荐图片成功', url('wxapp/version/edit', array('wxapp' => 'recommend', 'multiid' => $multiid, 'uniacid' => $_GPC['uniacid'], 'version_id' => $version_id, 'wxapp' => 'recommend')), 'success');
			}
		}
	}
	$slides = pdo_getall('site_slide', array('uniacid' => $uniacid, 'multiid' => $multiid));
	$navs = pdo_getall('site_nav', array('uniacid' => $uniacid, 'multiid' => $multiid));
	if (!empty($navs)) {
		foreach($navs as &$nav) {
			$nav['css'] = iunserializer($nav['css']);
		}
	}
	$recommends = pdo_getall('site_article', array('uniacid' => $_GPC['uniacid']));
	
	$wxapp_info = wxapp_fetch($uniacid, $version_id);
	$version_info = $wxapp_info['version'];
	
	if (!empty($version_info['modules'])) {
		$modules = iunserializer($version_info['modules']);
		foreach ($modules as &$module_val) {
			if (!empty($module_val['name'])) {
				$module_info = module_fetch($module_val['name']);
				$module_val['title'] = $module_info['title'];
				if (file_exists(IA_ROOT.'/addons/'.$module_val['name'].'/icon-custom.jpg')) {
					$module_val['modulelogo'] = tomedia(IA_ROOT.'/addons/'.$module_val['name'].'/icon-custom.jpg');
				} else {
					$module_val['modulelogo'] = tomedia(IA_ROOT.'/addons/'.$module_val['name'].'/icon.jpg');
				}
			}
			if (!empty($module_val['connect_account'])) {
				foreach ($module_val['connect_account'] as &$connect_account_info) {
					if (!empty($connect_account_info)) {
						$accounts = uni_account_default($connect_account_info);
						if (!empty($accounts) && $accounts['isdeleted'] == 0 && $accounts['type'] != 4) {
							$connect_account_info['thumb'] = tomedia('headimg_'.$accounts['acid']. '.jpg').'?time='.time();
						}
					}
				}
			} else {
				$module_val['connect_account'] = array();
			}
		}
		unset($module_val);
		unset($connect_account_info);
		$module_connections = $modules;
	}
	template('wxapp/wxapp-edit');
}

if ($do == 'account_list') {
	//查询当前用户所有公众号
	$accounts = uni_owned();
	//筛选有模块权限的公众号
	foreach($accounts as $key =>$val){
		$account_module = pdo_get('uni_account_modules',array('module' => $_GPC['module'],'enabled' => '1','uniacid'=>$val['uniacid']),array('uniacid'), 'uniacid');
		if(empty($account_module)){
			continue;
		}
		$val['thumb'] = tomedia('headimg_'.$val['acid']. '.jpg').'?time='.time();
		$account_list[]=$val;
	}
	iajax(0, $account_list, '');
}

if ($do == 'save_connection') {
	$version_id = intval($_GPC['version_id']);
	$module = trim($_GPC['module']);
	echo "<pre>";
	print_r($_GPC);
	echo "</pre>";
	exit;
	if (empty($version_id) || empty($module)) {
		iajax(-1, '参数错误！');
	}
	$version_info = pdo_get('wxapp_versions', array('id' => $version_id));
	$modules = iunserializer($version_info['modules'], true);
	if (!in_array($module, $modules)) {
		iajax(-1, '模块参数错误！');
	}
	if (!empty($modules['connect_account'])) {
		$modules['connect_account'][] = $uniacid;
	} else {
		$moduels['connect_account'] = array($uniacid);
	}
	$result = pdo_update('wxapp_versions', array('connection' => iserializer($modules)), array('id' => $version_id));
	if (is_error($result)) {
		iajax(-1, $result['message']);
	}
	iajax(0, '保存成功！', referer());
}

if ($do == 'switch_version') {
	$wxapp_version_lists = pdo_getall('wxapp_versions', array('uniacid' => $uniacid));
	template('wxapp/switch-version');
}