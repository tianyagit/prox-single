<?php
/**
 * 公众平台oAuth选项
 * [WeEngine System] Copyright (c) 2013 WE7.CC 
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('oauth', 'save_oauth', 'credit', 'fans_sync', 'register', 'save_credit_setting', 'save_tactics_setting', 'uc_setting');
$do = in_array($do, $dos) ? $do : 'oauth';
uni_user_permission_check('mc_passport_oauth');
$_W['page']['title'] = '公众平台oAuth选项 - 会员中心';

if ($do == 'uc_setting') {
	uni_user_permission_check('mc_uc');
	$_W['page']['title'] = 'uc站点整合';
	$setting = uni_setting_load('uc');
	$uc = $setting['uc'];
	if(!is_array($uc)) {
		$uc = array();
	}

	if(checksubmit('submit')) {
		$rec = array();
		$uc['status'] = intval($_GPC['status']);

		if($uc['status'] == '1') {
			$connect = $_GPC['connect'];
			$uc['connect'] = trim($_GPC['connect']);
			$uc['title'] = empty($_GPC['title']) ? message('请填写正确的站点名称！', referer(), 'error') : trim($_GPC['title']);
			$uc['appid'] = empty($_GPC['appid']) ? message('请填写正确的应用id！', referer(), 'error') : intval($_GPC['appid']);
			$uc['key'] = empty($_GPC['key']) ? message('请填写与UCenter的通信密钥！', referer(), 'error') : trim($_GPC['key']);
			$uc['charset'] = empty($_GPC['charset']) ? message('请填写UCenter的字符集！', referer(), 'error') : trim($_GPC['charset']);
			if($connect == 'mysql') {
				$uc['dbhost'] = empty($_GPC['dbhost']) ? message('请填写UCenter数据库主机地址！', referer(), 'error') : trim($_GPC['dbhost']);
				$uc['dbuser'] = empty($_GPC['dbuser']) ? message('请填写UCenter数据库用户名！', referer(), 'error') : trim($_GPC['dbuser']);
				$uc['dbpw'] = empty($_GPC['dbpw']) ? message('请填写UCenter数据库密码！', referer(), 'error') : trim($_GPC['dbpw']);
				$uc['dbname'] = empty($_GPC['dbname']) ? message('请填写UCenter数据库名称！', referer(), 'error') : trim($_GPC['dbname']);
				$uc['dbcharset'] = empty($_GPC['dbcharset']) ? message('请填写UCenter数据库字符集！', referer(), 'error') : trim($_GPC['dbcharset']);
				$uc['dbtablepre'] = empty($_GPC['dbtablepre']) ? message('请填写UCenter数据表前缀！', referer(), 'error') : trim($_GPC['dbtablepre']);
				$uc['dbconnect'] = intval($_GPC['dbconnect']);
				$uc['api'] = trim($_GPC['api']);
				$uc['ip'] = trim($_GPC['ip']);
			} elseif($connect == 'http') {
				$uc['dbhost'] = trim($_GPC['dbhost']);
				$uc['dbuser'] = trim($_GPC['dbuser']);
				$uc['dbpw'] = trim($_GPC['dbpw']);
				$uc['dbname'] = trim($_GPC['dbname']);
				$uc['dbcharset'] = trim($_GPC['dbcharset']);
				$uc['dbtablepre'] = trim($_GPC['dbtablepre']);
				$uc['dbconnect'] = intval($_GPC['dbconnect']);
				$uc['api'] = empty($_GPC['api']) ? message('请填写UCenter 服务端的URL地址！', referer(), 'error') : trim($_GPC['api']);
				$uc['ip'] = empty($_GPC['ip']) ? message('请填写UCenter的IP！', referer(), 'error') : trim($_GPC['ip']);
			}
		}
		$uc = iserializer($uc);
		uni_setting_save('uc', $uc);
		message('设置UC参数成功！', referer(), 'success');
	}
}

if ($do == 'save_tactics_setting') {
	$setting = $_GPC['setting'];
	if (empty($setting)) {
		message(error(1));
	}
	uni_setting_save('creditbehaviors', $setting);
	message(error(0));
}

//获取所有的认证服务号
if ($do == 'save_oauth') {
	$type = $_GPC['type'];
	$account = trim($_GPC['account']);
	if ($type == 'oauth') {
		$host = $_GPC['host'];
		$host = rtrim($host,'/');
		if(!empty($host) && !preg_match('/^http(s)?:\/\//', $host)) {
			$host = $_W['sitescheme'].$host;
		}
		$data = array(
			'host' => $host,
			'account' => $account,
		);
		pdo_update('uni_settings', array('oauth' => iserializer($data)), array('uniacid' => $_W['uniacid']));
		cache_delete("unisetting:{$_W['uniacid']}");
	}
	if ($type == 'jsoauth') {
		pdo_update('uni_settings', array('jsauth_acid' => $account), array('uniacid' => $_W['uniacid']));
		cache_delete("unisetting:{$_W['uniacid']}");
	}
	message(error(0), '', 'ajax');
}

if ($do == 'save_credit_setting') {
	$credit_setting = $_GPC['credit_setting'];
	if (empty($credit_setting)) {
		message(error(1), '', 'ajax');
	}
	uni_setting_save('creditnames', $credit_setting);
	message(error(0));
}

if ($do == 'oauth') {
	$where = '';
	$params = array();
	if(empty($_W['isfounder'])) {
		$where = " WHERE `uniacid` IN (SELECT `uniacid` FROM " . tablename('uni_account_users') . " WHERE `uid`=:uid)";
		$params[':uid'] = $_W['uid'];
	}
	$sql = "SELECT * FROM " . tablename('uni_account') . $where;
	$user_have_accounts = pdo_fetchall($sql, $params);
	$oauth_accounts = array();
	$jsoauth_accounts = array();
	if(!empty($user_have_accounts)) {
		foreach($user_have_accounts as $uniaccount) {
			$accountlist = uni_accounts($uniaccount['uniacid']);
			if(!empty($accountlist)) {
				foreach($accountlist as $account) {
					if(!empty($account['key']) && !empty($account['secret'])) {
						if (in_array($account['level'], array(4))) {
							$oauth_accounts[$account['acid']] = $account['name'];
						}
						if (in_array($account['level'], array(3, 4))) {
							$jsoauth_accounts[$account['acid']] = $account['name'];
						}
					}
				}
			}
		}
	}
//获取已保存的oauth信息
	$oauth = pdo_fetchcolumn('SELECT `oauth` FROM '.tablename('uni_settings').' WHERE `uniacid` = :uniacid LIMIT 1',array(':uniacid' => $_W['uniacid']));
	$oauth = iunserializer($oauth) ? iunserializer($oauth) : array();
	$jsoauth = pdo_getcolumn('uni_settings', array('uniacid' => $_W['uniacid']), 'jsauth_acid');
}

if ($do == 'credit') {
	$_W['page']['title'] = '积分设置';
	$credit_setting = uni_setting_load('creditnames');
	$credit_setting = $credit_setting['creditnames'];

	$credit_tactics = uni_setting_load('creditbehaviors');
	$credit_tactics = $credit_tactics['creditbehaviors'];

	$enable_credit = array();
	if (!empty($credit_setting)) {
		foreach ($credit_setting as $key => $credit) {
			if ($credit['enabled'] == 1) {
				$enable_credit[] = $key;
			}
		}
		unset($credit);
	}
}

if ($do == 'fans_sync') {
	uni_user_permission_check('mc_passport_sync');
	$_W['page']['title'] = '更新粉丝信息 - 公众号选项';
	$operate = $_GPC['operate'];
	if ($operate == 'save_setting') {
		uni_setting_save('sync', intval($_GPC['setting']));
		message(error(0), '', 'ajax');
	}
	$setting = uni_setting($_W['uniacid'], array('sync'));
	$sync_setting = $setting['sync'];
}

if ($do == 'register') {
	$_W['page']['title'] = '注册设置';
	if (checksubmit('submit')) {
		$passport = $_GPC['passport'];
		if (!empty($passport)) {
			uni_setting_save('passport', $passport);
			message('设置成功', '', 'success');
		}
	}
	$setting = uni_setting_load('passport');
	$register_setting = $setting['passport'];
}

template('profile/passport');
