<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
defined('IN_IA') or exit('Access Denied');

load()->classs('validator');
load()->model('pc');

if($do == 'create') {
	if(!checksubmit()) {
		echo '非法提交';
		return;
	}
	if(! pc_can_create($_W['uid'])) {
		itoast('创建PC个数已满', url('pc/manage/list'));
	}
	$data = array(
		'name'=>$_GPC['name'],
		'description'=>$_GPC['description']
	);

	/* @var $pc PcTable*/
	$pc = table('pc');
	$uniacid = $pc->create($data, $_W['uid']);
	if($uniacid){
		itoast('创建成功', url('pc/manage/list'));
	}
}

if($do == 'createview') {
	if(!pc_can_create($_W['uid'])) { //没有权限创建
		itoast('', url('pc/manage/list'));
	}
	template('pc/create');
}

/* pc 列表*/
if($do == 'list') {
	$account_info = permission_user_account_num();
	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;
	/* @var $pc PcTable*/
	$pc = table('pc');
	list($list, $total) = $pc->pclist($_W['uid'], $pindex, $psize);
	$pager = pagination($total, $pindex, $psize);

	foreach ($list as &$item) {

		$item['logo'] = tomedia('headimg_'.$account['acid']. '.jpg').'?time='.time();
		$item['switchurl'] = wurl('pc/home/switch', array('uniacid' => $item['uniacid']));
	}
	template('pc/list');
}