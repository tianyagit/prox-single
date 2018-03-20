<?php

/**
 * 更新模板缓存
 * @return boolean
 */
function cache_build_template() {
	load()->func('file');
	rmdirs(IA_ROOT . '/data/tpl', true);
}

/**
 * 更新设置项缓存
 * @return mixed
 */
function cache_build_setting() {
	$setting = table('coresetting')->getSettingList();
	if (is_array($setting)) {
		foreach ($setting as $k => $v) {
			$setting[$v['key']] = iunserializer($v['value']);
		}
		cache_write("setting", $setting);
	}
}

/**
 * 更新盗版模块数据及缓存
 * @return mixed
 */
function cache_build_module_status() {
	load()->model('cloud');
	$cloud_modules = cloud_m_query();
	$module_ban = is_array($cloud_modules['pirate_apps']) ? $cloud_modules['pirate_apps'] : array();
	$local_module = setting_load('module_ban');
	$update_modules = array_merge(array_diff($local_module, $module_ban), array_diff($module_ban, $local_module));
	if (!empty($update_modules)) {
		foreach ($update_modules as $module) {
			cache_build_module_info($module);
		}
	}
	setting_save($module_ban, 'module_ban');
	setting_save(array(), 'module_upgrade');
}

/**
 * 重建公众号下可使用的模块
 * @param int $uniacid 要重建模块的公众号uniacid
 */
function cache_build_account_modules($uniacid = 0) {
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		//以前缀的形式删除缓存
		cache_clean(cache_system_key('unimodules'));
		cache_clean(cache_system_key('user_modules'));
	} else {
		cache_delete(cache_system_key("unimodules:{$uniacid}:1"));
		cache_delete(cache_system_key("unimodules:{$uniacid}:"));
		$owner_uid = table('account')->searchWithUniacid($uniacid)->searchWithRole('owner')->getOwnerUid();
		cache_delete(cache_system_key("user_modules:{$owner_uid}:"));
	}
}

/*
 * 重建公众号缓存
 * @param int $uniacid 要重建缓存的公众号uniacid
 */
function cache_build_account($uniacid = 0) {
	global $_W;
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		$uniacid_arr = table('account')->getUniAccountList();
		foreach($uniacid_arr as $account){
			cache_delete("uniaccount:{$account['uniacid']}");
			cache_delete("unisetting:{$account['uniacid']}");
			cache_delete("defaultgroupid:{$account['uniacid']}");
		}
	} else {
		cache_delete("uniaccount:{$uniacid}");
		cache_delete("unisetting:{$uniacid}");
		cache_delete("defaultgroupid:{$uniacid}");
	}

}

/**
 * 重建会员缓存
 * @param int uid 要重建缓存的会员uid
 */
function cache_build_memberinfo($uid) {
	$uid = intval($uid);
	$cachekey = cache_system_key(CACHE_KEY_MEMBER_INFO, $uid);
	cache_delete($cachekey);
	return true;
}

/**
 * 更新会员个人信息字段
 * @return array
 */
