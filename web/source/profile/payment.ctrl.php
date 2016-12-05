<?php
/**
 * Created by ccj.
 * User: Administrator
 * Date: 2016/11/21
 * Time: 17:19
 * 支付参数配置
 */
defined('IN_IA') or exit('Access Denied');

$dos = array('save_setting', 'display', 'test_alipay', 'get_setting');
$do = in_array($do, $dos) ? $do : 'display';

uni_user_permission_check('profile_payment');
$_W['page']['title'] = '支付参数 - 公众号选项';

if ($do == 'get_setting') {
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$pay_setting = $setting['payment'];
	if(!is_array($pay_setting) || empty($pay_setting)) {
		$pay_setting = array(
			'delivery' => array('switch' => false),
			'credit' => array('switch' => false),
			'alipay' => array('switch' => false, 'account' => '', 'partner' => '', 'secret' => ''),
			'wechat' => array('switch' => false, 'account' => '', 'signkey' => '', 'partner' => '', 'key' => '', 'version' => '', 'mchid' => '', 'apikey' => '', 'service' => '', 'borrow' => '', 'sub_mch_id' => ''),
			'wechat_facilitator' => array('switch' => false, 'mchid' => '', 'signkey' => ''),
			'unionpay' => array('switch' => false, 'signcertpwd' => '', 'merid' => ''),
			'baifubao' => array('switch' => false, 'signkey' => '', 'mchid' => ''),
			'line' => array('switch' => false, 'message' => ''),
		);
	}
	message(error(0, $pay_setting), '', 'ajax');
}

if ($do == 'test_alipay') {
	$alipay = $_GPC['__input']['param'];
	$params = array();
	$params['tid'] = md5(uniqid());
	$params['user'] = '测试用户';
	$params['fee'] = '0.01';
	$params['title'] = '测试支付接口';
	load()->model('payment');
	load()->func('communication');
	$result = alipay_build($params, $alipay);
	message(error(0, $result['url']), '', 'ajax');
}

