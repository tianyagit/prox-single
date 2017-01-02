<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
defined('IN_IA') or exit('Access Denied');
load()->model('reply');
load()->model('module');

$dos = array('module');
$do = in_array($do, $dos) ? $do : 'module';

uni_user_permission_check('platform_cover_' . $do, true, 'cover');

if($do == 'module') {
	$modulename = $_GPC['m'];
	$module = $_W['current_module'] = module_fetch($modulename);
	if (empty($module)) {
		message('模块不存在或是未安装');
	}
	if (!empty($module['isrulefields'])) {
		$url = url('platform/reply', array('m' => $module['name']));
	}
	if (empty($url)) {
		$url = url('platform/cover', array('m' => $module['name']));
	}
	define('ACTIVE_FRAME_URL', $url);
	
	$entries = module_entries($modulename);
	$cover_list = pdo_getall('cover_reply', array('module' => $entry['module'], 'do' => $entry['do'], 'uniacid' => $_W['uniacid']));
	print_r($cover_list);exit;
	
} elseif ($do == 'post') {
	$eid = intval($_GPC['eid']);
	if(empty($eid)) {
		message('访问错误');
	}
	$entry = module_entry($eid);
	if (is_error($entry)) {
		message('模块菜单不存在或是模块已经被删除');
	}
	$module = module_fetch($entry['module']);
	$cover['title'] = $entry['title'];
	
	if (!empty($module['isrulefields'])) {
		$url = url('platform/reply', array('m' => $module['name']));
	}
	if (empty($url) && !empty($entries['cover'])) {
		$url = url('platform/cover', array('eid' => $eid));
	}
	define('ACTIVE_FRAME_URL', $url);
}

$cover = pdo_get('cover_reply', array('module' => $entry['module'], 'do' => $entry['do'], 'uniacid' => $_W['uniacid']));

if(!empty($cover)) {
	$cover['saved'] = true;
	if(!empty($cover['thumb'])) {
		$cover['src'] = tomedia($cover['thumb']);
	}
	$cover['url_show'] = $entry['url_show'];
	$reply = reply_single($cover['rid']);
	$entry['title'] = $cover['title'];
} else {
	$cover['title'] = $entry['title'];
	$cover['url_show'] = $entry['url_show'];
}
if(empty($reply)) {
	$reply = array();
}
if (checksubmit('submit')) {
	if(trim($_GPC['keywords']) == '') {
		message('必须输入触发关键字.');
	}
	
	$keywords = @json_decode(htmlspecialchars_decode($_GPC['keywords']), true);
	if(empty($keywords)) {
		message('必须填写有效的触发关键字.');
	}
	$rule = array(
		'uniacid' => $_W['uniacid'],
		'name' => $entry['title'],
		'module' => 'cover', 
		'status' => intval($_GPC['status']),
	);
	if(!empty($_GPC['istop'])) {
		$rule['displayorder'] = 255;
	} else {
		$rule['displayorder'] = range_limit($_GPC['displayorder'], 0, 254);
	}
	if (!empty($reply)) {
		$rid = $reply['id'];
		$result = pdo_update('rule', $rule, array('id' => $rid));
	} else {
		$result = pdo_insert('rule', $rule);
		$rid = pdo_insertid();
	}
	
	if (!empty($rid)) {
		//更新，添加，删除关键字
		$sql = 'DELETE FROM '. tablename('rule_keyword') . ' WHERE `rid`=:rid AND `uniacid`=:uniacid';
		$pars = array();
		$pars[':rid'] = $rid;
		$pars[':uniacid'] = $_W['uniacid'];
		pdo_query($sql, $pars);

		$rowtpl = array(
			'rid' => $rid,
			'uniacid' => $_W['uniacid'],
			'module' => 'cover',
			'status' => $rule['status'],
			'displayorder' => $rule['displayorder'],
		);
		foreach($keywords as $kw) {
			$krow = $rowtpl;
			$krow['type'] = range_limit($kw['type'], 1, 4);
			$krow['content'] = $kw['content'];
			pdo_insert('rule_keyword', $krow);
		}
		
		$entry = array(
			'uniacid' => $_W['uniacid'],
			'multiid' => 0,
			'rid' => $rid,
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'thumb' => $_GPC['thumb'],
			'url' => $entry['url'],
			'do' => $entry['do'],
			'module' => $entry['module'],
		);
		if (empty($cover['id'])) {
			pdo_insert('cover_reply', $entry);
		} else {
			pdo_update('cover_reply', $entry, array('id' => $cover['id']));
		}
		message('封面保存成功！', 'refresh', 'success');
	} else {
		message('封面保存失败, 请联系网站管理员！');
	}
}

template('platform/cover');