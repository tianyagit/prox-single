<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
if (in_array($action, array('app', 'setting', 'site', 'fans'))) {
	define('FRAME', 'account');
}
if (in_array($action, array('account'))) {
	define('FRAME', 'system');
}