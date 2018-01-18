<?php

defined('IN_IA') or exit('Access Denied');


load()->model('phoneapp');
$account_info = permission_user_account_num();

$do = safe_gpc_belong($do, array('create_display', 'list', 'save', 'display', 'del_version'), 'list');

$uniacid = safe_gpc_int($_GPC['uniacid']);

if ($do == 'save') {
	if (empty($uniacid) && empty($account_info['phoneapp_limit']) && !user_is_founder($_W['uid'])) {
		iajax(-1, '创建APP个数已满', url('phoneapp/manage/create_display'));
	}

	$data = array(
		'uniacid' => $uniacid,
		'name' => safe_gpc_string($_GPC['name']),
		'description' => safe_gpc_string($_GPC['description']),
		'version' => safe_gpc_string($_GPC['version']),
		'modules' => iserializer(safe_gpc_array($_GPC['module'])),
		'createtime' => TIMESTAMP
	);

	if (empty($uniacid)) {
		$phoneapp_table = table('phoneapp');
		$result = $phoneapp_table->createPhoneApp($data);
	} else {
		unset($data['name']);
		$result = pdo_insert('phoneapp_versions', $data);
	}

	if (!empty($result)) {
		iajax(0, '创建成功', url('phoneapp/display'));
	}
	iajax(-1, '创建失败', url('phoneapp/manage/create_display'));
}

if($do == 'create_display') {
	$version_id = safe_gpc_int($_GPC['version_id']);
	$version_info = phoneapp_version($version_id);
	$modules = phoneapp_support_modules();
	template('phoneapp/create');
}

if($do == 'list') {

	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;

	$account_table = table('phoneapp');
	$account_table->searchWithType(array(ACCOUNT_TYPE_PHONEAPP_NORMAL));

	$keyword = trim($_GPC['keyword']);
	if (!empty($keyword)) {
		$account_table->searchWithKeyword($keyword);
	}

	$account_table->accountRankOrder();
	$account_table->searchWithPage($pindex, $psize);
	$list = $account_table->searchAccountList();
	$total = $account_table->getLastQueryTotal();

	$pager = pagination($total, $pindex, $psize);

	if (!empty($list)) {
		foreach ($list as &$account) {
			$account = uni_fetch($account['uniacid']);
			$account['switchurl'] = url('phoneapp/home/switch', array('uniacid' => $account['uniacid']));
		}
	}

	template('phoneapp/display');
}

if ($do == 'display') {
	$account = uni_fetch($uniacid);
	if (is_error($account)) {
		itoast($account['message'], url('account/manage', array('account_type' => ACCOUNT_TYPE_PHONEAPP_NORMAL)), 'error');
	} else {
		$phoneapp_table = table('phoneapp');
		$phoneapp_info = $phoneapp_table->phoneappAccountInfo($account['uniacid']);

		$version_exist = phoneapp_fetch($account['uniacid']);

		if (!empty($version_exist)) {
			$phoneapp_version_lists = phoneapp_version_all($account['uniacid']);
			$phoneapp_modules = phoneapp_support_modules();
		}
	}

	template('phoneapp/manage');
}

if ($do == 'del_version') {
	$id = intval($_GPC['versionid']);
	if (empty($id)) {
		iajax(1, '参数错误！');
	}
	$version_exist = pdo_get('phoneapp_versions', array('id' => $id, 'uniacid' => $uniacid));
	if (empty($version_exist)) {
		iajax(1, '模块版本不存在！');
	}
	$result = pdo_delete('phoneapp_versions', array('id' => $id, 'uniacid' => $uniacid));
	if (!empty($result)) {
		iajax(0, '删除成功！', referer());
	} else {
		iajax(1, '删除失败，请稍候重试！');
	}
}