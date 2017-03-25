<?php
/**
 * 编辑应用套餐
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');

$dos = array('display', 'delete', 'post', 'save');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';
//只有创始人、主管理员、管理员才有权限
if ($_W['role'] != ACCOUNT_MANAGE_NAME_OWNER && $_W['role'] != ACCOUNT_MANAGE_NAME_MANAGER && $_W['role'] != ACCOUNT_MANAGE_NAME_FOUNDER) {
	message('无权限操作！', referer(), 'error');
}
if ($do != 'display' && $_W['role'] != ACCOUNT_MANAGE_NAME_FOUNDER) {
	message('您只有查看权限！', url('system/module-group'), 'error');
}

if ($do == 'save') {
	$modules = empty($_GPC['modules']) ? array() : (array)array_keys($_GPC['modules']);
	$wxapp = empty($_GPC['wxapp']) ? array() : (array)array_keys($_GPC['wxapp']);
	$package_info = array(
		'id' => intval($_GPC['id']),
		'name' => $_GPC['name'],
		'modules' => array_merge($modules, $wxapp),
		'templates' => $_GPC['templates'],
	);
	if (empty($package_info['name'])) {
		message(error(1, '请输入套餐名'), '', 'ajax');
	}

	if (!empty($package_info['modules'])) {
		$package_info['modules'] = iserializer($package_info['modules']);
	}
	if (!empty($package_info['templates'])) {
		foreach ($package_info['templates'] as $key => $template) {
			$package_info['templates'][] = $template['id'];
			unset($package_info['templates'][$key]);
		}
		$package_info['templates'] = iserializer($package_info['templates']);
	}
	if (!empty($package_info['id'])) {
		$name_exist = pdo_get('uni_group', array('uniacid' => 0, 'id <>' => $package_info['id'], 'name' => $package_info['name']));
		if (!empty($name_exist)) {
			message(error(1, '套餐名已存在'), '', 'ajax');
		}
		$packageid = $package_info['id'];
		unset($package_info['id']);
		pdo_update('uni_group', $package_info, array('id' => $packageid));
		cache_build_account_modules();
		module_build_privileges();
		message(error(0, url('system/module-group')), '', 'ajax');
	} else {
		$name_exist = pdo_get('uni_group', array('uniacid' => 0, 'name' => $package_info['name']));
		if (!empty($name_exist)) {
			message(error(1, '套餐名已存在'), '', 'ajax');
		}
		pdo_insert('uni_group', $package_info);
		module_build_privileges();
		message(error(0, url('system/module-group')), '', 'ajax');
	}
}

if ($do == 'display') {
	$_W['page']['title'] = '应用套餐列表';

	$param = array('uniacid' => 0);
	if (!empty($_GPC['name'])) {
		$param['name like'] = "%". trim($_GPC['name']) ."%";
	}
	$modules_group_list = uni_groups();
	if (!empty($modules_group_list)) {
		foreach ($modules_group_list as &$group) {
			if (!empty($group['modules'])) {
				if (is_array($group['modules']) && !empty($group['modules'])) {
					foreach ($group['modules'] as &$module) {
						if (file_exists(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg')) {
							$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon-custom.jpg');
						} else {
							$module['logo'] = tomedia(IA_ROOT.'/addons/'.$module['name'].'/icon.jpg');
						}
					}
					unset($module);
				} else {
					$group['modules'] = array();
				}
			} else {
				$group['modules'] = array();
			}
			if (!empty($group['wxapp'])) {
				$wxapp = $group['wxapp'];
				if (is_array($wxapp) && !empty($wxapp)) {
					if (!empty($group['wxapp'])) {
						foreach ($group['wxapp'] as &$wxapp) {
							if (file_exists(IA_ROOT.'/addons/'.$wxapp['name'].'/icon-custom.jpg')) {
								$wxapp['logo'] = tomedia(IA_ROOT.'/addons/'.$wxapp['name'].'/icon-custom.jpg');
							} else {
								$wxapp['logo'] = tomedia(IA_ROOT.'/addons/'.$wxapp['name'].'/icon.jpg');
							}
						}
						unset($wxapp);
					}
				} else {
					$group['wxapp'] = array();
				}
			}
			$group['templates'] = !empty($group['templates']) ? $group['templates'] : array();
		}
		unset($group);
	}
}

if ($do == 'delete') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		pdo_delete('uni_group', array('id' => $id));
		cache_build_account_modules();
	}
	message('删除成功！', referer(), 'success');
}

if ($do == 'post') {
	$group_id = intval($_GPC['id']);
	$_W['page']['title'] = $group_id ? '编辑应用套餐' : '添加应用套餐';

	$module_list = pdo_getall('modules', array('issystem' => 0), array(), 'name');
	$template_list = pdo_getall('site_templates',array(), array(), 'name');
	$group_have_module_app = array();
	$group_have_module_wxapp = array();
	$group_have_template = array();

	if (!empty($group_id)) {
		$uni_groups = uni_groups();
		$module_group = $uni_groups[$group_id];
		$group_have_module_app = empty($module_group['modules']) ? array() : $module_group['modules'];
		$group_have_module_wxapp = empty($module_group['wxapp']) ? array() : $module_group['wxapp'];
		$group_have_template = empty($module_group['templates']) ? array() : $module_group['templates'];
	}

	$group_not_have_module_app = array();
	$group_not_have_module_wxapp = array();
	if (!empty($module_list)) {
		foreach ($module_list as $name => $module_info) {
			if (file_exists(IA_ROOT.'/addons/' . $module_name . '/icon-custom.jpg')) {
				$module_info['logo'] = tomedia(IA_ROOT . '/addons/' . $module_info['name'] . '/icon-custom.jpg');
			} else {
				$module_info['logo'] = tomedia(IA_ROOT . '/addons/' . $module_info['name'] . '/icon.jpg');
			}
			if ($module_info['app_support'] == 2 && !in_array($name, array_keys($group_have_module_app))) {
				$group_not_have_module_app[$name] = $module_info;
			}
			if ($module_info['wxapp_support'] == 2 && !in_array($name, array_keys($group_have_module_wxapp))) {
				$group_not_have_module_wxapp[$name] = $module_info;
			}
		}
	}
	$group_not_have_template = array();//套餐未拥有模板
	if (!empty($template_list)) {
		foreach ($template_list as $template) {
			if (!in_array($template['name'], array_keys($group_have_template))) {
				$group_not_have_template[$template['name']] =  $template;
			}
		}
	}
}
template('system/module-group');