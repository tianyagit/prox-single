<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (strexists($_W['siteurl'], 'c=profile&a=module&do=setting')) {
	itoast('', url('module/module-account/setting', array('m' => $_GPC['m'])), 'info');
}

define('FRAME', 'account');
checkaccount();