function cache_build_users_struct() {
	$base_fields = array(
		'uniacid' => '同一公众号id',
		'groupid' => '分组id',
		'credit1' => '积分',
		'credit2' => '余额',
		'credit3' => '预留积分类型3',
		'credit4' => '预留积分类型4',
		'credit5' => '预留积分类型5',
		'credit6' => '预留积分类型6',
		'createtime' => '加入时间',
		'mobile' => '手机号码',
		'email' => '电子邮箱',
		'realname' => '真实姓名',
		'nickname' => '昵称',
		'avatar' => '头像',
		'qq' => 'QQ号',
		'gender' => '性别',
		'birth' => '生日',
		'constellation' => '星座',
		'zodiac' => '生肖',
		'telephone' => '固定电话',
		'idcard' => '证件号码',
		'studentid' => '学号',
		'grade' => '班级',
		'address' => '地址',
		'zipcode' => '邮编',
		'nationality' => '国籍',
		'reside' => '居住地',
		'graduateschool' => '毕业学校',
		'company' => '公司',
		'education' => '学历',
		'occupation' => '职业',
		'position' => '职位',
		'revenue' => '年收入',
		'affectivestatus' => '情感状态',
		'lookingfor' => ' 交友目的',
		'bloodtype' => '血型',
		'height' => '身高',
		'weight' => '体重',
		'alipay' => '支付宝帐号',
		'msn' => 'MSN',
		'taobao' => '阿里旺旺',
		'site' => '主页',
		'bio' => '自我介绍',
		'interest' => '兴趣爱好',
		'password' => '密码',
		'pay_password' => '支付密码',
	);
	cache_write('userbasefields', $base_fields);
	$fields = table('profile')->getProfileFields();
	if (!empty($fields)) {
		foreach ($fields as &$field) {
			$field = $field['title'];
		}
		$fields['uniacid'] = '同一公众号id';
		$fields['groupid'] = '分组id';
		$fields['credit1'] ='积分';
		$fields['credit2'] = '余额';
		$fields['credit3'] = '预留积分类型3';
		$fields['credit4'] = '预留积分类型4';
		$fields['credit5'] = '预留积分类型5';
		$fields['credit6'] = '预留积分类型6';
		$fields['createtime'] = '加入时间';
		$fields['password'] = '用户密码';
		$fields['pay_password'] = '支付密码';
		cache_write('usersfields', $fields);
	} else {
		cache_write('usersfields', $base_fields);
	}
}

function cache_build_frame_menu() {
	global $_W;
	$table_name = table('menu');
	$system_menu_db = $table_name->getCoreMenuFillPermissionName();
	$system_menu = require IA_ROOT . '/web/common/frames.inc.php';
	if (!empty($system_menu) && is_array($system_menu)) {
		$system_displayoder = 1;
		foreach ($system_menu as $menu_name => $menu) {
			$system_menu[$menu_name]['is_system'] = true;
			$system_menu[$menu_name]['is_display'] = !empty($system_menu_db[$menu_name]['is_display']) ? true : ((isset($system_menu[$menu_name]['is_display']) && empty($system_menu[$menu_name]['is_display']) || !empty($system_menu_db[$menu_name])) ? false : true);
			$system_menu[$menu_name]['displayorder'] = !empty($system_menu_db[$menu_name]) ? intval($system_menu_db[$menu_name]['displayorder']) : ++$system_displayoder;
			foreach ($menu['section'] as $section_name => $section) {
				$displayorder = max(count($section['menu']), 1);

				//查询此节点下新增的菜单
				if (empty($section['menu'])) {
					$section['menu'] = array();
				}
				$table_name->searchWithGroupName($section_name);
				$table_name->coreMenuOrderByDisplayorder('DESC');
				$add_menu = $table_name->getCoreMenuList();
				if (!empty($add_menu)) {
					foreach ($add_menu as $permission_name => $menu) {
						$menu['icon'] = !empty($menu['icon']) ? $menu['icon'] : 'wi wi-appsetting';
						$section['menu'][$permission_name] = $menu;
					}
				}
				$section_hidden_menu_count = 0;
				foreach ($section['menu']  as $permission_name => $sub_menu) {
					$sub_menu_db = $system_menu_db[$sub_menu['permission_name']];
					$system_menu[$menu_name]['section'][$section_name]['menu'][$permission_name] = array(
						'is_system' => isset($sub_menu['is_system']) ? $sub_menu['is_system'] : 1,
						'is_display' => isset($sub_menu['is_display']) && empty($sub_menu['is_display']) ? 0 : (isset($sub_menu_db['is_display']) ? $sub_menu_db['is_display'] : 1),
						'title' => !empty($sub_menu_db['title']) ? $sub_menu_db['title'] : $sub_menu['title'],
						'url' => $sub_menu['url'],
						'permission_name' => $sub_menu['permission_name'],
						'icon' => $sub_menu['icon'],
						'displayorder' => !empty($sub_menu_db['displayorder']) ? $sub_menu_db['displayorder'] : $displayorder,
						'id' => $sub_menu['id'],
						'sub_permission' => $sub_menu['sub_permission'],
					);
					$displayorder--;
					$displayorder = max($displayorder, 0);
					if (empty($system_menu[$menu_name]['section'][$section_name]['menu'][$permission_name]['is_display'])) {
						$section_hidden_menu_count++;
					}
				}
				if (empty($section['is_display']) && $section_hidden_menu_count == count($section['menu']) && $section_name != 'platform_module') {
					$system_menu[$menu_name]['section'][$section_name]['is_display'] = 0;
				}
				$system_menu[$menu_name]['section'][$section_name]['menu'] = iarray_sort($system_menu[$menu_name]['section'][$section_name]['menu'], 'displayorder', 'desc');
			}
		}
		$add_top_nav = $table_name->searchWithGroupName('frame')->getTopMenu();
		if (!empty($add_top_nav)) {
			foreach ($add_top_nav as $menu) {
				$menu['url'] = strexists($menu['url'], 'http') ?  $menu['url'] : $_W['siteroot'] . $menu['url'];
				$menu['blank'] = true;
				$menu['is_display'] = true;
				$system_menu[$menu['permission_name']] = $menu;
			}
		}
		$system_menu = iarray_sort($system_menu, 'displayorder', 'asc');
		cache_delete('system_frame');
		cache_write('system_frame', $system_menu);
		return $system_menu;
	}
}

