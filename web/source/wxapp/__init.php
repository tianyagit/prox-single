<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
if (($action == 'version' && ($do == 'manage' || $do == 'module_link_uniacid')) || ($action == 'payment')) {
	define('FRAME', 'wxapp');
}

