<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (in_array($action, array('permission', 'account-manage'))) {
	define('FRAME', 'account');
	checkaccount();
}
if (in_array($action, array('group'))) {
	define('FRAME', 'system');
}