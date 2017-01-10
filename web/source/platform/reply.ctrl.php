<?php
/**
 * 自动回复
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
load()->model('reply');
load()->model('module');

$dos = array('display', 'post', 'delete', 'change_status', 'change_keyword_status');
$do = in_array($do, $dos) ? $do : 'display';

$m = empty($_GPC['m']) ? 'keyword' : trim($_GPC['m']);
if ($m == 'keyword') {
	uni_user_permission_check('platform_reply');
} elseif ($m == 'special') {
	uni_user_permission_check('platform_reply_special');
} elseif ($m == 'welcome' || $m == 'default') {
	uni_user_permission_check('platform_reply_system');
} elseif($m == 'apply') {
	uni_user_permission_check('platform_reply_apply');
} else {
	$modules = uni_modules();
	$_W['current_module'] = $modules[$m];
}
$_W['page']['title'] = '自动回复';
if(empty($m)) {
	message('错误访问.');
}

if ($m == 'special') {
	$mtypes = array(
		'image' => '图片消息',
		'voice' => '语音消息',
		'video' => '视频消息',
		'shortvideo' => '小视频消息',
		'location' => '位置消息',
		'trace' => '上报地理位置',
		'link' => '链接消息',
		'merchant_order' => '微小店消息',
		'ShakearoundUserShake' => '摇一摇:开始摇一摇消息',
		'ShakearoundLotteryBind' => '摇一摇:摇到了红包消息',
		'WifiConnected' => 'Wifi连接成功消息'
	);
}
//功能模块用
$sysmods = system_modules();
if(in_array($m, array('custom'))) {
	$site = WeUtility::createModuleSite('reply');
	$site_urls = $site->getTabUrls();
}


if($do == 'display') {
	if ($m == 'keyword' || !in_array($m, $sysmods)) {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 8;
		$cids = $parentcates = $list =  array();
		$condition = 'uniacid = :uniacid';
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		if(isset($_GPC['type']) && !empty($_GPC['type'])) {
			if($_GPC['type'] == 'apply') {
				$condition .= ' AND module NOT IN ("basic", "news", "images", "voice", "video", "music", "wxcard", "reply")';
			}else {
				$condition .= " AND (FIND_IN_SET(:type, `containtype`) OR module = :type)";
				$params[':type'] = $_GPC['type'];	
			}
		}
		if(!in_array($m, $sysmods)) {
			$condition .= " AND `module` = :type";
			$params[':type'] = $m;
		}
		if(isset($_GPC['keyword'])) {
			$condition .= ' AND `name` LIKE :keyword';
			$params[':keyword'] = "%{$_GPC['keyword']}%";
		}
		$replies = reply_search($condition, $params, $pindex, $psize, $total);
		$pager = pagination($total, $pindex, $psize);
		if (!empty($replies)) {
			foreach($replies as &$item) {
				$condition = '`rid`=:rid';
				$params = array();
				$params[':rid'] = $item['id'];
				$item['keywords'] = reply_keywords_search($condition, $params);
				$item['allreply'] = reply_contnet_search($item['id']);
				$entries = module_entries($item['module'], array('rule'),$item['id']);
				if(!empty($entries)) {
					$item['options'] = $entries['rule'];
				}
				//若是模块，获取模块图片
				if (!in_array($item['module'], array("basic", "news", "images", "voice", "video", "music", "wxcard", "reply"))) {
					if (file_exists(IA_ROOT.'/addons/'.$item['module'].'/icon-custom.jpg')) {
						$item['logo'] = tomedia(IA_ROOT.'/addons/'.$item['module'].'/icon-custom.jpg');
					}elseif(file_exists(IA_ROOT.'/addons/'.$item['module'].'/icon.jpg')) {
						$item['logo'] = tomedia(IA_ROOT.'/addons/'.$item['module'].'/icon.jpg');
					}else {
						$item['logo'] = './resource/images/11.png';
					}
				}
			}
			unset($item);
		}
	}
	if ($m == 'special') {
		$setting = uni_setting_load('default_message', $_W['uniacid']);
		$setting = $setting['default_message'] ? $setting['default_message'] : array();
	}
	if ($m == 'welcome') {
		$setting = uni_setting($_W['uniacid'], array('welcome'));
		$ruleid = pdo_getcolumn('rule_keyword', array('uniacid' => $_W['uniacid'], 'content' => $setting['welcome']), 'rid');
	}
	if ($m == 'default') {
		$setting = uni_setting($_W['uniacid'], array('default'));
		$ruleid = pdo_getcolumn('rule_keyword', array('uniacid' => $_W['uniacid'], 'content' => $setting['default']), 'rid');
	}

	$entries = module_entries($m);
	template('platform/reply');
}

if($do == 'post') {
	if ($m == 'keyword' || !in_array($m, $sysmods)) {
		$module['title'] = '关键字自动回复';
		if ($_W['isajax'] && $_W['ispost']) {
			/*检测规则是否已经存在*/
			$sql = 'SELECT `rid` FROM ' . tablename('rule_keyword') . " WHERE `uniacid` = :uniacid  AND `content` = :content";
			$result = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'], ':content' => $_GPC['keyword']));
			if (!empty($result)) {
				$keywords = array();
				foreach ($result as $reply) {
					$keywords[] = $reply['rid'];
				}
				$rids = implode($keywords, ',');
				$sql = 'SELECT `id`, `name` FROM ' . tablename('rule') . " WHERE `id` IN ($rids)";
				$rules = pdo_fetchall($sql);
				message(error(0, @json_encode($rules)), '', 'ajax');
			}
			message(error(-1), '', 'ajax');
		}
		$rid = intval($_GPC['rid']);
		if(!empty($rid)) {
			$reply = reply_single($rid);
			if(empty($reply) || $reply['uniacid'] != $_W['uniacid']) {
				message('抱歉，您操作的规则不在存或是已经被删除！', url('platform/reply', array('m' => $m)), 'error');
			}
			foreach($reply['keywords'] as &$kw) {
				$kw = array_elements(array('type', 'content'), $kw);
			}
			unset($kw);
		}
		if(checksubmit('submit')) {
			if(empty($_GPC['rulename'])) {
				message('必须填写回复规则名称.');
			}
			$keywords = @json_decode(htmlspecialchars_decode($_GPC['keywords']), true);
			if(empty($keywords)) {
				message('必须填写有效的触发关键字.');
			}
			$containtype = '';
			foreach ($_GPC['reply'] as $replykey => $replyval) {
				if(!empty($replyval)) {
					$containtype .= substr($replykey, 6).',';
				}
			}
			$rule = array(
				'uniacid' => $_W['uniacid'],
				'name' => $_GPC['rulename'],
				'module' => $m == 'keyword' ? 'reply' : $m,
				'containtype' => $containtype,
				'reply_type' => intval($_GPC['reply_type']) == 2 ? 2 : 1,
				'status' => $_GPC['status'] == 'true' ? 1 : 0,
				'displayorder' => intval($_GPC['displayorder_rule']),
			);
			if($_GPC['istop'] == 1) {
				$rule['displayorder'] = 255;
			} else {
				$rule['displayorder'] = range_limit($rule['displayorder'], 0, 254);
			}
			$module = WeUtility::createModule('core');
			if(empty($module)) {
				message('抱歉，模块不存在请重新选择其它模块！');
			}
			$msg = $module->fieldsFormValidate();
			if(is_string($msg) && trim($msg) != '') {
				message($msg);
			}
			if (!empty($rid)) {
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
					'module' => $m == 'keyword' ? 'reply' : $m,
					'status' => $rule['status'],
					'displayorder' => $rule['displayorder'],
				);
				foreach($keywords as $kw) {
					$krow = $rowtpl;
					$krow['type'] = range_limit($kw['type'], 1, 4);
					$krow['content'] = $kw['content'];
					pdo_insert('rule_keyword', $krow);
				}
				$kid = pdo_insertid();
				$module->fieldsFormSubmit($rid);
				message('回复规则保存成功！', url('platform/reply'));
			} else {
				message('回复规则保存失败, 请联系网站管理员！');
			}
		}
		template('platform/reply-post');
	}
	if ($m == 'special') {
		$type = trim($_GPC['type']);
		$setting = uni_setting_load('default_message', $_W['uniacid']);
		$setting = $setting['default_message'] ? $setting['default_message'] : array();
		if (checksubmit('submit')) {
			$rule_id = intval(trim(htmlspecialchars_decode($_GPC['reply']['reply_keyword']), "\""));
			$status = $_GPC['status'];
			if (empty($status)) {
				$setting[$type] = array('type' => '');
				uni_setting_save('default_message', $setting);
				message('关闭成功', url('platform/reply', array('m' => 'special')));
			}
			$reply_module = WeUtility::createModule('reply');
			$result = $reply_module->fieldsFormValidate();
			if (is_error($result)) {
				message($result['message'], '', 'info');
			}
			$result = $reply_module->fieldsFormSubmit($rule_id);
			$rule = pdo_get('rule_keyword', array('rid' => $rule_id, 'uniacid' => $_W['uniacid']));
			$setting[$type] = array('type' => 'keyword', 'keyword' => $rule['content']);
			uni_setting_save('default_message', $setting);
			message('发布成功', url('platform/reply', array('m' => 'special')));
		}
		$rule_id = pdo_getcolumn('rule_keyword', array('uniacid' => $_W['uniacid'], 'content' => $setting[$type]['keyword']), 'rid');
		template('platform/specialreply-post');
	}
	if ($m == 'welcome') {
		if (checksubmit('submit')) {
			$rule_id = intval(trim(htmlspecialchars_decode($_GPC['reply']['reply_keyword']), "\""));
			$rule = pdo_get('rule_keyword', array('rid' => $rule_id, 'uniacid' => $_W['uniacid']));
			$settings = array(
				'welcome' => $rule['content']
			);
			$item = pdo_fetch('SELECT uniacid FROM '.tablename('uni_settings')." WHERE uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
			if(!empty($item)){
				pdo_update('uni_settings', $settings, array('uniacid' => $_W['uniacid']));
			}else{
				$settings['uniacid'] = $_W['uniacid'];
				pdo_insert('uni_settings', $settings);
			}
			cache_delete("unisetting:{$_W['uniacid']}");
			message('系统回复更新成功！', url('platform/reply', array('m' => 'welcome')));
		}
	}
	if ($m == 'default') {
		if (checksubmit('submit')) {
			$rule_id = intval(trim(htmlspecialchars_decode($_GPC['reply']['reply_keyword']), "\""));
			$rule = pdo_get('rule_keyword', array('rid' => $rule_id, 'uniacid' => $_W['uniacid']));
			$settings = array(
				'default' => $rule['content']
			);
			$item = pdo_fetch('SELECT uniacid FROM '.tablename('uni_settings')." WHERE uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
			if(!empty($item)){
				pdo_update('uni_settings', $settings, array('uniacid' => $_W['uniacid']));
			}else{
				$settings['uniacid'] = $_W['uniacid'];
				pdo_insert('uni_settings', $settings);
			}
			cache_delete("unisetting:{$_W['uniacid']}");
			message('系统回复更新成功！', url('platform/reply', array('m' => 'default')));
		}
	}
	if ($m == 'apply') {
		include IA_ROOT . '/framework/library/pinyin/pinyin.php';
		$pinyin = new Pinyin_Pinyin();
		$module['title'] = '应用关键字';
		$installedmodulelist = uni_modules();
		foreach ($installedmodulelist as &$value) {
			$value['official'] = empty($value['issystem']) && (strexists($value['author'], 'WeEngine Team') || strexists($value['author'], '微擎团队'));
		}
		unset($value);
		foreach($installedmodulelist as $name => $module) {
			$module['title_first_pinyin'] = $pinyin->get_first_char($module['title']);
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
			$module['icon'] = $cion;

			if($module['enabled'] == 1) {
				$enable_modules[$name] = $module;
			} else {
				$unenable_modules[$name] = $module;
			}
		}
		$current_user_permissions = pdo_getall('users_permission', array('uid' => $_W['user']['uid'], 'uniacid' => $_W['uniacid']), array(), 'type');
		if (!empty($current_user_permissions)) {
			$current_user_permission_types = array_keys($current_user_permissions);
		}
		$moudles = true;
		template('platform/reply-post');
	}
}

if($do == 'delete') {
	$rids = $_GPC['rid'];
	if(!is_array($rids)) {
		$rids = array($rids);
	}
	if(empty($rids)) {
		message('非法访问.');
	}
	foreach($rids as $rid) {
		$rid = intval($rid);
		$reply = reply_single($rid);
		if(empty($reply) || $reply['uniacid'] != $_W['uniacid']) {
			message('抱歉，您操作的规则不在存或是已经被删除！', url('platform/reply', array('m' => $m)), 'error');
		}
		//删除回复，关键字及规则
		if (pdo_delete('rule', array('id' => $rid))) {
			pdo_delete('rule_keyword', array('rid' => $rid));
			//删除统计相关数据
			pdo_delete('stat_rule', array('rid' => $rid));
			pdo_delete('stat_keyword', array('rid' => $rid));
			//调用模块中的删除
			$module = WeUtility::createModule('reply');
			if (method_exists($module, 'ruleDeleted')) {
				$module->ruleDeleted($rid);
			}
		}
	}
	message('规则操作成功！', referer());
}

//非文字自动回复切换开启关闭状态
if ($do == 'change_status') {
	$status = intval($_GPC['status']);
	$type = $_GPC['type'];
	$setting = uni_setting_load('default_message', $_W['uniacid']);
	$setting = $setting['default_message'] ? $setting['default_message'] : array();
	$setting[$type]['type'] = $status;
	$result = uni_setting_save('default_message', $setting);
	if($result) {
		message(error(0), '','ajax');
	}
}

if($do == 'change_keyword_status') {
	/*改变状态：是否开启该关键字*/
	$id = intval($_GPC['id']);
	$result = pdo_get('rule', array('id' => $id), array('status'));
	if (!empty($result)) {
		$rule = $rule_keyword = false;
		if($result['status'] == 1) {
			$rule = pdo_update('rule', array('status' => 0), array('id' => $id));
			$rule_keyword = pdo_update('rule_keyword', array('status' => 0), array('uniacid' => $_W['uniacid'], 'rid' => $id));
		}else {
			$rule = pdo_update('rule', array('status' => 1), array('id' => $id));
			$rule_keyword = pdo_update('rule_keyword', array('status' => 1), array('uniacid' => $_W['uniacid'], 'rid' => $id));
		}
		if($rule && $rule_keyword) {
			message(error(0), '', 'ajax');
		}else {
			message(error(-1), '', 'ajax');
		}
	}
	message(error(-1), '', 'ajax');
}