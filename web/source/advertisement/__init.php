<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/6
 * Time: 16:28
 */
itoast('访问链接已失效！', referer(), 'error');
header('Location: ' . url('account/display'));
defined('IN_IA') or exit('Access Denied');
define('FRAME', 'advertisement');
if ($do == 'display') {
	define('ACTIVE_FRAME_URL', url('advertisement/content-provider/account_list'));
}

