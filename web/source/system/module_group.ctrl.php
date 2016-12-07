<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 2016/12/2
 * Time: 16:23
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('display', 'delete', post);
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

load()->model('module');

if ($do == 'display') {
	$_W['page']['title'] = '应用套餐列表';

	$param = array('uniacid' => 0);
	if (!empty($_GPC['name'])) {
		$param['name like'] = "%". trim($_GPC['name']) ."%";
	}
	$modules_group_list = pdo_getall('uni_group', $param);
	if (!empty($modules_group_list)) {
		foreach ($modules_group_list as &$group) {
			if (!empty($group['modules'])) {
				$modules = iunserializer($group['modules']);
				if (is_array($modules)) {
					$group['modules'] = pdo_fetchall("SELECT name, title FROM ".tablename('modules')." WHERE `name` IN ('".implode("','", $modules)."')");
				}
			}
			if (!empty($group['templates'])) {
				$templates = iunserializer($group['templates']);
				if (is_array($templates)) {
					$group['templates'] = pdo_fetchall("SELECT name, title FROM ".tablename('site_templates')." WHERE id IN ('".implode("','", $templates)."')");
				}
			}
		}
	}
}
if ($do == 'delete') {
	$group_list = $_GPC['group_list'];
	if (!empty($group_list)) {
		pdo_delete('uni_group', array('id' => $group_list));
		cache_build_account_modules();
	}
	message('删除成功！', referer(), 'success');
}

if ($do == 'post') {
	$id = intval($_GPC['id']);
	$_W['page']['title'] = $id ? '编辑应用套餐' : '添加应用套餐';

	$module_list = pdo_getall('modules', array('issystem' => 0), array(), 'name');
	$template_list = pdo_getall('site_templates');
	if (!empty($id)) {
		$module_group = pdo_get('uni_group', array('id' => $id));
		$module_group['modules'] = empty($module_group['modules']) ? array() : iunserializer($module_group['modules']);
		$module_group['templates'] = empty($module_group['templates']) ? array() : iunserializer($module_group['templates']);
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['name'])) {
			message('请输入公众号组名称！');
		}
		$data = array(
			'name' => $_GPC['name'],
			'modules' => iserializer($_GPC['module']),
			'templates' => iserializer($_GPC['template'])
		);
		if (empty($id)) {
			pdo_insert('uni_group', $data);
		} else {
			pdo_update('uni_group', $data, array('id' => $id));
			cache_build_account_modules();
		}
		module_build_privileges();
		message('公众号组更新成功！', url('system/module_group/display'), 'success');
	}
}

template('system/module_group');
