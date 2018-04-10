<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/9
 * Time: 16:17
 */

defined('IN_IA') or exit('Access Denied');

load()->model('user');
load()->model('wxapp');
load()->model('phoneapp');
load()->model('account');

$dos = array('rank', 'display', 'switch', 'display_all');
$do = in_array($_GPC['do'], $dos) ? $do : 'display';
$_W['page']['title'] = '公众号列表 - 公众号';

$state = permission_account_user_role($_W['uid'], $_W['uniacid']);
$account_info = permission_user_account_num();

if ($do == 'display') {

	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;

	$type = safe_gpc_string($_GPC['type']);
	$type = !empty($type) ? $type : 'account';
	$title = safe_gpc_string($_GPC['title']);

	switch ($type) {
		case 'all':
			$tableName = ACCOUNT_TYPE_SIGN;
			$condition = array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH, ACCOUNT_TYPE_APP_NORMAL, ACCOUNT_TYPE_WEBAPP_NORMAL, ACCOUNT_TYPE_PHONEAPP_NORMAL);
			$fields = 'a.uniacid,b.type';
			break;
		case 'account':
			$tableName = ACCOUNT_TYPE_SIGN;
			$condition = array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH);
			break;
		case 'wxapp':
			$tableName = WXAPP_TYPE_SIGN;
			$condition = array(ACCOUNT_TYPE_APP_NORMAL);
			break;
		case 'webapp':
			$tableName = WEBAPP_TYPE_SIGN;
			$condition = array(ACCOUNT_TYPE_WEBAPP_NORMAL);
			break;
		case 'phoneapp':
			$tableName = PHONEAPP_TYPE_SIGN;
			$condition = array(ACCOUNT_TYPE_PHONEAPP_NORMAL);
			break;
	}

	$table = table($tableName);
	$table->searchWithType($condition);

	$keyword = safe_gpc_string($_GPC['keyword']);
	if (!empty($keyword)) {
		$table->searchWithKeyword($keyword);
	}

	$letter = safe_gpc_string($_GPC['letter']);
	if (isset($letter) && strlen($letter) == 1) {
		$table->searchWithLetter($letter);
	}

	$table->accountRankOrder();
	$table->searchWithPage($pindex, $psize);
	$list = $table->searchAccountListFields($fields);
	$total = $table->getLastQueryTotal();

	$list = array_values($list);

	foreach($list as &$account) {
		$account = uni_fetch($account['uniacid']);
		switch ($account['type']) {
			case ACCOUNT_TYPE_OFFCIAL_NORMAL :case ACCOUNT_TYPE_OFFCIAL_AUTH :
				$account['role'] = permission_account_user_role($_W['uid'], $account['uniacid']);
				break;
			case ACCOUNT_TYPE_APP_NORMAL :
				$account['versions'] = wxapp_get_some_lastversions($account['uniacid']);
				if (!empty($account['versions'])) {
					foreach ($account['versions'] as $version) {
						if (!empty($version['current'])) {
							$account['current_version'] = $version;
						}
					}
				}
				break;
			case ACCOUNT_TYPE_WEBAPP_NORMAL :
				$account['switchurl'] = url('webapp/home/switch', array('uniacid' => $account['uniacid']));
				break;
			case ACCOUNT_TYPE_PHONEAPP_NORMAL :
				$account['versions'] = phoneapp_get_some_lastversions($account['uniacid']);
				if (!empty($account['versions'])) {
					foreach ($account['versions'] as $version) {
						if (!empty($version['current'])) {
							$account['current_version'] = $version;
						}
					}
				}
				break;
		}
	}

	if ($_W['ispost']) {
		iajax(0, $list);
	}
}

