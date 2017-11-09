<?php

/**
 * @package     web\source\pc
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
defined('IN_IA') or exit('Access Denied');
define('FRAME', 'pc');

load()->model('pc');

if($do == 'switch') {
	$uniacid = intval($_GPC['uniacid']);
	$uniacid = pc_get_pc_uniacid($_W['uid'], $uniacid);
	if(!$uniacid) {
		itoast('', url('pc/manage/list'), 'info');
	}
	pc_save_last($uniacid);
	itoast('', url('pc/home/display', array('uniacid'=>$uniacid)));
}

if($do == 'display') {
	define('FRAME', 'pc');
	$uniacid = intval($_GPC['uniacid']);
	$uniacid = pc_get_pc_uniacid($_W['uid'], $uniacid);
	if(!$uniacid) {
		itoast('', url('pc/manage/list'), 'info');
	}
	$account = uni_fetch($uniacid);
	$modulelist = uni_modules(false);
	if (!empty($modulelist)) {
		foreach ($modulelist as $name => &$row) {
			if (!empty($row['issystem']) || $row['pc_support'] != 2 || (!empty($_GPC['keyword']) && !strexists ($row['title'], $_GPC['keyword'])) || (!empty($_GPC['letter']) && $row['title_initial'] != $_GPC['letter'])) {
				unset($modulelist[$name]);
				continue;
			}
		}
		$modules = $modulelist;
	}
	template('pc/home');
}