function cache_build_module_subscribe_type() {
	global $_W;
	$modules = table('module')->getSubscribesModules();
	$subscribe = array();
	if (!empty($modules)) {
		foreach ($modules as $module) {
			$module['subscribes'] = unserialize($module['subscribes']);
			if (!empty($module['subscribes'])) {
				foreach ($module['subscribes'] as $event) {
					if ($event == 'text') {
						continue;
					}
					$subscribe[$event][] = $module['name'];
				}
			}
		}
	}

	$module_ban = $_W['setting']['module_receive_ban'];
	foreach ($subscribe as $event => $module_group) {
		if (!empty($module_group)) {
			foreach ($module_group as $index => $module) {
				if (!empty($module_ban[$module])) {
					unset($subscribe[$event][$index]);
				}
			}
		}
	}
	cache_write('module_receive_enable', $subscribe);
	return $subscribe;
}


/*更新流量主缓存*/
function cache_build_cloud_ad() {
	global $_W;
	$uniacid_arr = table('account')->getUniAccountList();
	foreach($uniacid_arr as $account){
		cache_delete("stat:todaylock:{$account['uniacid']}");
		cache_delete("cloud:ad:uniaccount:{$account['uniacid']}");
		cache_delete("cloud:ad:app:list:{$account['uniacid']}");
	}
	cache_delete("cloud:flow:master");
	cache_delete("cloud:ad:uniaccount:list");
	cache_delete("cloud:ad:tags");
	cache_delete("cloud:ad:type:list");
	cache_delete("cloud:ad:app:support:list");
	cache_delete("cloud:ad:site:finance");
}

/**
 * 更新未安装模块列表
 */
