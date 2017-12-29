<?php
/**
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');

$site = WeUtility::createModuleWebapp($entry['module']);
$method = 'doPage' . ucfirst($entry['do']);
if(!is_error($site) && method_exists($site, $method)) {
	exit($site->$method());
}
message('模块不存在或是 '.$method.' 方法不存在', '', 'error');