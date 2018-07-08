<?php
/**
 * xall
 * 域名绑定
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('display');
$do = in_array($do, $dos) ? $do : 'display';

$_W['page']['title'] = '伪静态';

if ($do == 'display') {
	if (!empty($_W['account']['setting']['default_module'])) {
		$eid = table('modules_bindings')->where('module', $_W['account']['setting']['default_module'])->getcolumn('eid');
	}
	template('webapp/rewrite');
}