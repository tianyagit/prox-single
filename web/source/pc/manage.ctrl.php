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
//load()->classs('user');

if($do == 'create') {
	if(!checksubmit()) {
		echo '非法提交';
		return;
	}


	$isajax = $_W['isajax'];
	$rule = array(
		'name'=>'required',
		'description'=>'required',
	);
	$message = array(
		'name'=>'pc名称最大长度30',
		'description.required'=>'描述太长',
	);
	$validtor = Validator::create($_GPC, $rule, $message);
	$valid = $validtor->valid();
	if(! $valid) {
		if($isajax) {

		}else {
			$errors = $validtor->errors();
			template('pc/create');
			return;
		}

	}
	/* @var $pc PcTable*/
	$pc = table('pc');
	$uniacid = $pc->create($_GPC);
	if($uniacid){
		itoast('创建成功', url('pc/manage/display'));
	}

}

if($do == 'createview') {
	template('pc/create');
}

/* pc 列表*/
if($do == 'list') {
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