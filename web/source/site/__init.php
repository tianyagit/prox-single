<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($action != 'entry' && empty($_GPC['account_type'])) {
	checkaccount();
}

if ($action == 'editor' && !empty($_GPC['account_type'])) {
	checkwebapp();
}

if ($action == 'editor' && !empty($_GPC['account_type'])) {
	define('FRAME', 'webapp');
}

if (!($action == 'multi' && $do == 'post')) {
	define('FRAME', 'account');
}