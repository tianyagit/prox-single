<?php

/**
 * @package     web\source\pc
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
defined('IN_IA') or exit('Access Denied');

load()->model('webapp');

if($do == 'switch') {
	$uniacid = intval($_GPC['uniacid']);
	$uniacid = webapp_get_uniacid($_W['uid'], $uniacid);
	if(!$uniacid) {
		itoast('', url('webapp/manage/list'), 'info');
	}
	webapp_save_last($uniacid);
	itoast('', url('webapp/home/display', array('uniacid'=>$uniacid)));
}

if($do == 'display') {
	define('FRAME', 'webapp');
	$uniacid = intval($_GPC['uniacid']);
	$uniacid = webapp_get_uniacid($_W['uid'], $uniacid);
	if(!$uniacid) {
		itoast('', url('webapp/manage/list'), 'info');
	}
	$account = uni_fetch($uniacid);
	$modulelist = uni_modules(false);
	if (!empty($modulelist)) {
		foreach ($modulelist as $name => &$row) {
			if (!empty($row['issystem']) || $row['webapp_support'] != 2 || (!empty($_GPC['keyword']) && !strexists ($row['title'], $_GPC['keyword'])) || (!empty($_GPC['letter']) && $row['title_initial'] != $_GPC['letter'])) {
				unset($modulelist[$name]);
				continue;
			}
		}
		$modules = $modulelist;
	}
	template('webapp/home');
}


