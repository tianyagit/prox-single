<?php
/**
 * 公众号欢迎页，统计等信息
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('platform', 'ext');
$do = in_array($do, $dos) ? $do : 'platform';

if ($do == 'platform') {
	define('FRAME', 'account');
	
	template('home/welcome');
} elseif ($do == 'ext') {
	define('FRAME', 'module');
	
	template('home/welcome-ext');
}