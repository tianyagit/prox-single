<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if ($action != 'webapp' && $do != 'list') {
	define('FRAME', 'webapp');
}
