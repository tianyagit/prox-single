<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/18
 * Time: 11:35
 */

defined('IN_IA') or exit('Access Denied');

$dos = array('oauth', 'save_oauth');
$do = in_array($do, $dos) ? $do : 'oauth';

uni_user_permission_check('mc_passport_oauth');
//获取所有的认证服务号
$_W['page']['title'] = '公众平台oAuth选项 - 会员中心';

if ($do == 'save_oauth') {
	$type = $_GPC['__input']['type'];
	if ($type == 'oauth') {
		$host = $_GPC['__input']['host'];
		$account = $_GPC['__input']['account'];
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

	}
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
							$oauth_accounts[] = array(
								'acid' => $account['acid'],
								'name' => $account['name']
							);
						}
						if (in_array($account['level'], array(3, 4))) {
							$jsoauth_accounts[] = array(
								'acid' => $account['acid'],
								'name' => $account['name']
							);
						}
					}
				}
			}
		}
	}
//获取已保存的oauth信息
	$oauth = pdo_fetchcolumn('SELECT `oauth` FROM '.tablename('uni_settings').' WHERE `uniacid` = :uniacid LIMIT 1',array(':uniacid' => $_W['uniacid']));
	$oauth = iunserializer($oauth) ? iunserializer($oauth) : array();
	template('profile/passport');
}