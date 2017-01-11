<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/6
 * Time: 16:28
 */
defined('IN_IA') or exit('Access Denied');
define('FRAME', 'adviertisement');
if ($do == 'display') {
	define('ACTIVE_FRAME_URL', url('adviertisement/content-provider/account_list'));
}

