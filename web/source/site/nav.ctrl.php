<?php
/**
 * 微站导航管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('module');

$dos = array('home', 'profile', 'homemenu_display', 'homemenu_post', 'homemenu_del', 'homemenu_switch');
$do = in_array($do, $dos) ? $do : 'home';

uni_user_permission_check('platform_nav_' . $do, true, 'nav');
$modulename = $_GPC['m'];

//微官网首页快捷菜单：homemenu_display、homemenu_post、homemenu_del、homemenu_switch(切换开关状态)
if($do == 'homemenu_display' && $_W['isajax'] && $_W['ispost']) {
	$multiid = intval($_GPC['multiid']);
	$pars = array(
		':uniacid' => $_W['uniacid'],
		':multiid' => $multiid,
	);
	$sql = 'SELECT * FROM ' . tablename('site_nav') . ' WHERE `uniacid`=:uniacid AND `position`= 1 AND `multiid`=:multiid ORDER BY `displayorder` DESC, id ASC';
	$navs = pdo_fetchall($sql, $pars);
	$navigations = array();
	if(!empty($navs)) {
		foreach($navs as $nav) {
			/*处理icon图片链接*/
			if (!empty($nav['icon'])) {
				$nav['icon'] = tomedia($nav['icon']);
			}
			if (is_serialized($nav['css'])) {
				$nav['css'] = iunserializer($nav['css']);
			}
			if(empty($nav['css']['icon']['icon'])) {
				$nav['css']['icon']['icon'] = 'fa fa-external-link';
			}
			$navigations[] = array(
				'id' => $nav['id'],
				'module' => $nav['module'],
				'name' => $nav['name'],
				'url' => $nav['url'],
				'from' => $nav['module'] ? 'define' : 'custom',
				'status' => $nav['status'],
				'remove' => true,
				'displayorder' => $nav['displayorder'],
				'icon' => $nav['icon'],
				'css' => $nav['css'],
				'section' => $nav['section'],
				'description' => $nav['description']
			);
		}
	}
	message($navigations, 'ajax', 'success');
}
if($do == 'homemenu_post' && $_W['isajax'] && $_W['ispost']) {
	$multiid = intval($_GPC['multiid']);
	$post = $_GPC['menu_info'];
	if(empty($post['name'])) {
		//抱歉，请输入导航菜单的名称！
		message('-1', 'ajax', 'error');
	}
	$url = ((strexists($post['url'], 'http://') || strexists($post['url'], 'https://')) && !strexists($post['url'], '#wechat_redirect')) ? $post['url'] . '#wechat_redirect' : $post['url'];
	if (intval($post['section']) > 10) {
		$post['section'] = 10;
	}else {
		$post['section'] = intval($post['section']);
	}
	$data = array(
		'uniacid' => $_W['uniacid'],
		'multiid' => $multiid,
		'section' => $post['section'],
		'name' => $post['name'],
		'description' => $post['description'],
		'displayorder' => intval($post['displayorder']),
		'url' => $url,
		'status' => intval($post['status']),
		'position' => 1
	);
	//获取icon的类型 1:系统内置图标 2:自定义上传图标
	$icontype = $post['icontype'];
	if ($icontype == 1) {
		$data['icon'] = '';
		$data['css'] = serialize(array(
				'icon' => array(
					'font-size' => $post['css']['icon']['width'],
					'color' => $post['css']['icon']['color'],
					'width' => $post['css']['icon']['width'],
					'icon' => empty($post['css']['icon']['icon']) ? 'fa fa-external-link' : $post['css']['icon']['icon'],
				),
				'name' => array(
					'color' => $post['css']['icon']['color'],
				),
			)
		);
	} else {
		$data['css'] = '';
		$data['icon'] = $post['icon'];
	}
	if(empty($post['id'])) {
		pdo_insert('site_nav', $data);
	} else {
		pdo_update('site_nav', $data, array('id' => $post['id']));
	}
	message('0', 'ajax', 'success');
}

if($do == 'homemenu_del' && $_W['isajax'] && $_W['ispost']) {
	$id = intval($_GPC['id']);
	$nav_exist = pdo_get('site_nav', array('id' => $id, 'uniacid' => $_W['uniacid']));
	if(empty($nav_exist)){
		//本公众号不存在该导航
		message('-1', 'ajax', 'error');
	}else {
		$nav_del = pdo_delete('site_nav', array('id' => $id));
		if(!empty($nav_del)){
			message('0', 'ajax', 'success');
		}else {
			//删除失败
			message('1', 'ajax', 'error');
		}
	}
	exit;
}

if($do == 'homemenu_switch' && $_W['isajax'] && $_W['ispost']) {
	$id = intval($_GPC['id']);
	$nav_exist = pdo_get('site_nav', array('id' => $id, 'uniacid' => $_W['uniacid']));
	if(empty($nav_exist)){
		//本公众号不存在该导航
		message('-1', 'ajax', 'error');
	}else {
		$status = $nav_exist['status'] == 1 ? 0 : 1;
		$nav_update = pdo_update('site_nav', array('status' => $status), array('id' => $id));
		if(!empty($nav_update)){
			message('0', 'ajax', 'success');
		}else {
			//更新失败
			message('1', 'ajax', 'error');
		}
	}
	exit;
	message('switch', 'ajax', 'success');
}

//首页导航
if ($do == 'home' || $do == 'profile') {
	$modules = uni_modules();
	$bindings = array();
	
	if(!empty($modulename)) {
		$modulenames = array($modulename);
	} else {
		$modulenames = array_keys($modules);
	}
	
	foreach($modulenames as $modulename) {
		$entries = module_entries($modulename, array($do));
		if(!empty($entries[$do])) {
			$bindings[$modulename] = $entries[$do];
		}
	}
	$entries = array();
	if(!empty($bindings)) {
		foreach($bindings as $modulename => $group) {
			foreach($group as $bind) {
				$entries[] = array('module' => $modulename, 'from' => $bind['from'], 'title' => $bind['title'], 'url' => $bind['url']);
			}
		}
	}
	template('site/nav');
}