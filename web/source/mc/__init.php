<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

$check_manange = WeAccount::create($_W['account'])->checkIntoManage();

if (is_error($check_manange)) {
	itoast('', $check_manange['url']);
} else {
	define('FRAME', $check_manange['frame']);
}