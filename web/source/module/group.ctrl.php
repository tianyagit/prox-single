<?php
/**
 * 编辑应用套餐
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('module');
load()->model('user');
load()->model('module');

$dos = array('display', 'delete', 'post', 'save');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';
/* xstart */
if (IMS_FAMILY == 'x') {
	if (!in_array($_W['role'], array(ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_MANAGER, ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER))){
		itoast('无权限操作！', referer(), 'error');
	}
}
/* xend */
/* svstart */
if (IMS_FAMILY == 's' || IMS_FAMILY == 'v') {
	if (!in_array($_W['role'], array(ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_MANAGER, ACCOUNT_MANAGE_NAME_FOUNDER))){
		itoast('无权限操作！', referer(), 'error');
	}
}
/* svend */

/* xstart */
if (IMS_FAMILY == 'x') {
	if ($do != 'display' && !in_array($_W['role'], array(ACCOUNT_MANAGE_NAME_FOUNDER, ACCOUNT_MANAGE_NAME_VICE_FOUNDER))) {
		itoast('您只有查看权限！', url('module/group'), 'error');
	}
}
/* xend */

/* svstart */
if (IMS_FAMILY == 's' || IMS_FAMILY == 'v') {
	if ($do != 'display' && !in_array($_W['role'], array(ACCOUNT_MANAGE_NAME_FOUNDER))) {
		itoast('您只有查看权限！', url('module/group'), 'error');
	}
}
/* svend */
if ($do == 'save') {
	$modules = empty($_GPC['modules']) ? array() : (array)$_GPC['modules'];
	$wxapp = empty($_GPC['wxapp']) ? array() : (array)$_GPC['wxapp'];
	$webapp = empty($_GPC['webapp']) ? array() : (array)array_keys($_GPC['webapp']);
	$package_info = array(
		'id' => intval($_GPC['id']),
		'name' => $_GPC['name'],
		'modules' => array_merge($modules, $wxapp, $webapp),
		'templates' => $_GPC['templates'],
	);

	$package_info = module_save_group_package($package_info);

	if (is_error($package_info)) {
		iajax(1, $package_info['message'], '');
	}
	iajax(0, '', url('module/group'));
}

if ($do == 'display') {
	$_W['page']['title'] = '应用套餐列表';
	$pageindex = max(1, intval($_GPC['page']));
	$pagesize = 10;

	$condition = 'WHERE uniacid = 0';
	$params = array();
	$name = safe_gpc_string($_GPC['name']);
	if (!empty($name)) {
		$condition .= " AND name LIKE :name";
		$params[':name'] = "%{$name}%";
	}
	/* xstart */
	if (IMS_FAMILY == 'x') {
		if (user_is_vice_founder()) {
			$condition .= " AND owner_uid = :owner_uid";
			$params[':owner_uid'] = $_W['uid'];
		}
	}
	/* xend */
	$modules_group_list = pdo_fetchall("SELECT * FROM " . tablename('uni_group') . $condition . " LIMIT " . ($pageindex - 1) * $pagesize . "," . $pagesize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('uni_group') . $condition, $params);
	$pager = pagination($total, $pageindex, $pagesize);
	if (!empty($modules_group_list)) {
		foreach ($modules_group_list as $key => $value) {
			$modules = (array)iunserializer($value['modules']);
			if (!empty($modules)) {
				foreach ($modules as $module_name) {
					$module_info = module_fetch($module_name);
					if (empty($module_info)) {
						continue;
					}
					if ($module_info['account_support'] == MODULE_SUPPORT_ACCOUNT || $module_info['app_support'] == MODULE_SUPPORT_ACCOUNT) {
						$modules_group_list[$key]['account_num'] = intval($modules_group_list[$key]['account_num']) > 0 ? (intval($modules_group_list[$key]['account_num']) + 1) : 1;
						$modules_group_list[$key]['account_modules'][] = $module_info;
					}
					if ($module_info['wxapp_support'] == MODULE_SUPPORT_WXAPP) {
						$modules_group_list[$key]['wxapp_num'] = intval($modules_group_list[$key]['wxapp_num']) > 0 ? (intval($modules_group_list[$key]['wxapp_num']) + 1) : 1;
						$modules_group_list[$key]['wxapp_modules'][] = $module_info;
					}
					if ($module_info['phoneapp_support'] == MODULE_NOSUPPORT_PHONEAPP) {
						$modules_group_list[$key]['phoneapp_num'] = intval($modules_group_list[$key]['phoneapp_num']) > 0 ? (intval($modules_group_list[$key]['phoneapp_num']) + 1) : 1;
						$modules_group_list[$key]['phoneapp_modules'][] = $module_info;
					}
					if ($module_info['webapp_support'] == MODULE_NOSUPPORT_WEBAPP) {
						$modules_group_list[$key]['webapp_num'] = intval($modules_group_list[$key]['webapp_num']) > 0 ? (intval($modules_group_list[$key]['webapp_num']) + 1) : 1;
						$modules_group_list[$key]['webapp_modules'][] = $module_info;
					}
				}
			}

			$templates = (array)iunserializer($value['templates']);

			$modules_group_list[$key]['template_num'] = !empty($templates) ? count($templates) : 0;
			$modules_group_list[$key]['templates'] = pdo_getall('site_templates', array('id' => $templates), array('id', 'name', 'title'), 'name');
		}

	}

	//模版调用（主应用与插件）
	$modules = user_modules($_W['uid']);
}

