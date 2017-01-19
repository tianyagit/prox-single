<?php
/**
 * Date: 2017/1/18
 * 模板管理
 */

defined('IN_IA') or exit('Access Denied');

load()->model('extension');
load()->model('cloud');

$dos = array();
$do = in_array($do, $dos) ? $do : '';
template('system/template');