if ($do == 'save_setting') {
	$type = $_GPC['__input']['type'];
	$param = $_GPC['__input']['param'];
	$setting = uni_setting_load('payment', $_W['uniacid']);
	$pay_setting = $setting['payment'];
	if ($type == 'credit' || $type == 'delivery') {
		$param['switch'] = !$param['switch'];
	}
	if ($type == 'wechat') {
		$param['account'] = $_W['acid'];
	}
	if ($type == 'unionpay') {
		if ($unionpay['switch'] && empty($_FILES['unionpay']['tmp_name']['signcertpath']) && !file_exists(IA_ROOT . '/attachment/unionpay/PM_'.$_W['uniacid'].'_acp.pfx')) {
			message('请上联银商户私钥证书.');
		}
		$param = array(
			'switch' => $_GPC['unionpay']['switch'] == 'false'? false : true,
			'merid' => $_GPC['unionpay']['merid'],
			'signcertpwd' => $_GPC['unionpay']['signcertpwd']
		);
		if($param['switch'] && (empty($param['merid']) || empty($param['signcertpwd']))) {
			message('请输入完整的银联支付接口信息.');
		}
		if ($param['switch'] && empty($_FILES['unionpay']['tmp_name']['signcertpath']) && !file_exists(IA_ROOT . '/attachment/unionpay/PM_'.$_W['uniacid'].'_acp.pfx')) {
			message('请上传银联商户私钥证书.');
		}
		if ($param['switch'] && !empty($_FILES['unionpay']['tmp_name']['signcertpath'])) {
			load()->func('file');
			mkdirs(IA_ROOT . '/attachment/unionpay/');
			file_put_contents(IA_ROOT . '/attachment/unionpay/PM_'.$_W['uniacid'].'_acp.pfx', file_get_contents($_FILES['unionpay']['tmp_name']['signcertpath']));
			$public_rsa = '-----BEGIN CERTIFICATE-----
MIIEIDCCAwigAwIBAgIFEDRVM3AwDQYJKoZIhvcNAQEFBQAwITELMAkGA1UEBhMC
Q04xEjAQBgNVBAoTCUNGQ0EgT0NBMTAeFw0xNTEwMjcwOTA2MjlaFw0yMDEwMjIw
OTU4MjJaMIGWMQswCQYDVQQGEwJjbjESMBAGA1UEChMJQ0ZDQSBPQ0ExMRYwFAYD
VQQLEw1Mb2NhbCBSQSBPQ0ExMRQwEgYDVQQLEwtFbnRlcnByaXNlczFFMEMGA1UE
Aww8MDQxQDgzMTAwMDAwMDAwODMwNDBA5Lit5Zu96ZO26IGU6IKh5Lu95pyJ6ZmQ
5YWs5Y+4QDAwMDE2NDkzMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA
tXclo3H4pB+Wi4wSd0DGwnyZWni7+22Tkk6lbXQErMNHPk84c8DnjT8CW8jIfv3z
d5NBpvG3O3jQ/YHFlad39DdgUvqDd0WY8/C4Lf2xyo0+gQRZckMKEAId8Fl6/rPN
HsbPRGNIZgE6AByvCRbriiFNFtuXzP4ogG7vilqBckGWfAYaJ5zJpaGlMBOW1Ti3
MVjKg5x8t1/oFBkpFVsBnAeSGPJYrBn0irfnXDhOz7hcIWPbNDoq2bJ9VwbkKhJq
Vz7j7116pziUcLSFJasnWMnp8CrISj52cXzS/Y1kuaIMPP/1B0pcjVqMNJjowooD
OxID3TZGfk5V7S++4FowVwIDAQABo4HoMIHlMB8GA1UdIwQYMBaAFNHb6YiC5d0a
j0yqAIy+fPKrG/bZMEgGA1UdIARBMD8wPQYIYIEchu8qAQEwMTAvBggrBgEFBQcC
ARYjaHR0cDovL3d3dy5jZmNhLmNvbS5jbi91cy91cy0xNC5odG0wNwYDVR0fBDAw
LjAsoCqgKIYmaHR0cDovL2NybC5jZmNhLmNvbS5jbi9SU0EvY3JsMjI3Mi5jcmww
CwYDVR0PBAQDAgPoMB0GA1UdDgQWBBTEIzenf3VR6CZRS61ARrWMto0GODATBgNV
HSUEDDAKBggrBgEFBQcDAjANBgkqhkiG9w0BAQUFAAOCAQEAHMgTi+4Y9g0yvsUA
p7MkdnPtWLS6XwL3IQuXoPInmBSbg2NP8jNhlq8tGL/WJXjycme/8BKu+Hht6lgN
Zhv9STnA59UFo9vxwSQy88bbyui5fKXVliZEiTUhjKM6SOod2Pnp5oWMVjLxujkk
WKjSakPvV6N6H66xhJSCk+Ref59HuFZY4/LqyZysiMua4qyYfEfdKk5h27+z1MWy
nadnxA5QexHHck9Y4ZyisbUubW7wTaaWFd+cZ3P/zmIUskE/dAG0/HEvmOR6CGlM
55BFCVmJEufHtike3shu7lZGVm2adKNFFTqLoEFkfBO6Y/N6ViraBilcXjmWBJNE
MFF/yA==
-----END CERTIFICATE-----';
			file_put_contents(IA_ROOT . '/attachment/unionpay/UpopRsaCert.cer', trim($public_rsa));
		}
	}
	$pay_setting[$type] = $param;
	$pay_setting = iserializer($pay_setting);
	pdo_update('uni_settings', array('payment' => $pay_setting), array('uniacid' => $_W['uniacid']));
	cache_delete("unisetting:{$_W['uniacid']}");
	if ($type == 'unionpay') {
		header('LOCATION: '.url('profile/payment'));
		exit();
	}
	message(error(0), '', 'ajax');
}

$setting = uni_setting_load('payment', $_W['uniacid']);
$pay_setting = $setting['payment'];
if(!is_array($pay_setting) || empty($pay_setting)) {
	$pay_setting = array(
		'delivery' => array('switch' => false),
		'credit' => array('switch' => false),
		'alipay' => array('switch' => false, 'account' => '', 'partner' => '', 'secret' => ''),
		'wechat' => array('switch' => false, 'account' => '', 'signkey' => '', 'partner' => '', 'key' => '', 'version' => '', 'mchid' => '', 'apikey' => '', 'service' => '', 'borrow' => '', 'sub_mch_id' => ''),
		'wechat_facilitator' => array('switch' => false, 'mchid' => '', 'signkey' => ''),
		'unionpay' => array('switch' => false, 'signcertpwd' => '', 'merid' => ''),
		'baifubao' => array('switch' => false, 'signkey' => '', 'mchid' => ''),
		'line' => array('switch' => false, 'message' => ''),
	);
}
if (empty($_W['isfounder'])) {
	$user_account_list = pdo_getall('uni_account_users', array('uid' => $_W['uid']), array(), 'uniacid');
	$param['uniacid'] = array_keys($user_account_list);
}
$accounts = array();
$accounts[$_W['acid']] = array_elements(array('name', 'acid', 'key', 'secret', 'level'), $_W['account']);
$pay_setting['unionpay']['signcertexists'] = file_exists(IA_ROOT . '/attachment/unionpay/PM_'.$_W['uniacid'].'_acp.pfx');
template('profile/payment');