function cache_build_uninstalled_module() {
	load()->model('cloud');
	load()->classs('cloudapi');
	load()->model('extension');
	load()->func('file');
	$cloud_api = new CloudApi();
	$module_table = table('module');
	$all_modules = $module_table->getModulesList();
	$cloud_m_count = $cloud_api->get('site', 'stat', array('module_quantity' => 1), 'json');
	$sql = 'SELECT * FROM '. tablename('modules') . " as a LEFT JOIN" . tablename('modules_recycle') . " as b ON a.name = b.modulename WHERE b.modulename is NULL";
	$installed_module = pdo_fetchall($sql, array(), 'name');
	if (!empty($installed_module) && is_array($installed_module)) {
		foreach ($installed_module as &$value) {
			$value['phoneapp_support'] = !empty($value['phoneapp_support']) ? $value['phoneapp_support'] : 1;
		}
		unset($value);
	}
	$local_module_list = $module_table->getModulesLocalList();
	$uninstallModules = array('recycle' => array(), 'uninstalled' => array());
	$recycle_modules = $cloud_api->post('cache', 'get', array('key' => cache_system_key('recycle_module:')));
	$recycle_modules = !empty($recycle_modules['data']) ? $recycle_modules['data'] : array();
	if (empty($recycle_modules)) {
		$recycle_modules = pdo_getall('modules_recycle', array(), array('modulename'), 'modulename');
		$cloud_api->post('cache', 'set', array('key' => cache_system_key('recycle_module:'), 'value' => $recycle_modules));
	}
	$cloud_module = cloud_m_query();
	unset($cloud_module['pirate_apps']);
	if (!empty($cloud_module) && !is_error($cloud_module)) {
		foreach ($cloud_module as $module) {
			$upgrade_support_module = false;
			$wxapp_support = !empty($module['site_branch']['wxapp_support']) && is_array($module['site_branch']['bought']) && in_array('wxapp', $module['site_branch']['bought']) ? $module['site_branch']['wxapp_support'] : 1;
			$app_support = !empty($module['site_branch']['app_support']) && is_array($module['site_branch']['bought']) && in_array('app', $module['site_branch']['bought']) ? $module['site_branch']['app_support'] : 1;
			$webapp_support = !empty($module['site_branch']['webapp_support']) && is_array($module['site_branch']['bought']) && in_array('webapp', $module['site_branch']['bought']) ? $module['site_branch']['webapp_support'] : MODULE_NOSUPPORT_WEBAPP;
			$welcome_support = !empty($module['site_branch']['system_welcome_support']) && is_array($module['site_branch']['bought']) && in_array('system_welcome', $module['site_branch']['bought']) ? $module['site_branch']['system_welcome_support'] : MODULE_NONSUPPORT_SYSTEMWELCOME;
			$phoneapp_support = !empty($module['site_branch']['phoneapp_support']) && is_array($module['site_branch']['bought']) && in_array('phoneapp', $module['site_branch']['bought']) ? $module['site_branch']['phoneapp_support'] : MODULE_NOSUPPORT_PHONEAPP;
			if ($wxapp_support ==  MODULE_NONSUPPORT_WXAPP && $app_support == MODULE_NONSUPPORT_ACCOUNT && $webapp_support == MODULE_NOSUPPORT_WEBAPP && $welcome_support == MODULE_NONSUPPORT_SYSTEMWELCOME && $phoneapp_support == MODULE_NOSUPPORT_PHONEAPP) {
				$app_support = MODULE_SUPPORT_ACCOUNT;
			}
			if (!empty($installed_module[$module['name']]) && ($installed_module[$module['name']]['app_support'] != $app_support || $installed_module[$module['name']]['wxapp_support'] != $wxapp_support || $installed_module[$module['name']]['webapp_support'] != $webapp_support || $installed_module[$module['name']]['welcome_support'] != $welcome_support || $installed_module[$module['name']]['phoneapp_support'] != $phoneapp_support)) {
				$upgrade_support_module = true;
			}
			$module_info = $installed_module[$module['name']];
			if (!empty($module_info)) {
				$site_branch = $module['site_branch']['id'];
				$site_branch = !empty($site_branch) ? $site_branch : $module['branch'];
				$cloud_branch_version = $module['branches'][$site_branch]['version'];
				$upgrade_branch = false;
				$is_upgrade = false;
				$has_new_branch = false;
				if (!empty($module['branches'])) {
					$best_branch_id = 0;
					foreach ($module['branches'] as $branch) {
						if (empty($branch['status']) || empty($branch['show'])) {
							continue;
						}
						if ($best_branch_id == 0) {
							$best_branch_id = $branch['id'];
						} else {
							if ($branch['displayorder'] > $module['branches'][$best_branch_id]['displayorder']) {
								$best_branch_id = $branch['id'];
							}
						}
					}
				} else {
					$is_upgrade = false;
					continue;
				}
				$best_branch = $module['branches'][$best_branch_id];
				if (($module['displayorder'] < $best_branch['displayorder'] && !empty($module['version'])) || (!empty($module_info['site_branch_id']) && $cloud_m_info['site_branch']['id'] > $module_info['site_branch_id'])){
					$has_new_branch = true;
				} else {
					$has_new_branch = false;
				}
				if (version_compare($module_info['version'], $cloud_branch_version) == -1) {
					$is_upgrade = true;
				} else {
					$is_upgrade = false;
				}
			}
			$module_local = array(
				'mid' => $module_info['mid'],
				'name' => $module['name'],
				'title' => $module['title'],
				'version' => $module['version'],
				'thumb' => $module['thumb'],
				'main_module' => $module['main_module'],
				'has_new_branch' => $has_new_branch,
				'is_upgrade' => $is_upgrade,
				'wxapp_support' => $wxapp_support,
				'app_support' => $app_support,
				'webapp_support' => $webapp_support,
				'phoneapp_support' => $phoneapp_support,
				'welcome_support' => $welcome_support,
				'upgrade_support' => $upgrade_support_module,
				'upgrade_branch' => $is_upgrade,
				'from' => 'cloud'
			);

			if (in_array($module['name'], array_keys($installed_module))) {
				$module_local['status'] = 'installed';
			}
			if (!in_array($module['name'], array_keys($installed_module)) || $upgrade_support_module) {
				if (!empty($recycle_modules[$module['name']])) {
					$status = 'recycle';
				}
				if (empty($all_modules[$module['name']]['mid'])) {
					$status = 'uninstalled';
				}
				if (!empty($module['id'])) {
					$module_local['status'] = $status;
				}
			}
			if (empty($local_module_list[$module['name']])) {
				$module_local['name'] = $module['name'];
				pdo_insert('modules_local', $module_local);
			} else {
				pdo_update('modules_local', $module_local, array('name' => $module['name']));
			}
		}
	}
	$path = IA_ROOT . '/addons/';
	mkdirs($path);
	$module_file = glob($path . '*');
	if (is_array($module_file) && !empty($module_file)) {
		foreach ($module_file as $modulepath) {
			if (!is_dir($modulepath)) {
				continue;
			}
			$upgrade_support_module = false;
			$modulepath = str_replace($path, '', $modulepath);
			$manifest = ext_module_manifest($modulepath);
			if (!is_array($manifest) || empty($manifest) || empty($manifest['application']['identifie'])) {
				continue;
			}
			$main_module = empty($manifest['platform']['main_module']) ? '' : $manifest['platform']['main_module'];
			$manifest = ext_module_convert($manifest);
			if (!empty($installed_module[$modulepath]) && ($manifest['app_support'] != $installed_module[$modulepath]['app_support'] || $manifest['wxapp_support'] != $installed_module[$modulepath]['wxapp_support'] || $manifest['welcome_support'] != $installed_module[$modulepath]['welcome_support'] || $manifest['phoneapp_support'] != $installed_module[$modulepath]['phoneapp_support'])) {
				$upgrade_support_module = true;
			}
			$module_local = array(
				'mid' => $installed_module[$manifest['name']]['mid'],
				'name' => $manifest['name'],
				'title' => $manifest['title'],
				'version' => $manifest['version'],
				'thumb' => $manifest['thumb'],
				'main_module' => $manifest['main_module'],
				'has_new_branch' => false,
				'is_upgrade' => false,
				'wxapp_support' => $manifest['wxapp_support'],
				'app_support' => $manifest['app_support'],
				'webapp_support' => $manifest['webapp_support'],
				'phoneapp_support' => $manifest['phoneapp_support'],
				'welcome_support' => $manifest['welcome_support'],
				'upgrade_support' => $upgrade_support_module,
				'upgrade_branch' => false,
				'from' => 'local'
			);
			if (in_array($manifest['name'], array_keys($installed_module))) {
				$module_local['status'] = 'installed';
			}
			if (!in_array($manifest['name'], array_keys($installed_module)) || $upgrade_support_module) {
				if (!empty($recycle_modules[$manifest['name']])) {
					$status = 'recycle';
				}
				if (empty($all_modules[$manifest['name']]['mid'])) {
					$status = 'uninstalled';
				}
				if (!empty($manifest['id'])) {
					$module_local['status'] = $status;
				}
			}
			if (empty($local_module_list[$manifest['name']])) {
				$module_local['name'] = $manifest['name'];
				pdo_insert('modules_local', $module_local);
			} else {
				pdo_update('modules_local', $module_local, array('name' => $manifest['name']));
			}
		}
	}
	return true;
}

