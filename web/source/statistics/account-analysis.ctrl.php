<?php
/**
 * app端访问统计
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

load()->model('statistics');

$dos = array('display', 'edit_setting');
$do = in_array($do, $dos) ? $do : 'display';

$statistics_setting = (array)uni_setting_load(array('statistics'), $_W['uniacid']);
$statistics_setting = $statistics_setting['statistics'];
if ($do == 'display') {

}
if ($do == 'edit_setting') {

}
template('statistics/account-analysis');