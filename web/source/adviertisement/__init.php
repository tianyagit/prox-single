<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/6
 * Time: 16:28
 */
defined('IN_IA') or exit('Access Denied');
define('FRAME', 'adviertisement');
if ($do == 'content_provider') {
	define('ACTIVE_FRAME_URL', url('adviertisement/content_create'));
}
if ($action == 'content_create') {
	header('Location: '. url('adviertisement/content-provider/content_provider'));
}
