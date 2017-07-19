<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');

/**
 * 判断订单是否符合退款条件
 * @params string $module  需要退款的模块
 * @params string $tid 模块内订单id
 * @return bool true 成功返回true，失败返回error结构错误
 */
function refund_order_can_refund($module, $tid) {
	global $_W;
	$paylog = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'tid' => $tid, 'module' => $module));
	if (empty($paylog)) {
		return error(1, '订单不存在');
	}
	if ($paylog['status'] != 1) {
		return error(1, '此订单还未支付成功不可退款');
	}
	$refund_amount = pdo_getcolumn('core_refundlog', array('uniacid' => $_W['uniacid'], 'status' => 1, 'uniontid' => $paylog['uniontid']), 'SUM(fee)');
	if ($refund_amount >= $paylog['card_fee']) {
		return error(1, '订单已退款成功');
	}
	return true;
}

/**
 * 创建退款订单
 * @params string $tid  模块内订单id
 * @params string $module 需要退款的模块
 * @params string $fee 退款金额
 * @params string $reason 退款原因
 * @return int  成功返回退款单id，失败返回error结构错误
 */
function refund_create_order($tid, $module, $fee = 0, $reason = '') {
	load()->classs('pay');
	load()->model('module');
	global $_W;
	$order_can_refund = refund_order_can_refund($module, $tid);
	if (is_error($order_can_refund)) {
		return $order_can_refund;
	}
	$module_info = module_fetch($module);
	$moduleid =  empty($module_info['mid']) ? '000000' : sprintf("%06d", $module_info['mid']);
	$refund_uniontid = date('YmdHis') . $moduleid . random(8,1);
	$paylog = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'tid' => $tid, 'module' => $module));
	$refund = array (
		'uniacid' => $_W['uniacid'],
		'uniontid' => $paylog['uniontid'],
		'fee' => empty($fee) ? $paylog['card_fee'] : $fee,
		'status' => 0,
		'refund_uniontid' => $refund_uniontid,
		'reason' => $reason
	);
	pdo_insert('core_refundlog', $refund);
	return pdo_insertid();
}

/**
 * 退款
 * @params int $refund_id  退款单id
 * @return array  成功返回退款详情，失败返回error结构错误
 */
function refund($refund_id) {
	global $_W;
	$refundlog = pdo_get('core_refundlog', array('id' => $refund_id));
	$paylog = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'uniontid' => $refundlog['uniontid']));
	if ($paylog['type'] == 'wechat') {
		$refund_param = reufnd_wechat_build($refund_id);
		$wechat = Pay::create('weixin');
		$response = $wechat->refund($refund_param);
		unlink(ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_refund_all.pem');
		if (is_error($response)) {
			pdo_update('core_refundlog', array('status' => '-1'), array('id' => $refund_id));
			return $response;
		} else {
			return $response;
		}
	}
	return error(1, '此订单退款方式不存在');
}

/**
 * 构造微信退款参数
 * @params int $refund_id  退款单id
 * @return array  成功返回请求微信退款接口所需参数，失败返回error结构错误
 */
function reufnd_wechat_build($refund_id) {
	global $_W;
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$refund_setting = $setting['payment']['wechat_refund'];
	if ($refund_setting['switch'] != 1) {
		return error(1, '未开启微信退款功能！');
	}
	if (empty($refund_setting['key']) || empty($refund_setting['cert'])) {
		return error(1, '缺少微信证书！');
	}

	$refundlog = pdo_get('core_refundlog', array('id' => $refund_id));
	$paylog = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'uniontid' => $refundlog['uniontid']));
	$account = uni_fetch($_W['uniacid']);
	$refund_param = array(
		'appid' => $account['key'],
		'mch_id' => $setting['payment']['wechat']['mchid'],
		'out_trade_no' => $refundlog['uniontid'],
		'out_refund_no' => $refundlog['refund_uniontid'],
		'total_fee' => $paylog['card_fee'] * 100,
		'refund_fee' => $refundlog['fee'] * 100,
		'nonce_str' => random(8),
		'refund_desc' => $refundlog['reason']
	);
	$cert = authcode($refund_setting['cert'], 'DECODE');
	$key = authcode($refund_setting['key'], 'DECODE');
	file_put_contents(ATTACHMENT_ROOT . $_W['uniacid'] . '_wechat_refund_all.pem', $cert . $key);
	return $refund_param;
}