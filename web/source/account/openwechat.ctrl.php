<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/30
 * Time: 14:01
 */
defined('IN_IA') or exit('Access Denied');

load()->classs('wxapp/api/wxappauthapi');
load()->classs('wxapp/wxappoauth');
load()->func('communication');

//$dos = array('test', 'ticket');
//$do = in_array($_GPC['do'], $dos)? $do : 'ticket';

if($do == 'ticket') {
	$post = file_get_contents('php://input');
	pdo_insert('component_ticket', array('postdata'=>$post));
	WeUtility::logging('debug', 'weapp-ticket' . $post);
	exit('success');


}

if($do == 'test') {
	template('account/temp');
}