if ($do == 'display_all') {
	$type = safe_gpc_string($_GPC['type']);
	$title = safe_gpc_string($_GPC['title']);

	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;

	$tableName = ACCOUNT_TYPE_SIGN;
	$condition = array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH,ACCOUNT_TYPE_APP_NORMAL,ACCOUNT_TYPE_WEBAPP_NORMAL,ACCOUNT_TYPE_PHONEAPP_NORMAL);

	$table = table($tableName);
	$table->searchWithType($condition);

	$keyword = safe_gpc_string($_GPC['keyword']);
	if (!empty($keyword)) {
		$table->searchWithKeyword($keyword);
	}

	$letter = safe_gpc_string($_GPC['letter']);
	if (isset($letter) && strlen($letter) == 1) {
		$table->searchWithLetter($letter);
	}

	$table->accountRankOrder();
	$table->searchWithPage($pindex, $psize);
	$list = $table->searchAccountListFields();


	foreach($list as &$account) {
		$account = uni_fetch($account['uniacid']);
		switch ($account['type']) {
			case ACCOUNT_TYPE_OFFCIAL_NORMAL :case ACCOUNT_TYPE_OFFCIAL_AUTH :
				$account['role'] = permission_account_user_role($_W['uid'], $account['uniacid']);
				break;
			case ACCOUNT_TYPE_APP_NORMAL :
				$account['versions'] = wxapp_get_some_lastversions($account['uniacid']);
				if (!empty($account['versions'])) {
					foreach ($account['versions'] as $version) {
						if (!empty($version['current'])) {
							$account['current_version'] = $version;
						}
					}
				}
				break;
			case ACCOUNT_TYPE_WEBAPP_NORMAL :
				$account['switchurl'] = url('webapp/home/switch', array('uniacid' => $account['uniacid']));
				break;
			case ACCOUNT_TYPE_PHONEAPP_NORMAL :
				$account['versions'] = phoneapp_get_some_lastversions($account['uniacid']);
				if (!empty($account['versions'])) {
					foreach ($account['versions'] as $version) {
						if (!empty($version['current'])) {
							$account['current_version'] = $version;
						}
					}
				}
				break;
		}
	}

//	echo "<pre>";
//	var_dump($table);


}

if ($do == 'rank' && $_W['isajax'] && $_W['ispost']) {
	$uniacid = intval($_GPC['uniacid']);

	if (!empty($uniacid)) {
		switch ($type) {
			case 'account' :
				$exist = pdo_get('uni_account', array('uniacid' => $uniacid));
				if (empty($exist)) {
					iajax(1, '公众号不存在', '');
				}
				break;
			case 'wxapp' :
				$wxapp_info = wxapp_fetch($uniacid);
				if (empty($wxapp_info)) {
					iajax(1, '小程序不存在', '');
				}
				break;
			case 'webapp' :
				break;
			case 'phoneapp' :
				break;
		}
	}
	uni_account_rank_top($uniacid);
	iajax(0, '更新成功！', '');
}

if ($do == 'switch') {
	$uniacid = intval($_GPC['uniacid']);
	$type = safe_gpc_string($_GPC['type']);
	if (!empty($uniacid)) {
		if ($type == 'wxapp' || $type == 'phoneapp') {
			if ($type == 'wxapp') {
				$info = wxapp_fetch($uniacid);
			} elseif ($type == 'phoneapp') {
				$info = phoneapp_fetch($uniacid);
			}

			if (!empty($info)) {
				$module_name = safe_gpc_string($_GPC['module']);
				$version_id = !empty($_GPC['version_id']) ? intval($_GPC['version_id']) : $info['version_id'];
				if (!empty($module_name) && !empty($version_id)) {
					$version_info = wxapp_version($version_id);
					$module_info = array();
					if (!empty($version_info['modules'])) {
						foreach ($version_info['modules'] as $key => $module_val) {
							if ($module_val['name'] == $module_name) {
								$module_info = $module_val;
							}
						}
					}
					if (empty($version_id) || empty($module_info)) {
						itoast('版本信息错误');
					}
					$url = url('home/welcome/ext/', array('m' => $module_name));
					if (!empty($module_info['account']['uniacid'])) {
						uni_account_switch($module_info['account']['uniacid'], $url);
					} else {
						$url .= '&version_id=' . $version_id;
						uni_account_switch($version_info['uniacid'], $url, WXAPP_TYPE_SIGN);
					}
				}

				if ($type == 'wxapp') {
					wxapp_update_last_use_version($uniacid, $version_id);
					uni_account_switch($uniacid, url('wxapp/version/home', array('version_id' => $version_id)), WXAPP_TYPE_SIGN);
				} elseif ($type == 'phoneapp') {
					phoneapp_update_last_use_version($uniacid, $version_id);
					uni_account_switch($uniacid, url('phoneapp/version/home', array('version_id' => $version_id)), PHONEAPP_TYPE_SIGN);
				}


				exit;
			} else {
				if ($type == 'wxapp') {
					itoast('小程序不存在', referer(), 'error');
				} elseif ($type == 'phoneapp') {
					itoast('APP不存在', referer(), 'error');
				}

			}
		}
	}
}





template('account/privileges');

