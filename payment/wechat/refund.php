<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
require '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->model('payment');
load()->func('communication');
global $_W;
$params = json_decode(base64_decode($_COOKIE[$_W['config']['cookie']['pre'] . 'wechat_refund']), true);
$log = pdo_get('core_paylog', array('plid' => $params['tid']));
if(!empty($log) && $log['status'] == 4) {
	exit('这个订单已经退款成功！');
}
$setting = uni_setting_load('payment', $log['uniacid']);
if (empty($setting['payment']['wechat_refund']['key']) || empty($setting['payment']['wechat_refund']['cert'])) {
	return error(1, '缺少微信证书！');
}
$acid = pdo_getcolumn('uni_account', array('uniacid' => $log['uniacid']), 'default_acid');
$appid = pdo_getcolumn('account_wechats', array('acid' => $acid), 'key');
$params = array(
	'appid' => $appid,
	'mch_id' => $setting['payment']['wechat']['mchid'],
	'out_trade_no' => $log['uniontid'],
	'out_refund_no' => $log['uniontid'],
	'total_fee' => $log['card_fee'] * 100,
	'refund_fee' => $log['card_fee'] * 100,
	'nonce_str' => random(8)
);
ksort($params);
$sign = '';
foreach($params as $key => $v) {
	if (empty($v)) {
		continue;
	}
	$sign .= "{$key}={$v}&";
}
$sign .= "key={$setting['payment']['wechat']['signkey']}";
$params['sign'] = strtoupper(md5($sign));
file_put_contents(ATTACHMENT_ROOT . 'all.pem', $setting['payment']['wechat_refund']['cert'] . $setting['payment']['wechat_refund']['key']);
$params = array2xml($params);
$refund_result = ihttp_request('https://api.mch.weixin.qq.com/secapi/pay/refund', $params, array('CURLOPT_SSLCERT' => ATTACHMENT_ROOT . 'all.pem'));
$result = json_decode(json_encode(isimplexml_load_string($refund_result['content'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
if ($result['result_code'] == 'SUCCESS') {
	pdo_update('core_paylog', array('status' => 4), array('plid' => $log['plid']));
	$site = WeUtility::createModuleSite($log['module']);
	if(!is_error($site)) {
		$method = 'refundResult';
		if (method_exists($site, $method)) {
			$ret = array();
			$ret['uniacid'] = $log['uniacid'];
			$ret['result'] = 'success';
			$ret['type'] = $log['type'];
			$ret['from'] = 'return';
			$ret['tid'] = $log['tid'];
			$ret['uniontid'] = $log['uniontid'];
			$ret['user'] = $log['openid'];
			$ret['fee'] = $log['fee'];
			$ret['tag'] = $tag;
			$ret['is_usecard'] = $log['is_usecard'];
			$ret['card_type'] = $log['card_type'];
			$ret['card_fee'] = $log['card_fee'];
			$ret['card_id'] = $log['card_id'];
			exit($site->$method($ret));
		}
	}
} else {
	exit($result['return_msg']);
}
?>
