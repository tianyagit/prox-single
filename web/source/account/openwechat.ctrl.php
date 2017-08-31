<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/8/30
 * Time: 14:01
 */
defined('IN_IA') or exit('Access Denied');



//$dos = array('test', 'ticket');
//$do = in_array($_GPC['do'], $dos)? $do : 'ticket';
load()->classs('wxapp/wxappcloud');
load()->func('communication');

if($do == 'ticket') {
	$post = file_get_contents('php://input');
	pdo_insert('component_ticket', array('postdata'=>$post));
	WeUtility::logging('debug', 'weapp-ticket' . $post);
	WxAppCloud::updateThreePlatform($XML);
	exit('success');


}

if($do == 'test') {
	template('account/temp');
}

