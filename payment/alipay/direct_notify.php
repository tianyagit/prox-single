<?php
error_reporting(0);

if(!empty($_POST)) {
	$out_trade_no = $_POST['out_trade_no'];
	require '../../framework/bootstrap.inc.php';
	load()->web('common');

	$setting = setting_load('store_pay');
	$alipay = $setting['store_pay']['alipay'];
	if(is_array($alipay) && !empty($alipay)) {
		$prepares = array();
		foreach($_POST as $key => $value) {
			if($key != 'sign' && $key != 'sign_type') {
				$prepares[] = "{$key}={$value}";
			}
		}
		sort($prepares);
		$string = implode($prepares, '&');
		$string .= $alipay['secret'];
		$sign = md5($string);
		if($sign == $_POST['sign']) {
			$_POST['query_type'] = 'notify';
			WeUtility::logging ('pay-alipay', var_export ($_POST, true));
		}
		$order = pdo_get('site_store_order', array('orderid' => $out_trade_no));
		if (!empty($order) && $order['type'] == 1) {
			pdo_update('site_store_order', array('type' => 3), array('orderid' => $out_trade_no));
			cache_delete(cache_system_key($order['uniacid'] . ':site_store_buy_modules'));
			cache_build_account_modules($order['uniacid']);
		}
	}
}
exit('fail');
