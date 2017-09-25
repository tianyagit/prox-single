<?php
defined('IN_IA') or exit('Access Denied');
/**
 *  进入工单系统
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/9/25
 * Time: 15:51
 */
load()->classs('cloudapi');
if($do == 'display') {
	$siteurl = $_W['siteroot'];
	$cloud = new CloudApi();
	$data = $cloud->get('system','workorder', array('do'=>'siteworkorder'), 'json');

	if(is_error($data)) {
		itoast('无权限进入工单系统');
	}

	$iframe_url = $data['data']['url'].'&from='.$siteurl;
	template('system/workorder');
}