/**
 * 构造可以借用支付和服务商支付的公众号的缓存
 */
function cache_build_proxy_wechatpay_account() {
	global $_W;
	load()->model('account');
	$account_table = table('account');
	$uniaccounts = $account_table->userOwnedAccount($_W['uid']);
	$service = array();
	$borrow = array();
	if (!empty($uniaccounts)) {
		foreach ($uniaccounts as $uniaccount) {
			$account = account_fetch($uniaccount['default_acid']);
			$account_setting = $account_table->searchWithUniacid($account['uniacid'])->getUniSetting();
			$payment = iunserializer($account_setting['payment']);
			if (is_array($account) && !empty($account['key']) && !empty($account['secret']) && in_array($account['level'], array (4)) &&
				is_array($payment) && !empty($payment) && intval($payment['wechat']['switch']) == 1) {

				if ((!is_bool ($payment['wechat']['switch']) && $payment['wechat']['switch'] != 4) || (is_bool ($payment['wechat']['switch']) && !empty($payment['wechat']['switch']))) {
					$borrow[$account['uniacid']] = $account['name'];
				}
			}
			if (!empty($payment['wechat_facilitator']['switch'])) {
				$service[$account['uniacid']] = $account['name'];
			}
		}
	}
	$cache = array(
		'service' => $service,
		'borrow' => $borrow
	);
	cache_write(cache_system_key("proxy_wechatpay_account:"), $cache);
	return $cache;
}

