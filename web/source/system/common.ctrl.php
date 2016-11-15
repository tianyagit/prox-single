<?php
/**
 * BAE相关设置选项
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

load()->model('setting');
load()->func('communication');

$_W['page']['title'] = '其他设置 - 系统管理';

if (checksubmit('bae_delete_update') || checksubmit('bae_delete_install')) {
	if (!empty($_GPC['bae_delete_update'])) {
		unlink(IA_ROOT . '/data/update.lock');
	} elseif (!empty($_GPC['bae_delete_install'])) {
		unlink(IA_ROOT . '/data/install.lock');
	}
	message('操作成功！', url('system/common'), 'success');
}

if (checksubmit('authmodesubmit')) {
	$authmode = intval($_GPC['authmode']);
	setting_save($authmode, 'authmode');
	message('更新设置成功！', url('system/common'));
}

template('system/common');