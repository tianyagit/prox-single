<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/10
 * Time: 10:42
 * 通信参数配置
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('mail', 'sms', 'wechat');
$do = in_array($do, $dos) ? $do : 'mail';

uni_user_permission_check('profile_notify');
if ($do == 'mail') {
	$_W['page']['title'] = '邮件通知 - 通知参数 - 通知中心';

}