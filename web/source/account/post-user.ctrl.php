<?php
/**
 * 管理公众号--使用者管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('system');

$dos = array('delete', 'edit', 'set_permission', 'add');
$do = in_array($do, $dos) ? $do : 'edit';
uni_user_permission_check('system_account');

$uniacid = intval($_GPC['uniacid']);
$acid = intval($_GPC['acid']);
$_W['page']['title'] = '管理设置 - 微信公众号管理';
if (empty($uniacid) || empty($acid)) {
	message('请选择要编辑的公众号', referer(), 'error');
}
$state = uni_permission($_W['uid'], $uniacid);
if($state != 'founder' && $state != 'manager') {
	message('没有该公众号操作权限！', referer(), 'error');
}
$headimgsrc = tomedia('headimg_'.$acid.'.jpg');
$account = account_fetch($acid);

if($do == 'edit') {
	$founders = explode(',', $_W['config']['setting']['founder']);
	$permissions = pdo_fetchall("SELECT id, uid, role FROM ".tablename('uni_account_users')." WHERE uniacid = '$uniacid' and role != :role  ORDER BY uid ASC, role DESC", array(':role' => 'clerk'), 'uid');
	$owner = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'role' => 'owner'), array('uid', 'id'));
	if (!empty($permissions)) {
		$member = pdo_fetchall("SELECT username, uid FROM ".tablename('users')." WHERE uid IN (".implode(',', array_keys($permissions)).")", array(), 'uid');
		if(!empty($member)) {
			foreach ($permissions as $key => $per_val) {
				$permissions[$key]['isfounder'] = in_array($member[$key]['uid'], $founders) ? 1 : 0;
				$permissions[$key]['username'] = $member[$key]['username'] ? $member[$key]['username'] : '';
			}
		}
	}
	$uids = array();
	foreach ($permissions as $v) {
		$uids[] = $v['uid'];
	}
}
if($do == 'delete') {
	$uid = is_array($_GPC['uid']) ? 0 : intval($_GPC['uid']);
	if(empty($uid)) {
		message('请选择要删除的用户！', referer(), 'error');
	}
	$data = array(
		'uniacid' => $uniacid,
		'uid' => $uid,
	);
	$exists = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'uid' => $uid));
	if(!empty($exists)) {
		$result = pdo_delete('uni_account_users', $data);
		if($result) {
			message('删除成功！', referer(), 'success');
		}else {
			message('删除失败，请重试！', referer(), 'error');
		}
	}else {
		message('该公众号下不存在该用户！', referer(), 'error');
	}
}
if($do == 'add' && $_W['isajax'] && $_W['ispost']) {
	$username = trim($_GPC['username']);
	if($username == 'admin') {
		message(error(1), '', 'ajax');
	}
	$user = user_single(array('username' => $username));
	if(!empty($user)) {
		//addtype为1：操作员；2：:管理员；3、主管理员
		$addtype = intval($_GPC['addtype']);
		$data = array(
			'uniacid' => $uniacid,
			'uid' => $user['uid'],
		);

		$exists = pdo_get('uni_account_users', array('uid' => $user['uid'], 'uniacid' => $uniacid));
		$owner = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'role' => 'owner'));
		if(empty($exists)) {
			if($addtype == 3) {
				if(empty($owner)) {
					$data['role'] = 'owner';
				}else {
					$result = pdo_update('uni_account_users', $data, array('id' => $owner['id']));
					if($result) {
						message(error(0), '', 'ajax');
					}else {
						message(error(1), '', 'ajax');
					}
					exit;
				}
			}elseif($addtype == 2) {
				$data['role'] = 'manager';
			}else {
				$data['role'] = 'operator';
			}
			$result = pdo_insert('uni_account_users', $data);
			if($result) {
				message(error(0), '', 'ajax');
			}else {
				message(error(1), '', 'ajax');
			}
		} else {
			//{$username} 已经是该公众号的操作员或管理员，请勿重复添加
			message(error(2), '', 'ajax');
		}
		exit('success');
	}else {
		message(error(-1), '', 'ajax');
	}
}
if($do == 'set_permission') {
	$uid = intval($_GPC['uid']);
	$user = user_single(array('uid' => $uid));
	if (empty($user)) {
		message('您操作的用户不存在或是已经被删除！');
	}
	
	if (!pdo_getcolumn('uni_account_users', array('uid' => $uid, 'uniacid' => $uniacid), 'id')) {
		message('此用户没有操作该统一公众号的权限，请选指派“管理者”权限！');
	}
	//获取系统权限
	$system_permission = pdo_get('users_permission', array('uniacid' => $uniacid, 'uid' => $uid, 'type' => 'system'));
	if(!empty($system_permission['permission'])) {
		$system_permission['permission'] = explode('|', $system_permission['permission']);
	} else {
		$system_permission['permission'] = array();
	}
	
	//获取模块权限
	$module_permission = pdo_getall('users_permission', array('uniacid' => $uniacid, 'uid' => $uid, 'type !=' => 'system'), array(), 'type');
	$module_permission_keys = array_keys($module_permission);

	if (checksubmit('submit')) {
		//系统权限
		$system_temp = array();
		if(!empty($_GPC['system'])) {
			foreach($_GPC['system'] as $li) {
				$li = trim($li);
				if(!empty($li)) {
					$system_temp[] = $li;
				}
			}
		}
		if(!empty($system_temp)) {
			if(empty($system_permission['id'])) {
				$insert = array(
					'uniacid' => $uniacid,
					'uid' => $uid,
					'type' => 'system',
				);
				$insert['permission'] = implode('|', $_GPC['system']);
				pdo_insert('users_permission', $insert);
			} else {
				$update = array(
					'permission' => implode('|', $_GPC['system'])
				);
				pdo_update('users_permission', $update, array('uniacid' => $uniacid, 'uid' => $uid));
			}
		} else {
			pdo_delete('users_permission', array('uniacid' => $uniacid, 'uid' => $uid));
		}
		pdo_query('DELETE FROM ' . tablename('users_permission') . ' WHERE uniacid = :uniacid AND uid = :uid AND type != :type', array(':uniacid' => $uniacid, ':uid' => $uid, ':type' => 'system'));
		//模块权限
		if(!empty($_GPC['module'])) {
			//print_r($_GPC);die;
			$arr = array();
			foreach($_GPC['module'] as $li) {
				$insert = array(
					'uniacid' => $uniacid,
					'uid' => $uid,
					'type' => $li,
				);
				if(empty($_GPC['module_'. $li]) || $_GPC[$li . '_select'] == 1) {
					$insert['permission'] = 'all';
					pdo_insert('users_permission', $insert);
					continue;
				} else {
					$data = array();
					foreach($_GPC['module_'. $li] as $v) {
						$data[] = $v;
					}
					if(!empty($data)) {
						$insert['permission'] = implode('|', $data);
						pdo_insert('users_permission', $insert);
					}
				}
			}
		}
		message('操作菜单权限成功！', referer(), 'success');
	}

	$menus = system_menu_permission_list();
	$_W['uniacid'] = $uniacid;
	$module = uni_modules();
	template('account/set-permission');
	exit;
}
template('account/manage-users');