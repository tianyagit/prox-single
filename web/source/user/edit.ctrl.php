<?php
/**
 * 编辑用户
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('user');


$do = $_GPC['do'];
$dos = array('edit_base', 'edit_modules_tpl', 'edit_account');
$do = in_array($do, $dos) ? $do: 'edit_base';
uni_user_permission_check('system_user_display');

$_W['page']['title'] = '编辑用户 - 用户管理';
$uid = intval($_GPC['uid']);
$user = user_single($uid);
if (empty($user)) {
	message('访问错误, 未找到该操作员.', url('user/display'), 'error');
}else {
	if($user['status'] == 1) message('访问错误，该用户未审核通过，请先审核通过再修改！', url('user/display/check_display'), 'error');
	if($user['status'] == 3) message('访问错误，该用户已被禁用，请先启用再修改！', url('user/display/recycle_display'), 'error');
}
$founders = explode(',', $_W['config']['setting']['founder']);
$profile = pdo_fetch('SELECT * FROM '.tablename('users_profile').' WHERE `uid` = :uid LIMIT 1',array(':uid' => $uid));
if(!empty($profile)) $profile['avatar'] = tomedia($profile['avatar']);

//编辑用户基础信息
if ($do == 'edit_base') {
	$user['last_visit'] = date('Y-m-d H:i:s', $user['lastvisit']);
	$user['end'] = $user['endtime'] == 0 ? '永久' : date('Y-m-d', $user['endtime']);
	$user['endtype'] = $user['endtime'] == 0 ? 1 : 2;
	if(!empty($profile)) {
		$profile['reside'] = array(
			'province' => $profile['resideprovince'],
			'city' => $profile['residecity'],
			'district' => $profile['residedist']
		);
		$profile['birth'] = array(
			'year' => $profile['birthyear'],
			'month' => $profile['birthmonth'],
			'day' => $profile['birthday'],
		);
		$profile['resides'] = $profile['resideprovince'] . $profile['residecity'] . $profile['residedist'] ;
		$profile['births'] = $profile['birthyear'] . '年' . $profile['birthmonth'] . '月' . $profile['birthday'] .'日' ;
	}
	template('user/edit-base');
}
if($do == 'edit_modules_tpl') {
	if($_W['isajax'] && $_W['ispost']) {
		if(intval($_GPC['groupid']) == $user['groupid']){
			message('0' , '', 'ajax');
		}
		if(!empty($_GPC['type']) && !empty($_GPC['groupid'])) {
			$data['uid'] = $uid;
			$data[$_GPC['type']] = intval($_GPC['groupid']);
			if(user_update($data)) {
				$group_info = group_detail_info($_GPC['groupid']);
				message($group_info, '', 'ajax');
			}else {
				message('1', '', 'ajax');
			}
		}else {
			message('-1', '', 'ajax');
		}
	}
	$groups = pdo_getall('users_group', array(), array('id', 'name'), 'id');
	$group_info = group_detail_info($user['groupid']);

	template('user/edit-modules-tpl');
}

if($do == 'edit_account') {
	$weids = pdo_fetchall("SELECT uniacid, role FROM ".tablename('uni_account_users')." WHERE uid = :uid", array(':uid' => $uid), 'uniacid');
	if (!empty($weids)) {
		$wechats = pdo_fetchall("SELECT w.name, w.level, w.acid, a.* FROM " . tablename('uni_account') . " a INNER JOIN " . tablename('account_wechats') . " w USING(uniacid) WHERE a.uniacid IN (".implode(',', array_keys($weids)).") ORDER BY a.uniacid ASC", array(), 'acid');
		foreach ($wechats as &$wechats_val) {
			$wechats_val['thumb'] = tomedia('headimg_'.$wechats_val['acid']. '.jpg').'?time='.time();
			foreach ($weids as $weids_key => $weids_val) {
				if($wechats_val['uniacid'] == $weids_key) {
					$wechats_val['role'] = $weids_val['role'];
				}
			}
		}
		unset($wechats_val);
	}
	template('user/edit-account');
}