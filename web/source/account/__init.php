<?php
/**
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
if($action != 'display') {
	define('FRAME', 'system');
}
if ($controller == 'account' && $action == 'manage') {
	if ($_GPC['type'] == 'wxapp') {
		define('ACTIVE_FRAME_URL', url('account/manage/display', array('type' => 'wxapp')));
	} 
}