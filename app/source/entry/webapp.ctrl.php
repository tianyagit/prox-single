<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');
$entry['module'] = 'we7_diyspecial';
$site = WeUtility::createModuleWebapp($entry['module']);
if(!is_error($site)) {
	$method = 'doPage' . ucfirst($entry['do']);
	exit($site->$method());
}
exit();