if ($do == 'delete') {
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		pdo_delete('uni_group', array('id' => $id));
		cache_build_uni_group();
		cache_build_account_modules();
	}
	itoast('删除成功！', referer(), 'success');
}

if ($do == 'post') {
	$group_id = intval($_GPC['id']);
	$_W['page']['title'] = $group_id ? '编辑应用套餐' : '添加应用套餐';

	$group_have_module_app = array();
	$group_have_module_wxapp = array();
	$group_have_template = array();
	$group_have_module_webapp = array();
	$group_have_module_phoneapp = array();
	if (!empty($group_id)) {
		$uni_groups = uni_groups();
		$module_group = $uni_groups[$group_id];
		$group_have_module_app = empty($module_group['modules']) ? array() : $module_group['modules'];
		$group_have_module_wxapp = empty($module_group['wxapp']) ? array() : $module_group['wxapp'];
		$group_have_template = empty($module_group['templates']) ? array() : $module_group['templates'];
		$group_have_module_webapp = empty($module_group['webapp']) ? array() : $module_group['webapp'];
		$group_have_module_phoneapp = empty($module_group['phoneapp']) ? array() : $module_group['phoneapp'];
	}
	$module_list = user_uniacid_modules($_W['uid']);
	$group_not_have_module_app = array();
	$group_not_have_module_wxapp = array();
	$group_not_have_module_webapp = array();
	$group_not_have_module_phoneapp = array();
	if (!empty($module_list)) {
		foreach ($module_list as $name => $module_info) {
			$module_info = module_fetch($name);
			if ($module_info['app_support'] == MODULE_SUPPORT_WXAPP && !in_array($name, array_keys($group_have_module_app))) {
				if (!empty($module_info['main_module'])) {
					if (in_array($module_info['main_module'], array_keys($group_have_module_app))) {
						$group_not_have_module_app[$name] = $module_info;
					}
				} elseif (is_array($module_info['plugin_list']) && !empty($module_info['plugin_list'])) {
					$group_not_have_module_app[$name] = $module_info;
					foreach ($module_info['plugin_list'] as $plugin) {
						if (!in_array($plugin, array_keys($group_have_module_app))) {
							$plugin = module_fetch($plugin);
							if (!empty($plugin)) {
								$group_not_have_module_app[$plugin['name']] = $plugin;
							}
						}
					}
				} else {
					$group_not_have_module_app[$name] = $module_info;
				}
			}
			if ($module_info['wxapp_support'] == MODULE_SUPPORT_WXAPP && !in_array($name, array_keys($group_have_module_wxapp))) {
				$group_not_have_module_wxapp[$name] = $module_info;
			}

			if ($module_info['webapp_support'] == MODULE_SUPPORT_WEBAPP && !in_array($name, array_keys($group_have_module_webapp))) {
				$group_not_have_module_webapp[$name] = $module_info;
			}

			if ($module_info['phoneapp_support'] == MODULE_SUPPORT_PHONEAPP && !in_array($name, array_keys($group_have_module_phoneapp))) {
				$group_not_have_module_phoneapp[$name] = $module_info;
			}
		}
	}

	/* xstart */
	if (IMS_FAMILY == 'x') {
		if (user_is_vice_founder($_W['uid'])) {
			$template_list = user_founder_templates($_W['user']['groupid']);
		} else {
			$template_list = pdo_getall('site_templates', array(), array(), 'name');
		}
	}
	/* xend */

	/* svstart */
	if (IMS_FAMILY == 's' || IMS_FAMILY == 'v') {
		$template_list = pdo_getall('site_templates', array(), array(), 'name');
	}
	/* svend */

	$group_not_have_template = array();//套餐未拥有模板
	if (!empty($template_list)) {
		foreach ($template_list as $template) {
			if (!in_array($template['name'], array_keys($group_have_template))) {
				$group_not_have_template[$template['name']] =  $template;
			}
		}
	}
}
template('module/group');