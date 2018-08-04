<?php
/**
 * 公众号列表
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

load()->model('wxapp');
load()->model('miniprogram');
load()->model('phoneapp');

$dos = array('rank', 'display', 'switch', 'platform');
$do = in_array($_GPC['do'], $dos) ? $do : 'display';
$_W['page']['title'] = '所有权限';
$account_info = permission_user_account_num($_W['uid']);

if ($do == 'platform') {
	$cache_last_account_type = cache_load(cache_system_key('last_account_type'));
	if (empty($cache_last_account_type)) {
		itoast('', url('account/display'));
	}
	$last_uniacid = uni_account_last_switch();
	$url = url('account/display', array('type' => $cache_last_account_type));
	if (empty($last_uniacid)) {
		itoast('', $url, 'info');
	}
	if (!empty($last_uniacid) && $last_uniacid != $_W['uniacid']) {
		uni_account_switch($last_uniacid, '', $cache_last_account_type);
	}
	$permission = permission_account_user_role($_W['uid'], $last_uniacid);
	if (empty($permission)) {
		itoast('', $url, 'info');
	}
	if ($cache_last_account_type == ACCOUNT_TYPE_SIGN) {
		$url = url('home/welcome');
	} elseif ($cache_last_account_type == WXAPP_TYPE_SIGN) {
		$last_version = wxapp_fetch($last_uniacid);
		if (!empty($last_version)) {
			$url = url('wxapp/version/home', array('version_id' => $last_version['version']['id']));
		}
	} elseif ($cache_last_account_type == WEBAPP_TYPE_SIGN) {
		$cache_key = cache_system_key('last_account', array('switch' => $_GPC['__switch']));
		$cache_lastaccount = cache_load($cache_key);
		$webapp_info = table('account')->getUniAccountByUniacid($cache_lastaccount[WEBAPP_TYPE_SIGN]);
		if (!empty($webapp_info)) {
			$url = url('webapp/home/display');
		} else {
			$url = url('account/display');
		}
	} elseif ($cache_last_account_type == PHONEAPP_TYPE_SIGN) {
		$last_version = phoneapp_fetch($last_uniacid);
		if (!empty($last_version)) {
			$url = url('phoneapp/version/home', array('version_id' => $last_version['version']['id']));
		}
	} elseif ($cache_last_account_type == XZAPP_TYPE_SIGN) {
		$url = url('xzapp/home/display');
	} elseif ($cache_last_account_type == ALIAPP_TYPE_SIGN) {
		$last_version = miniprogram_fetch($last_uniacid);
		if (!empty($last_version)) {
			$url = url('miniprogram/version/home', array('version_id' => $last_version['version']['id']));
		}
	}
	itoast('', $url);
}

if ($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;

	$type = safe_gpc_string($_GPC['type']);
	$title = safe_gpc_string($_GPC['title']);
	$type = in_array($type, array('all', ACCOUNT_TYPE_SIGN, WXAPP_TYPE_SIGN, WEBAPP_TYPE_SIGN, PHONEAPP_TYPE_SIGN, XZAPP_TYPE_SIGN, ALIAPP_TYPE_SIGN)) ? $type : 'all';

	if ($type == 'all') {
		$title = ' 公众号/微信小程序/PC/APP/熊掌号/支付宝小程序 ';
	}

	if ($type == 'all') {
		$tableName = ACCOUNT_TYPE_SIGN;
		$condition = array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH, ACCOUNT_TYPE_APP_NORMAL, ACCOUNT_TYPE_APP_AUTH, ACCOUNT_TYPE_WEBAPP_NORMAL, ACCOUNT_TYPE_PHONEAPP_NORMAL, ACCOUNT_TYPE_XZAPP_NORMAL, ACCOUNT_TYPE_ALIAPP_NORMAL);
		$fields = 'a.uniacid,b.type';
	} elseif ($type == ACCOUNT_TYPE_SIGN) {
		$tableName = ACCOUNT_TYPE_SIGN;
		$condition = array(ACCOUNT_TYPE_OFFCIAL_NORMAL, ACCOUNT_TYPE_OFFCIAL_AUTH);
	} elseif ($type == WXAPP_TYPE_SIGN) {
		$tableName = WXAPP_TYPE_SIGN;
		$condition = array(ACCOUNT_TYPE_APP_NORMAL, ACCOUNT_TYPE_APP_AUTH);
	} elseif ($type == WEBAPP_TYPE_SIGN) {
		$tableName = WEBAPP_TYPE_SIGN;
		$condition = array(ACCOUNT_TYPE_WEBAPP_NORMAL);
	} elseif ($type == PHONEAPP_TYPE_SIGN) {
		$tableName = PHONEAPP_TYPE_SIGN;
		$condition = array(ACCOUNT_TYPE_PHONEAPP_NORMAL);
	} elseif ($type == XZAPP_TYPE_SIGN) {
		$tableName = 'account_' . XZAPP_TYPE_SIGN;
		$condition = array(ACCOUNT_TYPE_XZAPP_NORMAL);
	} elseif ($type == ALIAPP_TYPE_SIGN) {
		$tableName = 'account_' . ALIAPP_TYPE_SIGN;
		$condition = array(ACCOUNT_TYPE_ALIAPP_NORMAL);
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
			case ACCOUNT_TYPE_XZAPP_NORMAL:
			case ACCOUNT_TYPE_OFFCIAL_NORMAL :
			case ACCOUNT_TYPE_OFFCIAL_AUTH :
				$account['role'] = permission_account_user_role($_W['uid'], $account['uniacid']);
				break;
			case ACCOUNT_TYPE_APP_NORMAL :
			case ACCOUNT_TYPE_APP_AUTH :
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
				$account['switchurl'] = url('account/display/switch', array('uniacid' => $account['uniacid']));
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
			case ACCOUNT_TYPE_ALIAPP_NORMAL :
				$account['versions'] = miniprogram_get_some_lastversions($account['uniacid']);
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
	template('account/display');
}

if ($do == 'rank' && $_W['isajax'] && $_W['ispost']) {
	$uniacid = intval($_GPC['uniacid']);
	$type = intval($_GPC['type']);
	if (!empty($uniacid)) {
		$exist = uni_fetch($uniacid);
		if (!$exist) {
			iajax(1, '账号信息不存在', '');
		}
	}
	uni_account_rank_top($uniacid);
	iajax(0, '更新成功！', '');
}

if ($do == 'switch') {
	$uniacid = intval($_GPC['uniacid']);
	if (!empty($uniacid)) {
		$account_info = uni_fetch($uniacid);
		$type = $account_info['type'];
		if ($type == ACCOUNT_TYPE_OFFCIAL_NORMAL || $type == ACCOUNT_TYPE_OFFCIAL_AUTH || $type == ACCOUNT_TYPE_XZAPP_NORMAL) {
			$role = permission_account_user_role($_W['uid'], $uniacid);
			if(empty($role)) {
				itoast('操作失败, 非法访问.', '', 'error');
			}
			if (empty($_W['isfounder'])) {
				$account_endtime = uni_fetch($uniacid);
				$account_endtime = $account_endtime['endtime'];
				if ($account_endtime > 0 && TIMESTAMP > $account_endtime) {
					itoast('公众号已到期。', '', 'error');
				}
			}
			uni_account_save_switch($uniacid);
			$module_name = trim($_GPC['module_name']);
			$version_id = intval($_GPC['version_id']);
			if (empty($module_name)) {
				$url = url('home/welcome', array('account_type' => $type));
			} else {
				$url = url('home/welcome/ext', array('m' => $module_name, 'version_id' => $version_id, 'account_type' => $type));
			}
			uni_account_switch($uniacid, $url);
		}

		if ($type == ACCOUNT_TYPE_WEBAPP_NORMAL) {
			uni_account_save_switch($uniacid, WEBAPP_TYPE_SIGN);
			itoast('', url('webapp/home/display'));
		}

		if (in_array($type, array(ACCOUNT_TYPE_APP_NORMAL, ACCOUNT_TYPE_APP_AUTH, ACCOUNT_TYPE_PHONEAPP_NORMAL, ACCOUNT_TYPE_ALIAPP_NORMAL))) {
			if (!empty($account_info)) {
				$module_name = safe_gpc_string($_GPC['module']);
				if (!empty($_GPC['version_id'])) {
					$version_id = intval($_GPC['version_id']);
				} else {
					if ($type == ACCOUNT_TYPE_PHONEAPP_NORMAL) {
						$versions = phoneapp_get_some_lastversions($uniacid);
					} else {
						$versions = miniprogram_get_some_lastversions($uniacid);
					}
					foreach ($versions as $val) {
						if ($val['current']) {
							$version_id = $val['id'];
						}
					}
				}

				if (!empty($module_name) && !empty($version_id)) {
					if ($type == ACCOUNT_TYPE_PHONEAPP_NORMAL) {
						$version_info = phoneapp_version($version_id);
					} else {
						$version_info = pdo_get('wxapp_versions', array('id' => $version_id));
						$version_info['modules'] = iunserializer($version_info['modules']);
					}
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
						if ($type == ACCOUNT_TYPE_APP_NORMAL || $type == ACCOUNT_TYPE_APP_AUTH) {
							uni_account_switch($version_info['uniacid'], $url, WXAPP_TYPE_SIGN);
						} elseif ($type == ACCOUNT_TYPE_PHONEAPP_NORMAL) {
							uni_account_switch($version_info['uniacid'], $url, $account_info['type_sign']);
						}
					}
				}
				if (in_array($type, array(ACCOUNT_TYPE_APP_NORMAL, ACCOUNT_TYPE_APP_AUTH, ACCOUNT_TYPE_ALIAPP_NORMAL))) {
					miniprogram_update_last_use_version($uniacid, $version_id);
					uni_account_switch($uniacid, url('miniprogram/version/home', array('version_id' => $version_id)), $account_info['type_sign']);
				} elseif ($type == ACCOUNT_TYPE_PHONEAPP_NORMAL) {
					phoneapp_update_last_use_version($uniacid, $version_id);
					uni_account_switch($uniacid, url('phoneapp/version/home', array('version_id' => $version_id)), $account_info['type_sign']);
				}
			} else {
				itoast('账号不存在', referer(), 'error');
			}
		}

		if ($type == ACCOUNT_TYPE_XZAPP_NORMAL) {
			uni_account_save_switch($uniacid, XZAPP_TYPE_SIGN);
			itoast('', url('xzapp/home/display'));
		}
	}
}