/**
 * 更新模块信息
 */
function cache_build_module_info($module_name) {
	global $_W;
	cache_delete(cache_system_key(CACHE_KEY_MODULE_INFO, $module_name));
	cache_delete(cache_system_key(CACHE_KEY_MODULE_SETTING, $_W['uniacid'], $module_name));
}

/**
 * 更新功能权限组
 */
function cache_build_uni_group() {
	cache_delete(cache_system_key(CACHE_KEY_UNI_GROUP));
}

/**
 * 构建所有已购买安装并有更新的模块的缓存
 */
function cache_build_cloud_upgrade_module() {
	load()->model('cloud');
	load()->model('extension');
	$module_list = table('module')->getModulesList();
	$cloud_module = cloud_m_query();
	$modules = array();
	if (is_array($module_list) && !empty($module_list)) {
		foreach ($module_list as $module) {
			if (in_array($module['name'], array_keys($cloud_module))) {
				$cloud_m_info = $cloud_module[$module['name']];
				$module['site_branch'] = $cloud_m_info['site_branch']['id'];
				if (empty($module['site_branch'])) {
					$module['site_branch'] = $cloud_m_info['branch'];
				}
				$cloud_branch_version = $cloud_m_info['branches'][$module['site_branch']]['version'];
				if (!empty($cloud_m_info['branches'])) {
					$best_branch_id = 0;
					foreach ($cloud_m_info['branches'] as $branch) {
						if (empty($branch['status']) || empty($branch['show'])) {
							continue;
						}
						if ($best_branch_id == 0) {
							$best_branch_id = $branch['id'];
						} else {
							if ($branch['displayorder'] > $cloud_m_info['branches'][$best_branch_id]['displayorder']) {
								$best_branch_id = $branch['id'];
							}
						}
					}
				} else {
					continue;
				}
				$module['branches'] = $cloud_m_info['branches'];
				$best_branch = $cloud_m_info['branches'][$best_branch_id];
				$module['from'] = 'cloud';
				if (version_compare($module['version'], $cloud_branch_version) == -1) {
					$module['upgrade_branch'] = true;
					$module['upgrade'] = true;
				}
				if ($cloud_m_info['displayorder'] < $best_branch['displayorder']) {
					$module['new_branch'] = true;
					$module['upgrade'] = true;
				}
				if ($module['upgrade']) {
					$modules[$module['name']] = $module;
				}
			}
		}
	} else {
		return array();
	}
	cache_write(cache_system_key('all_cloud_upgrade_module:'), $modules, 1800);
	return $modules;
}
